<?php
namespace Confident\Utilities;

class UrlHelper
{
    private static function getURLVarName($string)
    {
        preg_match_all("/{(.*?)}/si", $string, $value);
        return $value[1];
    }
    
    private static function bracketSuffix($string)
    {
        preg_match_all("/(.*?)}/", $string, $value);
        $value = $value[1][0].'}';
        $value = str_replace($value, "", $string);
        return $value;
    }
    
    private static function bracketPreffix($string)
    {
        preg_match_all("/(.*?){/si", $string, $value);
        return $value[1][0];
    }

    public static function getUrl()
    {
        if (isset($_SERVER["PATH_INFO"])) {
            return strtolower(explode('?', $_SERVER["PATH_INFO"])[0]);
        } elseif (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
            $reqUri = $_SERVER['REQUEST_URI'];
            $script = $_SERVER['SCRIPT_NAME'];

            $scriptExplode = explode('/', $script);
            $scriptName = strtolower($scriptExplode[sizeof($scriptExplode)-1]);
            $scriptPath = str_replace('/'.$scriptName, '', $script);
            
            $reqUri = str_replace($scriptPath, '', $reqUri);
            return $reqUri;
        }
        return '/';
    }
    
    /**
    * Compara dos url y envia true o false segun su compatibilidad
    * @param String $urlTemplate Template
    * @param String $urlReal Real
    * @return Boolean
    */
    public static function CompareUrl($urlTemplate, $urlReal)
    {
        if ($urlTemplate === $urlReal) {
            return true;
        }

        if (stripos($urlTemplate, '/')===0) {
            $urlTemplate = substr($urlTemplate, 1);
        }

        if (stripos($urlReal, '/')===0) {
            $urlReal = substr($urlReal, 1);
        }
    
        $urlReal = explode('/', $urlReal);
        $urlTemplate = explode('/', $urlTemplate);
    
        if (sizeof($urlReal)===sizeof($urlTemplate)) {
            for ($i=0; $i < sizeof($urlReal); $i++) {
                if (substr_count($urlTemplate[$i], '{')>0) {
                    $keys = self::getURLVarName($urlTemplate[$i]);
                    if (sizeof($keys)>1) {
                        return false;
                    }
                    $urlkey = $keys[0];
                    $preffix = self::bracketPreffix($urlTemplate[$i]);
                    $suffix = self::bracketSuffix($urlTemplate[$i]);
                    $urlvalue = str_replace($preffix, "", $urlReal[$i]);
                    $urlvalue = str_replace($suffix, "", $urlvalue);
                    $urlTemplate[$i] = $preffix.$urlvalue.$suffix;
                }
            }
        }
    
        // RECONSTRUYENDO
        $urlReal = implode('/', $urlReal);
        $urlTemplate = implode('/', $urlTemplate);
        if ($urlReal===$urlTemplate) {
            return true;
        }
        return false;
    }

    /**
    * Obtiene los parametros de una url en relacion a otra url
    * @param String $urlTemplate Template
    * @param String $urlReal Real
    * @return Array
    */
    public static function getParameters($urlTemplate)
    {
        $params = array();
        if (stripos($urlTemplate, '/')===0) {
            $urlTemplate = substr($urlTemplate, 1);
        }

        $urlReal = self::getUrl();
        if (stripos($urlReal, '/')===0) {
            $urlReal = substr($urlReal, 1);
        }
    
        $urlReal = explode('/', $urlReal);
        $urlTemplate = explode('/', $urlTemplate);
    
        if (sizeof($urlReal)===sizeof($urlTemplate)) {
            for ($i=0; $i < sizeof($urlReal); $i++) {
                if (substr_count($urlTemplate[$i], '{')>0) {
                    $keys = self::getURLVarName($urlTemplate[$i]);
                    if (sizeof($keys)>1) {
                        return false;
                    }
                    $urlkey = $keys[0];
                    $preffix = self::bracketPreffix($urlTemplate[$i]);
                    $suffix = self::bracketSuffix($urlTemplate[$i]);
                    $urlvalue = str_replace($preffix, "", $urlReal[$i]);
                    $urlvalue = str_replace($suffix, "", $urlvalue);

                    if (is_numeric($urlvalue)) {
                        $params[$urlkey] = (int) $urlvalue;
                    } else {
                        $params[$urlkey] = $urlvalue;
                    }
                }
            }
        }
        return $params;
    }
}
