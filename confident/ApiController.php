<?php
namespace Confident;

use ReflectionFunction;
use Confident\ApiObjects\Route;
use Confident\Http\Body;
use Confident\Http\Request;
use Confident\Http\Response;
use Confident\Utilities\BodyHelper;
use Confident\Utilities\ReflectionHelper;
use Confident\Utilities\UrlHelper;

define('CURRENT_HOST', $_SERVER["HTTP_HOST"]);
define('CURRENT_URL', $_SERVER["REQUEST_URI"]);
define('CURRENT_METHOD', $_SERVER['REQUEST_METHOD']);
define('CURRENT_PATH', UrlHelper::getUrl());

class ApiController
{
    private $apiRoutes = array();
    private $params = array();
    
    private function mapping($method, $url, $call)
    {
        $route = new Route();
        $route->setURL(strtolower($url));
        $route->setCallback($call);
        $route->setMethod(strtoupper($method));
        $this->apiRoutes[] = $route;
        return $route;
    }

    private function existsPath()
    {
        foreach ($this->apiRoutes as $route) {
            $routeUrl = $route->getURL();
            $routeMethod = $route->getMethod();
            
            if (stripos($routeUrl, '{')>-1) {
                if (UrlHelper::CompareUrl($routeUrl, CURRENT_PATH) && CURRENT_METHOD===$routeMethod) {
                    return true;
                }
            } else {
                if (CURRENT_PATH===$routeUrl && CURRENT_METHOD===$routeMethod) {
                    return true;
                }
            }
        }
        return false;
    }

    public function get($url, $call, $middlewares = array())
    {
        return $this->mapping('GET', $url, $call);
    }

    public function post($url, $call)
    {
        return $this->mapping('POST', $url, $call);
    }

    public function put($url, $call)
    {
        return $this->mapping('PUT', $url, $call);
    }

    public function delete($url, $call)
    {
        return $this->mapping('DELETE', $url, $call);
    }

    private function MethodNotSupported()
    {
        header('Content-Type: application/json');
        http_response_code(405);
        echo json_encode(array('message'=>'Error la url no soporta este metodo'));
    }

    private function NotFound()
    {
        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(array('message'=>'Error ruta no encontrada'));
    }

    public function start()
    {
        $existePath = $this->existsPath();
        foreach ($this->apiRoutes as $route) {
            $routeUrl = $route->getURL();
            $routeMethod = $route->getMethod();

            if (stripos($routeUrl, '{')>-1) {
                if (UrlHelper::CompareUrl($routeUrl, CURRENT_PATH)) {
                    if ($routeMethod===CURRENT_METHOD) {
                        $route->execute();
                        return;
                    } elseif (!$existePath) {
                        $this->MethodNotSupported();
                        return;
                    }
                }
            } else {
                if (CURRENT_PATH===$routeUrl && CURRENT_METHOD===$routeMethod) {
                    $route->execute();
                    return;
                } elseif (!$existePath) {
                    $this->MethodNotSupported();
                    return;
                }
                
                // elseif (CURRENT_PATH===$routeUrl && CURRENT_METHOD!==$routeMethod) {
                //     if (CURRENT_PATH==='/') {
                //         $route->execute();
                //         return;
                //     }
                    
                //     if (!$existePath) {
                //         $this->MethodNotSupported();
                //         return;
                //     }
                // }
            }
        }
        $this->NotFound();
    }

    public function stop()
    {
        exit;
    }
}
