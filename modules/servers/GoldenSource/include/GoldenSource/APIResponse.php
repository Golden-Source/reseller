<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.ir)
 */
namespace GoldenSource;
use GoldenSource\Models\Model;
/**
 * Class ApiResponse
 * @package GoldenSource
 */
class APIResponse
{
    /**
     * @var array
     */
    protected $response = [];

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string|null $resource
     * @return Model|string|boolean
     */
    public function getResponsePart($resource = null)
    {
        return (array_key_exists($resource, $this->response)) ? $this->response[$resource] : false;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response)
    {
        $this->response = $response;
    }

    /**
     * @param array $response
     * @return APIResponse
     */
    public static function create(array $response)
    {
        $apiResponse = new APIResponse();
        $apiResponse->setResponse($response);
        return $apiResponse;
    }
}
