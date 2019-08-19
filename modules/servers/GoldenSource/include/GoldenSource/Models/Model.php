<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */

namespace GoldenSource\Models;

use GoldenSource\Clients\GuzzleClient;
use GoldenSource\GoldenSourceAPIClient;
use const JSON_PRETTY_PRINT;

abstract class Model
{
    /** @var GoldenSourceAPIClient */
    protected $apiClient;
    /** @var GuzzleClient */
    protected $httpClient;

    /**
     * Model constructor.
     * @param GoldenSourceAPIClient $APIClient
     */
    public function __construct(GoldenSourceAPIClient $APIClient)
    {
        $this->apiClient = $APIClient;
        $this->httpClient = $APIClient->getHttpClient();
    }

    /**
     * @param $input
     * @param GuzzleClient $httpClient
     */
    public static function parse($input, GoldenSourceAPIClient $APIClient)
    {
    }

    public function __toString()
    {
        return \GuzzleHttp\json_encode($this, JSON_PRETTY_PRINT);
    }
}