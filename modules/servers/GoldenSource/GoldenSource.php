<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.ir)
 */
require_once __DIR__ . "/include/bootstrap.php";
use WHMCS\Database\Capsule;
use GoldenSource\APIException;
use GoldenSource\Models\Licenses\License;
use GoldenSource\PHPView;

function GoldenSource_MetaData()
{
    return array(
        'DisplayName' => 'GoldenSource',
        'APIVersion' => '1.0',
        'RequiresServer' => true,
    );
} 

function GoldenSource_TestConnection(array $params)
{
    global $_LANG;
    if (empty($params['serveraccesshash'])) {
        return GoldenSource_translate('pleaseEnterAPIToken');
    }
    try {
        $client = GoldenSource_getClientByParams($params);
        if ($client->ping()) {
            return ['success' => true, 'error' => null];
        }
    } catch (APIException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
    return [
        'success' => false,
        'error' => GoldenSource_translate('unknownError')
    ];
}

function GoldenSource_ConfigOptions()
{
    return array(
        'Product' => array(
            "FriendlyName" => GoldenSource_translate('Product'),
            'Type' => 'dropdown',
            'Loader' => 'GoldenSource_loadProducts',
            'SimpleMode' => true,
        ),
        'allowChnageIP' => array(
            "FriendlyName" => GoldenSource_translate('allowChnageIP'),
            "Type" => "yesno",
            "Options" => [
                0 => "No",
                1 => "Yes",
            ],
            "Description" => GoldenSource_translate('tickToEnable'),
            'SimpleMode' => true,
        ),
        'FreeChangeIP' => array(
            "FriendlyName" => GoldenSource_translate('Free change IP times'),
            "Type" => "text",
            "Default" => "3",
            'SimpleMode' => true,
        ),
    );
}

function GoldenSource_loadProducts(array $params)
{
    $client = GoldenSource_getClientByParams($params);
    try {
        $products = $client->products()->all();
        $result = [];
        foreach ($products as $product) {
            /** @var $product GoldenSource\Models\Products\Product */
            $result[$product->id] = $product->fullName;
        }
        return $result;
    } catch (APIException $e) {
        return ['Failed to load products: ' . $e->getMessage()];
    }
}

function GoldenSource_getServerIdByParams(array $params){
    if(isset($params['addonId']) && $params['addonId'] > 0){
        $version = (int)str_replace('.', null, explode("-", $params['whmcsVersion'])[0]);
        if($version < 745){
            $server = Capsule::table('tblservers')->where('type', 'GoldenSource')->first();
            return $server->id;
        }
    }
    if(isset($params['serveraccesshash'])){
        return $params['serverid'];
    }
}

function GoldenSource_getClientByParams(array $params){
    if(isset($params['addonId']) && $params['addonId'] > 0){
        $version = (int)str_replace('.', null, explode("-", $params['whmcsVersion'])[0]);
        if($version < 745){
            $server = Capsule::table('tblservers')->where('type', 'GoldenSource')->first();
            return GoldenSource_getClient($server->accesshash);
        }
    }
    if(isset($params['serveraccesshash'])){
        return GoldenSource_getClient($params['serveraccesshash']);
    }
}

function GoldenSource_CreateAccount(array $params)
{
    global $_LANG;
    $client = GoldenSource_getClientByParams($params);
    if (!array_key_exists('IP', $params['customfields'])) {
        return GoldenSource_translate('ipFieldNotFound');
    }
    if (array_key_exists('licenseId', $params['customfields']) && !empty($params['customfields']['licenseId'])) {
        return GoldenSource_translate('licenseAlreadyAssigned');
    }
    if (empty($params['customfields']['IP'])) {
        return GoldenSource_translate('ipAddressEmpty');
    }
    if($params['addonId'] > 0){
        $addon = Capsule::table('tblhostingaddons')->where('id', $params['addonId'])->first();
    } else {
        try {
            $service = \WHMCS\Service\Service::findOrFail($params['serviceid']);
        } catch (Exception $e) {
            return 'failure';
        }
    }
    $type = $params['addonId'] > 0 ? 'addon' : 'product';
    $serviceId = $type == 'addon' ? $params['addonId'] : $params['serviceid'];
    try {
        $product = $client->products()->get($params['configoption1']);
        if($params['addonId']>0){
            $cycle = lcfirst($addon->billingcycle);
        } else {
            $cycle = lcfirst($service->billingcycle);
        }
        $response = $product->order($params['customfields']['IP'], $cycle);
            if ($response){
                $customField = Capsule::table('tblcustomfields')
                    ->where('type', $type)
                    ->where('relid', $type == 'addon' ? $addon->addonid : $params['pid'])
                    ->where('fieldname', 'licenseId')
                    ->first(['id']);
            if ($customField) {
                $customFieldValueExists = Capsule::table('tblcustomfieldsvalues')
                    ->where('relid', $serviceId)
                    ->where('fieldid', $customField->id)->count() > 0;
                if ($customFieldValueExists) {
                    Capsule::table('tblcustomfieldsvalues')
                        ->where('relid', $serviceId)
                        ->where('fieldid', $customField->id)
                        ->update(['value' => $response->id]);
                } else {
                    Capsule::table('tblcustomfieldsvalues')->insert([
                        'relid' => $serviceId,
                        'fieldid' => $customField->id,
                        'value' => $response->id,
                    ]);
                }
            }
            if($type == 'product'){
                /** @var $server Server */
                $params['model']->serviceProperties->save([
                    'domain' => $params['customfields']['IP'],
                ]);
            }
            return 'success';
        }
    } catch (APIException $e) {
        return $e->getMessage();
    }
    return GoldenSource_translate('failedUnknownError');
}

function GoldenSource_Renew(array $params)
{
    global $_LANG;
    if (empty($params['customfields']['licenseId'])) {
        return GoldenSource_translate('noLicenseAssigned');
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
        if (!$license) {
            return GoldenSource_translate('licenseNotFound');
        }
        if($params['addonId']>0){
            $addon = Capsule::table('tblhostingaddons')->where('id', $params['addonId'])->first();
        } else {
            try {
                $service = \WHMCS\Service\Service::findOrFail($params['serviceid']);
            } catch (Exception $e) {
                return 'failure';
            }
        }
        if($params['addonId']>0){
            $cycle = lcfirst($addon->billingcycle);
        } else {
            $cycle = lcfirst($service->billingcycle);
        }
        $license->changeCycle($cycle);
        if ($license->renew()) {
            return 'success';
        }
    } catch (APIException $e) {
        return $e->getMessage();
    }
    return GoldenSource_translate('failedUnknownError');
}

function GoldenSource_SuspendAccount(array $params)
{
    global $_LANG;
    if (empty($params['customfields']['licenseId'])) {
        return GoldenSource_translate('noLicenseAssigned');
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
        if (!$license) {
            return GoldenSource_translate('licenseNotFound');
        }
        if ($license->status == License::STATUS_ACTIVE) {
            if ($license->changeStatus(License::STATUS_SUSPENDED)) {
                return 'success';
            }
        } else if($license->status == License::STATUS_SUSPENDED){
            return 'success';
        } else {
            return GoldenSource_translate('licenseNotActive');
        }
    } catch (APIException $e) {
        return $e->getMessage();
    }
    return GoldenSource_translate('failedUnknownError');
}

function GoldenSource_UnsuspendAccount(array $params)
{
    global $_LANG;
    if (empty($params['customfields']['licenseId'])) {
        return GoldenSource_translate('noLicenseAssigned');
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
        if (!$license) {
            return GoldenSource_translate('licenseNotFound');
        }
        if ($license->status == License::STATUS_ACTIVE) {
            return 'success';
        }
        if ($license->status == License::STATUS_SUSPENDED) {
            if(strtotime($license->renewDate) < time()){
                if($params['addonId']>0){
                    $addon = Capsule::table('tblhostingaddons')->where('id', $params['addonId'])->first();
                    $cycle = lcfirst($addon->billingcycle);
                } else {
                    try {
                        $service = \WHMCS\Service\Service::findOrFail($params['serviceid']);
                    } catch (Exception $e) {
                        return 'failure';
                    }
                    $cycle = lcfirst($service->billingcycle);
                }
                $license->changeCycle($cycle);
                $license->renew();
                return 'success';
            }
            if ($license->changeStatus(License::STATUS_ACTIVE)) {
                return 'success';
            }
        } else {
            return GoldenSource_translate('licenseNotSuspended');
        }
    } catch (APIException $e) {
        return $e->getMessage();
    }
    return GoldenSource_translate('failedUnknownError');
}

function GoldenSource_ChangeIPAdmin(array $params)
{
    global $_LANG;
    if (empty($params['customfields']['licenseId'])) {
        return GoldenSource_translate('noLicenseAssigned');
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
        if (!$license) {
            return GoldenSource_translate('licenseNotFound');
        }
        if ($license->status != License::STATUS_ACTIVE) {
            return GoldenSource_translate('licenseMustBeActiveToChangeIP');
        }
        if ($params['customfields']['IP'] == $license->ip) {
            return GoldenSource_translate('licenseNewIPIsSameAsBefore');
        }
        $result = $license->changeIP($params['customfields']['IP'], true);
        if ($result)
            return 'success';
    } catch (APIException $e) {
        return $e->getMessage();
    }
    return GoldenSource_translate('failedUnknownError');
}

function GoldenSource_renderTemplate($template, $vars){
    return PHPView::render(__DIR__ . '/include/templates/' . $template, $vars);
}

function GoldenSource_ClientArea(array $params)
{
    global $_LANG;
    if($params['status'] == 'Pending'){
        return;
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $t = function ($x) use ($_LANG) {
            return GoldenSource_translate($x);
        };
        if(empty($params['customfields']['licenseId'])){
            return GoldenSource_translate('noLicenseAssigned');
        }
        $license = $client->licenses()->get($params['customfields']['licenseId']);
        if (!$license)
            return GoldenSource_translate('noLicenseAssigned');

    } catch (APIException $e) {
        return $e->getMessage();
    }
    $vars = [];
    $vars['settings'] = Capsule::table('mod_GoldenSource_settings')->first();
    $vars['license'] = $license;
    $vars['allowChangeIP'] = $params['configoption2'] == 'on';
    $vars['free_change_ip'] = $params['configoption3'];
    $vars['serviceId'] = $params['serviceid'];
    $vars['installationHelp'] = [];
    $pdo = Capsule::connection()->getPdo();
    $licenseDuplicates = $pdo->query("SELECT COUNT(cfv.id) FROM tblcustomfields cf LEFT JOIN tblcustomfieldsvalues cfv ON cfv.fieldid=cf.id LEFT JOIN tblhosting h ON h.id=cfv.relid WHERE cf.type='product' AND cf.fieldname LIKE 'licenseId%%' AND cfv.value={$license->id} AND h.domainstatus IN('Active', 'Suspended')")->fetchColumn();
    $licenseDuplicates += $pdo->query("SELECT COUNT(cfv.id) FROM tblcustomfields cf LEFT JOIN tblcustomfieldsvalues cfv ON cfv.fieldid=cf.id LEFT JOIN tblhostingaddons h ON h.id=cfv.relid WHERE cf.type='product' AND cf.fieldname LIKE 'licenseId%%' AND cfv.value={$license->id} AND h.status IN('Active', 'Suspended')")->fetchColumn();
    if($licenseDuplicates>1){
        return $licenseDuplicates . GoldenSource_translate('DuplicateLicenseFound');
    }
    if ($license->status == 'active' && isset($_REQUEST['modop'], $_REQUEST['a']) && $_REQUEST['modop'] == 'custom') {
        try {
            switch ($_REQUEST['a']) {
                case 'changeIP':
                    if(!$vars['allowChangeIP']){
                        break;
                    }
                    if($license->changeIP < $vars['free_change_ip']){
                        $response = $license->changeIP($_POST['newIP']);
                        $vars['success'] = GoldenSource_translate('IP address was changed successfully');
                    } else if($vars['settings']->change_ip_price > 0) {
                        $whmcsClient = Capsule::table('tblclients')->where('id', $params['userid'])->first();
                        if($whmcsClient->credit < $vars['settings']->change_ip_price){
                            $vars['error'] = GoldenSource_translate('Not enough credit');
                        } else {
                            if(Capsule::table('tblclients')->where('id', $whmcsClient->id)->decrement('credit', $vars['settings']->change_ip_price)){
                                try {
                                    $invoice = call_user_func_array('localAPI', ['CreateInvoice', [
                                        'userid' => $whmcsClient->id,
                                        'status' => 'Paid',
                                        'date' => date('Y-m-d'),
                                        'duedate' => date('Y-m-d'),
                                        'itemdescription1' => GoldenSource_translate('License Change IP'),
                                        'itemamount1' => $vars['settings']->change_ip_price,
                                        'autoapplycredit' => 0,
                                    ]]);
                                    try {
                                        $response = $license->changeIP($_POST['newIP'], true);
                                        $vars['success'] = GoldenSource_translate('IP address was changed successfully');
                                        Capsule::table('tblcredit')->insert([
                                            'clientid' => $whmcsClient->id,
                                            'amount' => -$vars['settings']->change_ip_price,
                                            'date' => date('Y-m-d'),
                                            'description' => 'Credit Applied to Invoice #' . $invoice['id'],
                                            'relid' => 0,
                                        ]);
                                    } catch(\Exception $e){
                                        Capsule::table('tblorders')->where('id', $orderId)->delete();
                                        Capsule::table('tblinvoiceitems')->where('invoiceid', $invoice['id'])->delete();
                                        Capsule::table('tblinvoices')->where('id', $invoice['id'])->delete();
                                        Capsule::table('tblclients')->where('id', $whmcsClient->id)->increment('credit', $vars['settings']->change_ip_price);
                                        $vars['error'] = $e->getMessage();
                                    }
                                } catch(\Exception $e){
                                    Capsule::table('tblclients')->where('id', $whmcsClient->id)->increment('credit', $vars['settings']->change_ip_price);
                                    $vars['error'] = $e->getMessage();
                                }
                            }
                        }
                    }
                    break;
            }
        } catch (APIException $e){
            $vars['error'] = $e->getMessage();
        }
    }

    $information = $client->information();
    foreach($license->product()->installationHelp as $os => $commands){
        $commands = trim($commands);
        if($information->dedicatedLink){
            $commands = preg_replace('/([A-Za-z0-9]+)\.configserver\.pro/', $information->dedicatedLink, $commands);
            $commands = preg_replace('/configserver\.pro/', $information->dedicatedLink, $commands);
        }
        $vars['installationHelp'][] = (object)['os' => $os, 'commands' => $commands];
    }
    if(is_file(__DIR__ . '/include/templates/clientarea_custom.php')){
        return GoldenSource_renderTemplate('clientarea_custom', $vars);
    }
    return GoldenSource_renderTemplate('clientarea', $vars);
}

function GoldenSource_AdminServicesTabFields(array $params)
{
    if (empty($params['customfields']['licenseId'])) {
        return [
            GoldenSource_translate('LicenseInfo') => GoldenSource_translate('noLicenseAssigned'),
        ];
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
    } catch (APIException $e) {
        return [
            GoldenSource_translate('LicenseInfo') => 'Failed to process: ' . $e->getMessage(),
        ];
    }
    if(!$license){
        return [
            GoldenSource_translate('LicenseInfo') => 'No info was found for this license.',
        ];
    }
    $serverId = GoldenSource_getServerIdByParams($params);
    try {
        $product = $license->product();
        return [
            GoldenSource_translate('product') => $product->fullName,
            GoldenSource_translate('ipAddress') => sprintf('%s (%s)', $license->ip, $license->hostname),
            GoldenSource_translate('status') => GoldenSource_translate(ucfirst($license->status)),
            GoldenSource_translate('licenseKey') => $license->licenseKey,
            GoldenSource_translate('renewDate') => $license->renewDate . ' (' . $license->remainingDays() . ' ' . GoldenSource_translate('days') . ')',
            GoldenSource_translate('cost') => sprintf('%s$ (%s)', $product->priceWithDiscount($license->cycle), GoldenSource_translate($license->cycle)),
            GoldenSource_translate('numberOfIPChanges') => $license->changeIP.'/3',
            GoldenSource_translate('licenseDetails') => '<a href="addonmodules.php?module=GoldenSource&serverId='.$serverId.'&licenseId='.$license->id.'" target="_blank">» '.GoldenSource_translate('licenseDetails').'</a>',
        ];
    } catch (APIException $e) {
        return [
            GoldenSource_translate('LicenseInfo') => 'Failed to process: ' . $e->getMessage(),
        ];
    }
}

function GoldenSource_TerminateAccount(array $params){
    global $_LANG;
    if (empty($params['customfields']['licenseId'])) {
        return GoldenSource_translate('noLicenseAssigned');
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
        if (!$license) {
            return GoldenSource_translate('licenseNotFound');
        }
        $type = $params['addonId'] > 0 ? 'addon' : 'product';
        $serviceId = $type == 'addon' ? $params['addonId'] : $params['serviceid'];
        if($params['addonId']>0){
            $relid = Capsule::table('tblhostingaddons')->where('id', $params['addonId'])->first()->addonid;
        } else {
            $relid = $params['pid'];
        }
        $customField = Capsule::table('tblcustomfields')
            ->where('type', $type)
            ->where('relid', $relid)
            ->where('fieldname', 'licenseId')
            ->first(['id']);
        if ($customField) {
            Capsule::table('tblcustomfieldsvalues')->where('relid', $serviceId)->where('fieldid', $customField->id)->delete();
        }
        return 'success';
    } catch (APIException $e) {
        return $e->getMessage();
    }
    return GoldenSource_translate('failedUnknownError');
}

function GoldenSource_SyncWithCSP(array $params){
    global $_LANG;
    if (empty($params['customfields']['licenseId'])) {
        return GoldenSource_translate('noLicenseAssigned');
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
        if (!$license) {
            return GoldenSource_translate('licenseNotFound');
        }
        if($params['addonId']==0){
            /** @var $server Server */
            $params['model']->serviceProperties->save([
                'domain' => $license->ip,
            ]);
        }
        if($params['addonId']>0){
            Capsule::table('tblhostingaddons')->where('id', $params['addonId'])->update([
                'nextduedate' => $license->renewDate
            ]);
        } else {
            Capsule::table('tblhosting')->where('id', $params['serviceid'])->update([
                'nextduedate' => $license->renewDate
            ]);
        }
        return 'success';
    } catch (APIException $e) {
        return $e->getMessage();
    }
    return GoldenSource_translate('failedUnknownError');
}

function GoldenSource_AdminCustomButtonArray(array $params)
{
    global $_LANG;
    if (empty($params['customfields']['licenseId'])) {
        return [];
    }
    $client = GoldenSource_getClientByParams($params);
    try {
        $license = $client->licenses()->get($params['customfields']['licenseId']);
    } catch (APIException $e) {
        return [];
    }
    return [
        GoldenSource_translate('SyncWithCSP') => 'SyncWithCSP',
        ($license->changeIP < 3 ?  GoldenSource_translate('ChangeIP') :  GoldenSource_translate('ChangeIPWith2$')) => 'ChangeIPAdmin',
    ];
}
