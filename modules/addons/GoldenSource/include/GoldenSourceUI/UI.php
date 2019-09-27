<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.ir)
 */
namespace GoldenSourceUI;
use GoldenSource\Models\Products\Product;
use WHMCS\Database\Capsule;
use GoldenSource\APIException;
use GoldenSource\PHPView;
class UI
{
    private $output = '';
    private $params;
    private $client;
    private $information;
    private $session;

    private function getLatestVersion(){
        $url = "https://raw.githubusercontent.com/Golden-Source/reseller/master/modules/addons/GoldenSource/include/version?" . time();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_HEADER => 0,
            CURLOPT_AUTOREFERER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_FOLLOWLOCATION => 1,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function __construct(array $params)
    {
        if(isset($_REQUEST['update'])){
            if ($_REQUEST['update'] == 1) {
                $result = GoldenSource_update();
                if(true === $result){
                    header("Location: addonmodules.php?module=GoldenSource&update=2");
                } else {
                    header("Location: addonmodules.php?module=GoldenSource&update=2&error=" . urlencode($result));
                }
            } else if($_REQUEST['update'] == 2){
                header("Location: addonmodules.php?module=GoldenSource");
            }
            exit();
        }
        
        $version = file_get_contents(dirname(__DIR__) . "/version");
        $remoteVersion = $this->getLatestVersion();

        $v1 = (int)str_replace('.', null, $version);
        $v2 = (int)str_replace('.', null, $remoteVersion);

        if(!empty($remoteVersion) && $version < $remoteVersion){
            $this->output .= '<div class="alert alert-info text-center">';
            $this->output .= GoldenSource_translate('New update is available')." (".GoldenSource_translate("Current version").": $version, ".GoldenSource_translate('Latest version').": $remoteVersion)&nbsp;";
            $this->output .= sprintf(GoldenSource_translate('click %s to update'), '<a href="addonmodules.php?module=GoldenSource&update=1"><strong>'.GoldenSource_translate('here').'</strong></a>');
            $this->output .= '</div>';
            if($v2-$v1 >= 5){
                $this->output .= '<div class="alert alert-danger text-center">';
                $this->output .= "<strong>".GoldenSource_translate('version_too_old')."</strong>";
                $this->output .= '</div>';
                return;
            }
        }
        $this->session = new SessionHelper();
        $this->params = $params;
        $serverToken = isset($_REQUEST['serverId']) ? $this->getServerToken((int) $_REQUEST['serverId']) : null;
        if (!$serverToken) {
            $this->renderChooseServer();
            return;
        }
        try {
            $this->client = GoldenSource_getClient($serverToken);
            $this->information = $this->client->information();
        } catch(APIException $e){
            $this->output .= GoldenSource_translate('GoldenSource_not_available');
            $this->output .= '<br><br>';
            $this->output .= $this->renderTemplate('copyright', []);
            return;
        }
        if(isset($_REQUEST['productId'])){
            $product = $this->client->products()->get((int) $_REQUEST['productId']);
            $vars = [];
            $vars['fullName'] = $product->fullName;
            $vars['serverId'] = $_REQUEST['serverId'];
            $vars['installationHelp'] = [];
            foreach($product->installationHelp as $os => $commands){
                $commands = trim($commands);
                if($this->information->dedicatedLink){
                    $commands = preg_replace('/([A-Za-z0-9]+)\.configserver\.pro/', $this->information->dedicatedLink, $commands);
                    $commands = preg_replace('/configserver\.pro/', $this->information->dedicatedLink, $commands);
                }
                $vars['installationHelp'][] = (object)['os' => $os, 'commands' => $commands];
            }
            $this->output .= $this->renderTemplate('product', $vars);
            $this->output .= '<br><br>';
            $this->output .= $this->renderTemplate('copyright', []);
            return;
        }
        if (isset($_REQUEST['licenseId'])) {
            $this->renderLicense((int)$_REQUEST['licenseId']);
            return;
        }
        $this->renderLicenses();
    }

    private function renderAddProducts(){
        $vars = [];
        $vars['serverId'] = $_REQUEST['serverId'];
        $vars['productGroups'] = Capsule::table('tblproductgroups')->get();
        $vars['currencies'] = Capsule::table('tblcurrencies')->get();
        $vars['products'] = $this->client->products()->all();
        $vars['exchangeRate'] = isset($_POST['exchangeRate']) ? (float)$_POST['exchangeRate'] : $this->information->exchangeRateRial;

        $vars['allowChangeIP'] = isset($_POST['allowChangeIP']) ? (bool)$_POST['allowChangeIP'] : false;
        $vars['currency'] = isset($_POST['currency']) ? (int)$_POST['currency'] : false;
        $vars['productGroup'] = isset($_POST['productGroup']) ? (int)$_POST['productGroup'] : false;
        $vars['roundBy'] = isset($_POST['roundBy']) ? (float)$_POST['roundBy'] : 100;
        $vars['product'] = array_unique(isset($_POST['product']) ? (array)$_POST['product'] : []);
        $vars['productType'] = isset($_POST['productType']) && $_POST['productType'] == 'addon' ? 'addon' : 'product';
        $vars['updateExisting'] = isset($_POST['updateExisting']) && $_POST['updateExisting'] == 1;
        $vars['updateAddonPackages'] = isset($_POST['updateAddonPackages']) && $_POST['updateAddonPackages'] == 1;
        $vars['updateServicesPrices'] = isset($_POST['updateServicesPrices']) && $_POST['updateServicesPrices'] == 1;


        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            foreach($vars['product'] as $pid){
                if(empty($pid)) continue;
                $this->addProduct($pid, $vars['productGroup'], $vars['allowChangeIP'], $vars['productType'] == 'addon', $vars['currency'], $vars['exchangeRate'], $vars['roundBy'], $vars['updateExisting'], $vars['updateAddonPackages'], $vars['updateServicesPrices']);
            }
            $vars['success'] = GoldenSource_translate('Products added successfully');
        } else {
            $vars['updateExisting'] = true;
        }
        return $this->renderTemplate('addProducts', $vars);
    }


    private function addProduct($pid, $gid, $allowChangeIP, $isAddon, $currency, $exchangeRate, $roundBy, $updateExisting, $updateAddonPackages, $updateServicesPrices){
        $productDetails = $this->client->products()->get($pid);
        $productType = $isAddon ? 'addon' : 'product';

        if($productType == 'product'){
            $product = Capsule::table('tblproducts')->where('servertype', 'GoldenSource')->where('configoption1', $pid)->get(['id']);
            if($updateExisting && sizeof($product)){
                foreach($product as $prod){
                    Capsule::table('tblproducts')->where('id', $prod->id)->update([
                        'configoption2' => $allowChangeIP ? 'on' : '',
                    ]);
                    $this->handleProduct($currency, $exchangeRate, $roundBy, $productType, $prod->id, $productDetails, $updateServicesPrices);
                }
            } else {
                $product = new \WHMCS\Product\Product();
                $product->type = "other";
                $product->productGroupId = $gid;
                $product->name = $productDetails->fullName;
                $product->paymentType = 'recurring';
                $product->showDomainOptions = false;
                $displayOrder = \Illuminate\Database\Capsule\Manager::table("tblproducts")->where("gid", "=", $gid)->max("order");
                $product->displayOrder = is_null($displayOrder) ? 0 : ++$displayOrder;
                $product->servertype = "GoldenSource";
                $product->autosetup = "payment";
                $product->configoption1 = $productDetails->id;
                $product->configoption2 = $allowChangeIP ? 'on' : '';
                $product->allowqty = 1;
                $product->save();

                $this->handleProduct($currency, $exchangeRate, $roundBy, $productType, $product->id, $productDetails, $updateServicesPrices);
            }
        } else {
            $addons = Capsule::table('tbladdons as a')->where('module', 'GoldenSource')->whereRaw('(SELECT c.value FROM tblmodule_configuration c WHERE c.entity_type="addon" AND c.entity_id=a.id AND c.setting_name="configoption1")=' . $productDetails->id)->get();
            if($updateExisting && sizeof($addons)){
                foreach($addons as $addon){
                    if($updateAddonPackages){
                        $packages = Capsule::table('tblproducts')->whereNotIn('servertype', [
                            'GoldenSource',
                            'cpanel',
                            'directadmin',
                            'plesk',
                        ])->pluck('id');

                        Capsule::table('tbladdons')->where('id', $addon->id)->update([
                            'name' => $productDetails->fullName,
                            'updated_at' => date('Y-m-d H:i:s'),
                            'packages' => implode(",", $packages),
                        ]);
                    } else {
                        Capsule::table('tbladdons')->where('id', $addon->id)->update([
                            'name' => $productDetails->fullName,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }

                    Capsule::table('tblmodule_configuration')
                        ->where('entity_type', 'addon')
                        ->where('entity_id', $addon->id)
                        ->where('setting_name', 'configoption2')
                        ->update([
                            'friendly_name' => 'Allow change IP?',
                            'value' => $allowChangeIP ? 'on' : '',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    $this->handleProduct($currency, $exchangeRate, $roundBy, $productType, $addon->id, $productDetails, $updateServicesPrices);
                }
            } else {
                $packages = Capsule::table('tblproducts')->whereNotIn('servertype', [
                    'GoldenSource',
                    'cpanel',
                    'directadmin',
                    'plesk',
                ])->pluck('id');

                $productId = Capsule::table('tbladdons')->insertGetId([
                    'packages' => implode(",", $packages),
                    'name' => $productDetails->fullName,
                    'description' => '',
                    'billingcycle' => 'recurring',
                    'tax' => 0,
                    'showorder' => 1,
                    'downloads' => '',
                    'autoactivate' => 'payment',
                    'suspendproduct' => 0,
                    'welcomeemail' => 0,
                    'type' => 'other',
                    'module' => 'GoldenSource',
                    'autolinkby' => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                Capsule::table('tblmodule_configuration')->insert([
                    'entity_type' => 'addon',
                    'entity_id' => $productId,
                    'setting_name' => 'configoption1',
                    'friendly_name' => 'Product',
                    'value' => $productDetails->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                Capsule::table('tblmodule_configuration')->insert([
                    'entity_type' => 'addon',
                    'entity_id' => $productId,
                    'setting_name' => 'configoption2',
                    'friendly_name' => 'Allow change IP?',
                    'value' => $allowChangeIP ? 'on' : '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $this->handleProduct($currency, $exchangeRate, $roundBy, $productType, $productId, $productDetails, $updateServicesPrices);
            }
        }

    }

    private function renderLicenses()
    {
        $vars = ['activeTab' => isset($_REQUEST['search']) ? 'search' : (isset($_REQUEST['addProducts']) ? 'addProducts' : '')];
        if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && isset($_REQUEST['do']) && $_REQUEST['do'] == 'saveSettings'){
            Capsule::table('mod_GoldenSource_settings')->update([
                'change_ip_price' => (float) $_REQUEST['change_ip_price'],
            ]);
        }
        $vars['settings'] = Capsule::table('mod_GoldenSource_settings')->first();
        if (isset($_GET['c']) && $_GET['c'] == $this->session->getChecker()) {
            $this->session->changeChecker();
            if (isset($_REQUEST['extendLicense'])) {
                try {
                    $license = $this->client->licenses()->get((int) $_REQUEST['extendLicense']);
                    $result = $license->renew();
                    if ($result) {
                        $vars['success'] = sprintf('License has been renewed, $%s was deducted from your balance. Your new balance is $%s.', $result->cost, $result->balance);
                    }
                } catch (APIException $e) {
                    $vars['error'] = $e->getMessage();
                }
            }
        }
        $criteria = [];
        if (isset($_REQUEST['ip']) && !empty($_REQUEST['ip'])) {
            $criteria['ip'] = $_REQUEST['ip'];
        }
        if (isset($_REQUEST['status'])) {
            $criteria['status'] = $_REQUEST['status'];
        }
        $vars['addProducts'] = $vars['activeTab'] == 'addProducts' ? $this->renderAddProducts() : null;
        $vars['serverId'] = $_REQUEST['serverId'];
        $vars['criteria'] = $criteria;
        $vars['licenses'] = $this->client->licenses()->all($criteria);
        $vars['exchangeRateToman'] = $this->information->exchangeRateToman;
        foreach($vars['licenses'] as &$license){
            $client = null;
            $license->client = '<i class="fas fa-question-circle text-success"></i>';
            $stmt1 = Capsule::connection()->getPdo()->query("SELECT c.id, c.firstname, c.lastname, cfv.relid serviceid FROM tblcustomfields cf LEFT JOIN tblcustomfieldsvalues cfv ON cfv.fieldid=cf.id LEFT JOIN tblhosting h ON h.id=cfv.relid LEFT JOIN tblclients c ON c.id=h.userid WHERE cf.type='product' AND cf.fieldname LIKE 'licenseId%%' AND cfv.value={$license->id} AND h.domainstatus IN('Active', 'Suspended')");
            $stmt2 = Capsule::connection()->getPdo()->query("SELECT c.id, c.firstname, c.lastname, cfv.relid serviceid FROM tblcustomfields cf LEFT JOIN tblcustomfieldsvalues cfv ON cfv.fieldid=cf.id LEFT JOIN tblhostingaddons h ON h.id=cfv.relid LEFT JOIN tblclients c ON c.id=h.userid WHERE cf.type='addon' AND cf.fieldname LIKE 'licenseId%%' AND cfv.value={$license->id} AND h.status IN('Active', 'Suspended')");
            if(($stmt1->rowCount() + $stmt2->rowCount()) > 1){
                $license->client = '<i class="fas fa-exclamation-triangle text-danger"></i>';
            } else if($stmt1->rowCount()) {
                $client = $stmt1->fetch(\PDO::FETCH_ASSOC);
                $license->client = '<span title="'.sprintf('%s %s', $client['firstname'], $client['lastname']).'"><a target="_blank" href="clientsservices.php?userid='.$client['id'].'&id='.$client['serviceid'].'">🔍</a></span>';
            } else if($stmt2->rowCount()) {
                $client = $stmt1->fetch(\PDO::FETCH_ASSOC);
                $license->client = '<span title="'.sprintf('%s %s', $client['firstname'], $client['lastname']).'"><a target="_blank" href="clientsservices.php?userid='.$client['id'].'&aid='.$client['serviceid'].'">🔍</a></span>';
            }
        }

        $vars['sessionChecker'] = $this->session->getChecker();
        $vars['products'] = $this->client->products()->all();
        $vars['information'] = $this->information;

        $this->output .= $this->renderTemplate('licenses', $vars);
        $this->output .= $this->renderTemplate('footer', [
            'exchangeRateRial' => $this->information->exchangeRateRial,
            'exchangeRateToman' => $this->information->exchangeRateToman,
        ]);
    }
    private function renderChooseServer()
    {
        $servers = Capsule::table('tblservers')->where('type', 'GoldenSource')->get();
        if(!isset($_REQUEST['serverId']) && sizeof($servers) == 1){
            foreach($servers as $server){
                header("Location: addonmodules.php?module=GoldenSource&serverId={$server->id}");
                exit;
            }
        }
        $serversArr = [];
        foreach ($servers as $server) {
            if(empty($server->accesshash)) continue;
            try {
                $client = GoldenSource_getClient($server->accesshash);
                $information = $client->information();

                $row = &$serversArr[];
                $row = new \stdClass;

                $row->id = $server->id;
                $row->credit = $information->credit;
                $row->discount = $information->discount;
                $row->email = $information->email;
                $row->total_licenses = $information->total_licenses;
                $row->partnerLevel = $information->partnerLevel;
                $row->discount = $information->discount;
            } catch (\Exception $e) {
                if($e->getMessage() == "No data is provided."){
                    $this->output .= GoldenSource_translate('GoldenSource_not_available');
                    $this->output .= '<br><br>';
                    $this->output .= $this->renderTemplate('copyright', []);
                    return;
                }
                $this->output .= $e->getMessage();
            }
        }
        $this->output .= $this->renderTemplate('servers', ['servers' => $serversArr]);
    }

    private function renderLicense($id)
    {
        global $_LANG;
        $vars = [
            'activeTab' => 'details',
            'serverId' => (int) $_REQUEST['serverId'],
            'information' => $this->information,
        ];
        try {
            $license = $this->client->licenses()->get($id);
            $vars['installationHelp'] = [];
            foreach($license->product()->installationHelp as $os => $commands){
                $commands = trim($commands);
                if($this->information->dedicatedLink){
                    $commands = preg_replace('/([A-Za-z0-9]+)\.GoldenSource\.pro/', $this->information->dedicatedLink, $commands);
                    $commands = preg_replace('/GoldenSource\.pro/', $this->information->dedicatedLink, $commands);
                }
                $vars['installationHelp'][] = (object)['os' => $os, 'commands' => $commands];
            }
            $vars['license'] = $license;
        } catch (APIException $e) {
            $vars['error'] = $e->getMessage();
            goto render;
        }

        $vars['statusColor'] = $license->status == 'active' ? '#dff0d8' : ($license->status == 'suspended' ? '#f2dede' : 'initial');

        if(isset($_GET['c']) && $_GET['c'] == $this->session->getChecker()){
            $this->session->changeChecker();
            if (isset($_REQUEST['extend'])) {
                try {
                    $result = $license->renew();
                    if($result){
                        $vars['success'] = sprintf(GoldenSource_translate('License has been renewed, $%s was deducted from your balance. Your new balance is $%s.'), $result->cost, $result->balance);
                    }
                } catch (APIException $e) {
                    $vars['error'] = $e->getMessage();
                }
            }
            if(isset($_POST['action'])){
                switch($_POST['action']){
                    case 'changeSettings':
                        try {
                            $license->changeStatus((string) $_POST['setStatus']);
                            $license->changeCycle((string) $_POST['setBillingCycle']);
                            $vars['success'] = GoldenSource_translate('All settings were updated');
                        } catch (APIException $e) {
                            $vars['error'] = $e->getMessage();
                        }
                        break;
                        case 'updateNote':
                            try {
                                $license->updateNotes(filter_var($_POST['notes'], FILTER_SANITIZE_STRING));
                                $vars['success'] = GoldenSource_translate('Notes were saved');
                            } catch (APIException $e) {
                                $vars['error'] = $e->getMessage();
                            }
                            break;
                        case 'changeIP':
                            try {
                                $result = $license->changeIP($_POST['newIP'], true);
                                if($result){
                                    if(property_exists($result, 'cost')){
                                        $vars['success'] = sprintf(GoldenSource_translate('IP address was changed. $%s was deducted from your account. Your new balance is: $%s'), $result->cost, $result->balance);
                                    } else {
                                        $vars['success'] = GoldenSource_translate('IP address was changed');
                                    }
                                }
                            } catch (APIException $e) {
                                $vars['error'] = $e->getMessage();
                            }
                            break;
                }
            }
        }
        render:
        
        $vars['direction'] = GoldenSource_translate('direction');
        $vars['textAlign'] = GoldenSource_translate('textAlign');
        $vars['sessionChecker'] = $this->session->getChecker();
        $this->output .= $this->renderTemplate('license', $vars);
    }

    private function getServerToken($serverId)
    {
        $server = Capsule::table('tblservers')->where('type', 'GoldenSource')->where('id', $serverId)->first();
        if (!$server) {
            return false;
        }
        return $server->accesshash;
    }


    private function renderTemplate($template, $vars)
    {
        $vars['version'] = $this->params['version'];
        return PHPView::render(__DIR__ . '/templates/' . $template, $vars);
    }

    public function output()
    {
        return $this->output;
    }

    /**
     * @param $currency
     * @param $exchangeRate
     * @param $roundBy
     * @param $productType
     * @param $productId
     * @param $productDetails
     */
    private function handleProduct($currency, $exchangeRate, $roundBy, $productType, $productId, Product $productDetails, $updateServicesPrices)
    {
        $productPrices = [
            'monthly' => ceil($productDetails->priceWithDiscount('monthly') * $exchangeRate / $roundBy) * $roundBy,
            'quarterly' => ceil($productDetails->priceWithDiscount('quarterly') * $exchangeRate / $roundBy) * $roundBy,
            'semiannually' => ceil($productDetails->priceWithDiscount('semiannually') * $exchangeRate / $roundBy) * $roundBy,
            'annually' => ceil($productDetails->priceWithDiscount('annually') * $exchangeRate / $roundBy) * $roundBy,
            'setupfee' => ceil($productDetails->priceWithDiscount('setupfee') * $exchangeRate / $roundBy) * $roundBy,
        ];
        $pricing = Capsule::table('tblpricing')->where('type', $productType)->where('relid', $productId)->first();
        if (!$pricing) {
            $pricing = new \stdClass();
            $pricing->monthly = $productPrices['monthly'];
            $pricing->quarterly = $productPrices['quarterly'];
            $pricing->semiannually = $productPrices['semiannually'];
            $pricing->annually = $productPrices['annually'];

            $pricing->msetupfee = $pricing->qsetupfee = $pricing->ssetupfee = $pricing->asetupfee = $productPrices['setupfee'];
            $pricing->biennially = $pricing->bsetupfee = -1;
            $pricing->triennially = $pricing->tsetupfee = -1;

            $pricing->type = $productType;
            $pricing->relid = $productId;
            $pricing->currency = $currency;

            Capsule::table('tblpricing')->insert((array)$pricing);
        } else {
            Capsule::table('tblpricing')->where('id', $pricing->id)->update([
                'monthly' => $productPrices['monthly'],
                'quarterly' => $productPrices['quarterly'],
                'semiannually' => $productPrices['semiannually'],
                'annually' => $productPrices['annually'],
                'msetupfee' => $productPrices['setupfee'],
                'qsetupfee' => $productPrices['setupfee'],
                'ssetupfee' => $productPrices['setupfee'],
                'asetupfee' => $productPrices['setupfee'],
            ]);
        }
        if($updateServicesPrices){
            if($productType == 'product'){
                $services = Capsule::table('tblhosting')->where('packageid', $productId)->whereNotIn('domainstatus', ['Cancelled', 'Terminated'])->get(['id', 'billingcycle']);
                foreach($services as $service){
                    $billingCycle = strtolower($service->billingcycle);
                    if(in_array($billingCycle, $productPrices)){
                        Capsule::table('tblhosting')->where('id', $service->id)->update([
                            'amount' => $productPrices[$billingCycle],
                        ]);
                    }
                }
            } else {
                $services = Capsule::table('tblhostingaddons')->where('addonid', $productId)->whereNotIn('status', ['Cancelled', 'Terminated'])->get(['id', 'billingcycle']);
                foreach($services as $service){
                    $billingCycle = strtolower($service->billingcycle);
                    if(in_array($billingCycle, $productPrices)){
                        Capsule::table('tblhostingaddons')->where('id', $service->id)->update([
                            'recurring' => $productPrices[$billingCycle],
                        ]);
                    }
                }
            }
        }
        $customfields = [
            'IP' => [
                'type' => 'text',
                'regexpr' => '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/',
                'adminonly' => false,
                'showinvoice' => true,
                'showorder' => true,
                'required' => true,
            ],
            'licenseId' => [
                'type' => 'text',
                'adminonly' => true,
            ],
        ];

        foreach ($customfields as $key => $val) {
            $field = Capsule::table('tblcustomfields')->where('type', $productType)->where('relid', $productId)->where('fieldname', $key)->first();
            $data = [
                "type" => $productType,
                "relid" => $productId,
                "fieldname" => $key,
                "fieldtype" => $val["type"],
                "regexpr" => isset($val["regexpr"]) ? $val["regexpr"] : '',
                "adminonly" => isset($val["adminonly"]) && $val["adminonly"] ? 'on' : '',
                "required" => isset($val["required"]) && $val["required"] ? 'on' : '',
                "showorder" => isset($val["showorder"]) && $val["showorder"] ? 'on' : '',
                "showinvoice" => isset($val["showinvoice"]) && $val["showinvoice"] ? 'on' : '',
            ];
            if ($val['type'] == 'dropdown') {
                $data['fieldoptions'] = $val['fieldoptions'];
            }
            if ($field) {
                Capsule::table('tblcustomfields')->where('id', $field->id)->update($data);
            } else {
                Capsule::table('tblcustomfields')->insert($data);
            }
        }
    }
}
