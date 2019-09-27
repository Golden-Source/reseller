<?php
/**
 * GoldenSource module by Amirhossein Matini (matiniamirhossein@gmail.com) 
 * © All right reserved for GoldenSource Team (GoldenSource.ir)
 */
namespace GoldenSource;
/**
 *
 */
class APIException extends \Exception
{
    /**
     * @var APIResponse
     */
    protected $response;

    /**
     * APIException constructor.
     *
     * @param $response
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(APIResponse $response, $message = "", $code = 0, \Throwable $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return APIResponse
     */
    public function getApiResponse()
    {
        return $this->response;
    }
}
