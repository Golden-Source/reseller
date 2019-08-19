<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
namespace GoldenSource\Models\Products;

use GoldenSource\GoldenSourceAPIClient;
use GoldenSource\Models\Model;

class Products extends Model
{
    /**
     * @throws \GoldenSource\APIException
     */
    public function all()
    {
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->post('products'));
        $result = [];
        foreach ($response->products as $product) {
            $result[$product->id] = Product::parse($product, $this->apiClient);
        }
        return $result;
    }

    /**
     * @param $id
     * @return bool|Product
     * @throws \GoldenSource\APIException
     */
    public function get($id)
    {
        $response = GoldenSourceAPIClient::checkResponse($this->httpClient->get('products/' . (int)$id));
        if (sizeof($response->products)) {
            return Product::parse($response->products[0], $this->apiClient);
        }
        return false;
    }
}