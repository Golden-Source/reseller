<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */

namespace GoldenSource\Models\Licenses;

use GoldenSource\GoldenSourceAPIClient;
use GoldenSource\Models\Model;

class Licenses extends Model
{
    /**
     * @param null $ip
     * @param null $status
     * @return array
     * @throws \GoldenSource\APIException
     */
    public function all($criteria = [])
    {
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('licenses', [
            'form_params' => $criteria,
        ]));
        $result = [];
        foreach ($response->licenses as $license) {
            $result[] = License::parse($license, $this->apiClient);
        }
        usort($result, function($x, $y){
            return $x->productId == $y->productId ? strcmp($x->hostname, $y->hostname) : ($x->productId > $y->productId ? 1 : -1);
        });
        return $result;
    }

    /**
     * @param $licenseID
     * @return bool|License
     * @throws \GoldenSource\APIException
     */
    public function get($licenseID)
    {
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->get('licenses/' . $licenseID));
        if (sizeof($response->licenses)) {
            return License::parse($response->licenses[0], $this->apiClient);
        }
        return false;
    }
}