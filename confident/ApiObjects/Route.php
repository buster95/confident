<?php

namespace Confident\ApiObjects;

use Exception;
use Confident\Utilities\BodyHelper;
use Confident\Utilities\ReflectionHelper;
use Confident\Utilities\UrlHelper;

class Route
{
    private $url;
    private $method;
    private $callback;
    private $params = array();
    private $middlewares = array();

    public function setURL($url)
    {
        if (!is_string($url)) {
            throw new Exception("ApiController Route Url is not a string", 1);
        }
        $this->url = $url;
    }

    public function getURL()
    {
        return $this->url;
    }

    public function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception("ApiController Route Callback is not a function", 1);
        }
        $this->callback = $callback;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setMethod($method = 'GET')
    {
        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $this->method = $method;
                break;
            
            default:
                throw new Exception("ApiController Route method is not valid", 1);
                break;
        }
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function add($middleware)
    {
        $class = get_class($middleware);
        switch ($class) {
            case 'Confident\ApiObjects\Middleware\BasicAuth':
                $this->middlewares[] = clone($middleware);
                return $this;
                break;

            default:
                throw new Exception($class." is not a middleware", 1);
                break;
        }
    }

    private function runMiddlewares()
    {
        foreach ($this->middlewares as $middleware) {
            $middleResult = $middleware->run();
            if (is_bool($middleResult)) {
                if ($middleResult===false) {
                    $middleware->showFailMessage();
                    return false;
                }
            } else {
                $middleware->showFailMessage();
                return false;
            }
        }
        return true;
    }
    
    public function execute()
    {
        // EJECUTANDO MIDDLEWARES
        if ($this->runMiddlewares()) {
            //BODY PARAMETERS START
            $bodyparams = BodyHelper::getParameters();
            //BODY PARAMETERS END
        
            // URL PARAMETERS START
            $urlparams = UrlHelper::getParameters($this->url);
            // URL PARAMETERS END

            // CALLBACK PARAMETERS START
            $callparams = ReflectionHelper::getParameters($this->callback);
            // CALLBACK PARAMETERS END

            foreach ($callparams as $value) {
                if (isset($bodyparams[$value])) {
                    $this->params[$value] = $bodyparams[$value];
                } elseif (isset($urlparams[$value])) {
                    $this->params[$value] = $urlparams[$value];
                } else {
                    $this->params[$value] = null;
                }
            }

            if (sizeof($this->params) > 0) {
                call_user_func_array($this->callback, $this->params);
            } else {
                call_user_func($this->callback);
            }
        }
    }
}
