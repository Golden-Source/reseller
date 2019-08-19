<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
namespace GoldenSource\Models\Licenses;

use GoldenSource\GoldenSourceAPIClient;
use GoldenSource\Models\Model;
use function property_exists;
use function strtolower;

class License extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_EXPIRED = 'expired';

    public $id;
    public $productId;
    public $status;
    public $renewDate;
    public $hostname;
    public $licenseKey;
    public $type;
    public $ostype;
    public $cycle;
    public $ip;
    public $os;
    public $kernel;
    public $changeIP;
    public $autoRenew;
    public $notes;
    public $suspendedReason;
    public $lastRenew;
    public $changeIP_logs = [];

    private $product;

    public function __construct($id, GoldenSourceAPIClient $APIClient)
    {
        $this->id = $id;
        parent::__construct($APIClient);
    }

    /**
     * @return bool|\GoldenSource\Models\Products\Product
     * @throws \GoldenSource\APIException
     */
    public function product()
    {
        if (!$this->product) {
            $this->product = $this->apiClient->products()->get($this->productId);
        }
        return $this->product;
    }

    public function remainingDays($full = false){
        $timeLeft = strtotime($this->renewDate) - time();
        if($full){
            if($timeLeft < 0){
                return '-';
            }
            $days = floor($timeLeft / 86400);
            $hours = floor(($timeLeft % 86400) / 3600);
            $minutes = floor(($timeLeft % 3600) / 60);
            $seconds = $timeLeft % 60;

            return sprintf(
                '%sd + %s:%s:%sh', $days, 
                $hours < 10 ? '0'.$hours : $hours, 
                $minutes < 10 ? '0'.$minutes : $minutes, 
                $seconds < 10 ? '0'.$seconds : $seconds
            );
        }
        return ceil($timeLeft / 86400);
    }

    /**
     * @param $status
     * @return bool
     * @throws \GoldenSource\APIException
     */
    public function changeStatus($status)
    {
        $data = [
            'status' => $status,
        ];
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('licenses/' . $this->id . '/changeStatus', [
            'form_params' => $data,
        ]));
        if (property_exists($response, 'success') && $response->success) {
            $this->status = strtolower($response->status);
            return true;
        }
        return false;
    }

    /**
     * @param $newIP
     * @return bool
     * @throws \GoldenSource\APIException
     */
    public function changeIP($newIP, $force = false)
    {
        $data = [
            'newIP' => $newIP,
            'force' => $force,
        ];
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('licenses/' . $this->id . '/changeIP', [
            'form_params' => $data,
        ]));
        if (property_exists($response, 'success') && $response->success) {
            $this->ip = $newIP;
            $this->changeIP++;
            return $response;
        }
        return $response->approveRequired ? 'approvedRequired' : false;
    }

    /**
     * @param $cycle
     * @return bool
     * @throws \GoldenSource\APIException
     */
    public function changeCycle($cycle)
    {
        $data = [
            'cycle' => $cycle,
        ];
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('licenses/' . $this->id . '/changeCycle', [
            'form_params' => $data,
        ]));
        if (property_exists($response, 'success') && $response->success) {
            $this->cycle = $response->cycle;
            return true;
        }
        return false;
    }

    /**
     * @param $cycle
     * @return bool
     * @throws \GoldenSource\APIException
     */
    public function updateNotes($notes)
    {
        $data = [
            'notes' => $notes,
        ];
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('licenses/' . $this->id . '/updateNotes', [
            'form_params' => $data,
        ]));
        if (property_exists($response, 'success') && $response->success) {
            $this->notes = $response->notes;
            return true;
        }
        return false;
    }

    /**
     * @param $cycle
     * @return bool
     * @throws \GoldenSource\APIException
     */
    public function changeOS($os)
    {
        $data = [
            'os' => $os,
        ];
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('licenses/' . $this->id . '/changeOS', [
            'form_params' => $data,
        ]));
        if (property_exists($response, 'success') && $response->success) {
            $this->os = $response->os;
            return true;
        }
        return false;
    }

    /**
     * @throws \GoldenSource\APIException
     */
    public function renew()
    {
        if(!$this->lastRenew){
            return false;
        }
        if((time()-$this->lastRenew) < 90){
            return true;
        }
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->get('licenses/' . $this->id . '/renew'));
        if (property_exists($response, 'success') && $response->success) {
            $this->renewDate = $response->renewDate;
            $this->status = strtolower($response->status);
            return $response;
        }
        return false;
    }

    public function renewDate($includeHour = false){
        return date('Y-m-d' . ($includeHour ? ' H:i' : null), strtotime($this->renewDate));
    }

    public static function parse($input, GoldenSourceAPIClient $APIClient)
    {
        $obj = new self($input->id, $APIClient);
        $obj->productId = $input->productId;
        $obj->status = strtolower($input->status);
        $obj->renewDate = $input->renewDate;
        $obj->cycle = $input->cycle;
        $obj->type = $input->type;
        $obj->ostype = $input->ostype;
        $obj->hostname = $input->hostname;
        $obj->licenseKey = $input->licenseKey;
        $obj->changeIP = $input->changeip;
        $obj->kernel = $input->kernel;
        $obj->autoRenew = $input->autoRenew;
        $obj->ip = $input->ip;
        $obj->os = $input->os;
        $obj->notes = $input->notes;
        $obj->suspendedReason = $input->suspendedReason;
        $obj->changeIP_logs = [];
        if(sizeof($input->changeipLogs)){
            foreach($input->changeipLogs as $changeIP){
                preg_match("/Changed from ([0-9\.]+) to ([0-9\.]+)/", $changeIP->description, $matches);
                $obj->changeIP_logs[] = [
                    'from' => $matches[1],
                    'to' => $matches[2],
                    'date' => date('Y-m-d H:i:s', (new \DateTime($changeIP->date->date, new \DateTimeZone($changeIP->date->timezone)))->getTimestamp()),
                ];
            }
        }
        if(property_exists($input, 'latestrenewed')){
            $obj->lastRenew = (new \DateTime($input->latestrenewed))->getTimestamp();
        }
        return $obj;
    }
}