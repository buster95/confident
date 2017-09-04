<?php
namespace Confident\ApiObjects\Middleware;

use Exception;
use Confident\ApiObjects\Interfaces\MiddlewareInterface;
use Confident\Utilities\JsonHelper;
use Confident\Utilities\ReflectionHelper;

class BasicAuth implements MiddlewareInterface
{
    private $callback;
    private $callbackParams = array();
    private $extraParams = array();
    private $statusOnFail = 200;
    private $failmessage;
    private $formatmessage;

    private $statusCodes = array(
        // Informational 1xx
        100 => 'Continue',101 => 'Switching Protocols',

        // Success 2xx
        200 => 'OK', 201 => 'Created', 202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content',

        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',

        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );

    public function __construct($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception("Middleware BasicAuth need a callback", 1);
        }
        $this->callback = $callback;
        $this->callbackParams = ReflectionHelper::getParameters($callback);
        // if (sizeof($this->callbackParams)!==1) {
        //     throw new Exception("Middleware BasicAuth need just one parameter", 1);
        // }
    }

    public static function Authorization()
    {
        if (isset(apache_request_headers()['Authorization'])) {
            return apache_request_headers()['Authorization'];
        }
        return null;
    }

    public function setParameter($key, $value)
    {
        if (is_string($key)) {
            $this->extraParams[$key] = $value;
        } else {
            throw new Exception("Param name should be not a string", 1);
        }
        return $this;
    }

    public function run()
    {
        $callparams = array();
        $counter = 0;
        foreach ($this->callbackParams as $key) {
            if ($counter===0) {
                $callparams[$key] = self::Authorization();
                $counter++;
            } elseif (isset($this->extraParams[$key])) {
                $callparams[$key] = $this->extraParams[$key];
            } else {
                $callparams[$key] = null;
            }
        }
        $callResult = call_user_func_array($this->callback, $callparams);
        if (is_bool($callResult)) {
            return $callResult;
        }
        return false;
    }

    public function setMessageOnFail($message, $format = "JSON")
    {
        $this->failmessage = $message;
        $this->formatmessage = strtolower($format);
        return $this;
    }

    public function setStatusOnFail($status)
    {
        if (is_int($status)) {
            if (isset($this->statusCodes[$status])) {
                $this->statusOnFail = $status;
                return $this;
            }
            throw new Exception("Status code not exists", 1);
        }
        throw new Exception("Status code should be a integer number", 1);
    }

    public function showFailMessage()
    {
        http_response_code($this->statusOnFail);
        if ($this->failmessage===null) {
            $class = get_class($this);
            $this->failmessage = $class . " => fail middleware basic authentication";
        }
        switch ($this->formatmessage) {
            case 'json':
            default:
                header('Content-Type: application/json');
                echo JsonHelper::Serialize(array('message' => $this->failmessage));
                break;
                
            case 'xml':
                header('Content-Type: application/xml');
                echo '<?xml version="1.0" encoding="UTF-8"?>';
                echo '<message>'.$this->failmessage.'</message>';
                break;
        }
    }
}
