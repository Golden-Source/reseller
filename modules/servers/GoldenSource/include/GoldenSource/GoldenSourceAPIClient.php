<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.Pro)
 */
namespace GoldenSource;

use GoldenSource\Clients\CurlClient;
use GoldenSource\Clients\CurlResponse;
use GoldenSource\Models\Information;
use GoldenSource\Models\Licenses\Licenses;
use GoldenSource\Models\Products\Products;
use function property_exists;
use Psr\Http\Message\ResponseInterface;

class GoldenSourceAPIClient
{
    const VERSION = "1.0";

    /**
     * @var string
     */
    protected $apiToken;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $userAgent;

    /**
     * The default instance of the HTTP client, for easily getting it in the child models.
     * @var GoldenSourceAPIClient
     */
    public static $instance;

    /**
     * @var \GoldenSource\Clients\CurlClient
     */
    protected $httpClient;

    /**
     *
     * @param $apiToken
     * @param $baseUrl
     * @param $userAgent
     */
    public function __construct($apiToken, $baseUrl = 'https://GoldenSource.pro/api/v2/', $userAgent = '')
    {
        $this->apiToken = $apiToken;
        $this->baseUrl = $baseUrl;
        $this->userAgent = $userAgent;
        $this->httpClient = new CurlClient($this);
        self::$instance = $this;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return GuzzleClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param CurlResponse $response
     * @return mixed|string
     * @throws APIException
     */
    public static function checkResponse(CurlResponse $response)
    {
        if ($response->getStatusCode() == 401) {
            throw new APIException(APIResponse::create(['error' => 'Invalid access token.']), 'Invalid access token.');
        }
        $body = $body2 = $response->getResponse();
        if (strlen($body) <= 0) {
            throw new APIException(APIResponse::create(['response' => $response]), 'The response is not parseable.');
        }
        $body = \json_decode($body);
        if(json_last_error() != JSON_ERROR_NONE){
            throw new APIException(APIResponse::create(['response' => $response]), $body2);
        }
        if (isset($body->error) && $body->error) {
            throw new APIException(APIResponse::create([
                'code' => $body->error->errorCode,
                'message' => $body->error->message,
            ]), $body->error->message);
        }
        if (!property_exists($body, 'data')) {
            throw new APIException(APIResponse::create(['response' => $response]), 'No data is provided.');
        }
        return $body->data;
    }


    public function products()
    {
        return new Products($this);
    }

    public function licenses()
    {
        return new Licenses($this);
    }

    /**
     * @return bool
     * @throws APIException
     */
    public function ping()
    {
        $response = self::checkResponse($this->httpClient->get('ping'));
        if (property_exists($response, 'success') && $response->success) {
            return true;
        }
        return false;
    }

    /**
     * @throws APIException
     */
    public function information()
    {
        $response = self::checkResponse($this->httpClient->get('information'));
        return Information::parse($response, $this);
    }

    public function __destruct(){
        $this->httpClient->close();
    }
}