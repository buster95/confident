<?php

namespace Confident\Utilities;

use Exception;
use Confident\Utilities\JsonHelper;

define('REQ_METHOD', strtoupper($_SERVER['REQUEST_METHOD']));

if (isset($_SERVER['CONTENT_LENGTH'])) {
    define('REQ_LENGTH', intval($_SERVER['CONTENT_LENGTH']));
} else {
    define('REQ_LENGTH', 0);
}

if (isset($_SERVER['CONTENT_TYPE'])) {
    if (stripos($_SERVER['CONTENT_TYPE'], 'json')>-1) {
        define('REQ_BODY_TYPE', 'application/json');
    } elseif (stripos($_SERVER['CONTENT_TYPE'], 'x-www-form-urlencoded')>-1) {
        define('REQ_BODY_TYPE', 'application/x-www-form-urlencoded');
    } elseif (stripos($_SERVER['CONTENT_TYPE'], 'multipart/form-data')>-1) {
        define('REQ_BODY_TYPE', 'multipart/form-data');
    } else {
        define('REQ_BODY_TYPE', strtolower($_SERVER['CONTENT_TYPE']));
    }
} else {
    define('REQ_BODY_TYPE', 'nothing');
}

class BodyHelper
{
    private static $body = '';
    private static $params = array();

    private static function bodyRawJson(array &$data)
    {
        $data = JsonHelper::Deserialize(self::$body);
    }

    private static function bodyURLEncoded(array &$data)
    {
        parse_str(self::$body, $data);
    }

    private static function bodyFormData(array &$data)
    {
        $input = self::$body;
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
        if (!count($matches)) {
            parse_str(urldecode($input), $data);
            return $data;
        }
    
        $boundary = $matches[1];
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        foreach ($a_blocks as $id => $block) {
            if (empty($block)) {
                continue;
            }
    
            if (strpos($block, 'application/octet-stream') !== false) {
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                $data['files'][$matches[1]] = $matches[2];
            } else {
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                $data[$matches[1]] = $matches[2];
            }
        }
    }

    public static function getParameters()
    {
        // var_dump(REQ_METHOD);
        // var_dump(REQ_LENGTH);
        // var_dump(REQ_BODY_TYPE);
        // var_dump($_POST);

        switch (REQ_METHOD) {
            case 'GET':
                self::$params = $_GET;
                break;
                        
            case 'POST':
            case 'PUT':
            case 'DELETE':
                self::$body = file_get_contents('php://input', false, null, -1, REQ_LENGTH);
                switch (REQ_BODY_TYPE) {
                    case 'application/json':
                        self::bodyRawJson(self::$params);
                        break;
                        
                    case 'application/x-www-form-urlencoded':
                        self::bodyURLEncoded(self::$params);
                        break;
                            
                    case 'multipart/form-data':
                        if (REQ_METHOD==='POST') {
                            self::$params = $_POST;
                        } else {
                            self::bodyFormData(self::$params);
                        }
                        break;
                }
                break;
        }
        return self::$params;
    }
}
