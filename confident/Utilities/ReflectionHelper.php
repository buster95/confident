<?php

namespace Confident\Utilities;

use Exception;
use ReflectionFunction;

class ReflectionHelper
{
    public static function getParameters($callback)
    {
        if (is_callable($callback)) {
            $reflection = new ReflectionFunction($callback);
            $funcParams = $reflection->getParameters();
            $params = array();
            foreach ($funcParams as $prm) {
                array_push($params, $prm->name);
            }
            return $params;
        }
        throw new Exception("ReflectionHelper parameter is not a function", 1);
    }
}
