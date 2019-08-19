<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
namespace GoldenSource\Clients;

use GoldenSource\GoldenSourceAPIClient;

class CurlClient
{
    private $client, $curl;
    public function __construct(GoldenSourceAPIClient $client)
    {
        $this->client = $client;
        $this->curl = curl_init();
        $headers = array(
            'Accept: application/json',
            'Authorization: Bearer ' . $client->getApiToken(),
        );
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);
    }

    public function get($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->client->getBaseUrl() . $url);
        return new CurlResponse($this->curl);
    }

    function http_build_query_for_curl( $arrays, &$new = array(), $prefix = null ) {

        if ( is_object( $arrays ) ) {
            $arrays = get_object_vars( $arrays );
        }
    
        foreach ( $arrays AS $key => $value ) {
            $k = isset( $prefix ) ? $prefix . '[' . $key . ']' : $key;
            if ( is_array( $value ) OR is_object( $value )  ) {
                $this->http_build_query_for_curl( $value, $new, $k );
            } else {
                $new[$k] = $value;
            }
        }

        return $new;
    }

    public function post($url, $params = [])
    {
        if(isset($params['form_params'])){
            $params = $params['form_params'];
        }
        curl_setopt($this->curl, CURLOPT_URL, $this->client->getBaseUrl() . $url);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->http_build_query_for_curl($params));


        return new CurlResponse($this->curl);
    }

    public function close(){
        curl_close($this->curl);
    }
}