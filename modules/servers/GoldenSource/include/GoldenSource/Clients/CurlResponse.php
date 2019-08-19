<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
namespace GoldenSource\Clients;

use GoldenSource\GoldenSourceAPIClient;

class CurlResponse
{
    private $statusCode, $response;
    public function __construct($curl)
    {
        $this->response = curl_exec($curl);
        $this->statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    }

    public function getStatusCode(){
        return $this->statusCode;
    }
    
    public function getResponse(){
        return $this->response;
    }
}