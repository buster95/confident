<?php

namespace Confident\Utilities;

use Exception;

class JsonHelper
{
    
    private static function acentos($string)
    {
        $acentos = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ');
        foreach ($acentos as $llave => $acento) {
            if (strpos($string, $acento)>-1) {
                $string = utf8_decode($string);
                break;
            }
        }
        return utf8_encode($string);
    }

    private static function ContentType()
    {
        header('Content-Type: application/json');
    }

    public static function getListArray($resultSet)
    {
        $datos = array();
        while ($fila = $resultSet->fetch_array(MYSQLI_ASSOC)) {
            $datos[] = $fila;
        }
        return $datos;
    }

    public static function getFirstArray($resultSet)
    {
        if (is_array($resultSet)) {
            if (isset($resultSet['message'])) {
                return $resultSet;
            }
        }
        return $resultSet->fetch_array(MYSQLI_ASSOC);
    }

    public static function Serialize($datos)
    {
        self::ContentType();
        if ($datos===null) {
            return "[]";
        }
        
        if (is_array($datos)) {
            if (sizeof($datos)>0) {
                if (isset($datos[0])) {
                    // ARREGLO BIDIMENSIONAL
                    $filas = array();
                    foreach ($datos as $row) {
                        // var_dump($row);
                        foreach ($row as $key => $valor) {
                            if (is_string($valor)) {
                                $row[$key] = self::acentos($valor);
                            }
                        }
                        // var_dump($filas);
                        $filas[] = $row;
                        // $filas[] = array_map('utf8_encode', $row);
                    }
                } else {
                    // ARREGLO UNIDIMENSIONAL
                    $filas = $datos;
                    foreach ($filas as $key => $valor) {
                        if (is_string($valor)) {
                            $filas[$key] = self::acentos($valor);
                        }
                    }
                    // var_dump($filas);
                    $filas = $filas;
                    // $filas = array_map('utf8_encode', $filas);
                }
                
                $json = json_encode($filas, JSON_NUMERIC_CHECK);
                if ($json !== false) {
                    return $json;
                }
            }
        }
        return "[]";
    }
    
    public static function Deserialize($json)
    {
        return json_decode($json, true);
    }

    public static function Message($message, $status = 200)
    {
        if (!is_numeric($status)) {
            throw new Exception("Status code is not a number", 1);
        }
        
        $success = false;
        if ((int)$status >= 200 && (int)$status < 300) {
            $success = true;
        }
        
        http_response_code($status);
        $json_message = array(
            'message' => $message,
            'status' => (int) $status,
            'isSuccess' => $success
        );
        return self::Serialize($json_message);
    }
}
