<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
namespace GoldenSource\Models\Products;

use GoldenSource\GoldenSourceAPIClient;
use GoldenSource\Models\Licenses\License;
use GoldenSource\Models\Model;

class Product extends Model
{
    public $id;
    public $type;
    public $fullName;
    public $osType;
    public $cost;
    public $discount;
    public $osOptions;
    public $installationHelp;


    public function __construct($id, GoldenSourceAPIClient $APIClient)
    {
        $this->id = $id;
        parent::__construct($APIClient);
    }

    public static function parse($input, GoldenSourceAPIClient $APIClient)
    {
        $obj = new self($input->id, $APIClient);
        $obj->type = $input->type;
        $obj->fullName = $input->fullname;
        $obj->osType = $input->ostype;
        $obj->cost = $input->cost;
        $obj->discount = $input->discount;
        $obj->osOptions = $input->osOptions;
        $obj->installationHelp = $input->installationHelp;
        return $obj;
    }

    public function priceWithDiscount($cycle)
    {
        $price = round($this->cost->{$cycle} * (1 - ($this->discount / 100)), 2);
        return number_format((float)$price, 2, '.', '');
    }

    /**
     * @param $ipAddress
     * @return bool|License
     * @throws \GoldenSource\APIException
     */
    public function order($ipAddress, $cycle)
    {
        $data = [
            'ip' => $ipAddress,
            'cycle' => $cycle,
        ];
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('products/'.$this->id.'/add', [
            'form_params' => $data,
        ]));
        if ($response->success) {
            return $this->apiClient->licenses()->get($response->licenseId);
        }
        return false;
    }
}
