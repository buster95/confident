<?php
namespace Confident;

use mysqli;
use Exception;
use Confident\Utilities\JsonHelper as JsonConvert;
use Confident\DbObjects\Table;
use Confident\DbObjects\View;
use Confident\DbObjects\Procedure;
use Confident\DbObjects\Query;

define('zona_horaria', '+6');
date_default_timezone_set('Etc/GMT'.zona_horaria);
// date_default_timezone_get();

/**
 * ORM para Mysql
 */
class DataBase
{
    // VARIABLES DE CONFIGURACION
    private static $user = 'root'; // USUARIO de la BASE DE DATOS
    private static $password = '123456'; // PASSWORD del usuario de la BASE DE DATOS
    private static $database = 'mysql'; // BASE DE DATOS
    private static $host = '127.0.0.1'; // IPv4 o HOST DE CONEXION
    private static $port = ''; // PUERTO DE CONEXION DEFAULT:3306

    public static function table($nombre_tabla)
    {
        $tabla = new Table($nombre_tabla);
        return $tabla;
    }

    public static function setUser($username)
    {
        self::$user = $username;
    }
    
    public static function setPassword($pwd)
    {
        self::$password = $pwd;
    }
    
    public static function setDatabase($db)
    {
        self::$database = $db;
    }
    
    public static function setHost($host)
    {
        self::$host = $host;
    }
    
    public static function setPort($port)
    {
        self::$port = $port;
    }
    
    /**
     * @return mysqli_connect Conexion MySQL
     */
    private function conectar()
    {
        $con = new mysqli($this->getHOST(), self::$user, self::$password, self::$database);
        if ($con->connect_error) {
            trigger_error('Database connection failed: ' . $con->connect_error, E_USER_ERROR);
        } else {
            return $con;
        }
    }

    private function getHOST()
    {
        if (self::$port == '') {
            return self::$host;
        } else {
            return self::$host . ':' . self::$port;
        }
    }

    public static function query($consulta)
    {
        if ($consulta != null & $consulta != '' & is_string($consulta)) {
            $query = new Query($consulta);
            return $query;
        }
        throw new Exception("Consulta No Aceptada", 1);
    }

    public static function procedure($proc_name, $params = null)
    {
        if ($proc_name !== null && $proc_name !== '' && is_string($proc_name)) {
            $proc = new Procedure($proc_name, $params);
            return $proc;
        }
        throw new Exception("Procedimiento No Aceptado", 1);
    }

    public static function view($viewName)
    {
        if ($viewName != null & $viewName != '') {
            $vista = new View($viewName);
            return $vista;
        }
        throw new Exception("Consulta No aceptada", 1);
    }

    public function restore()
    {
    }

    public function backup()
    {
        $conexion = new DataBase();
        $backup = "------------------------------------------------------------------------\n";
        $backup .= "--                         CONFIDENT BACKUP                          \n";
        $backup .= "-- DATABASE: " . strtoupper($conexion->database) . "\n";
        $backup .= "-- HOST: " . $conexion->host . "         PORT: " . $conexion->port . "\n";
        $backup .= "-- MYSQL SERVER " . $this->query('SELECT VERSION() as version;')->getFirst()->version . "\n";
        $backup .= "-----------------------------------------------------------------------\n\n";

        $backup .= "CREATE DATABASE IF NOT EXISTS `" . $conexion->database . "` ";
        $backup .= "/*!40100 DEFAULT CHARACTER SET " . $this->query('select @@character_set_database as charset;')->getFirst()->charset . "*/;\n";
        $backup .= "USE `" . $conexion->database . "`;\n\n";

        $backup .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $backup .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $backup .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $backup .= "/*!40101 SET NAMES utf8 */;\n";
        $backup .= "/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;\n";
        $backup .= "/*!40103 SET TIME_ZONE='+00:00' */;\n";
        $backup .= "/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;\n";
        $backup .= "/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;\n";
        $backup .= "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;\n";
        $backup .= "/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;\n\n";

        $tablas = $conexion->consultar('SHOW TABLES');
        while ($tabla = $tablas->fetch_array(MYSQLI_NUM)) {
            $create = 'DROP TABLE IF EXISTS `' . $tabla[0] . "`;\n";
            $create .= "/*!40101 SET @saved_cs_client = @@character_set_client */;\n";
            $create .= "/*!40101 SET character_set_client = utf8 */;\n";

            $object = $conexion->consultar('SHOW CREATE TABLE ' . $tabla[0])->fetch_array(MYSQLI_NUM);
            $create .= $object[1] . ";\n";
            $create .= "/*!40101 SET character_set_client = @saved_cs_client */;\n\n";
            $backup .= $create;

            $backup .= "/*!40000 ALTER TABLE `" . $tabla[0] . "` DISABLE KEYS */;\n";
            $query = 'REPLACE INTO `' . $tabla[0] . '`';
            // HACIENDO DUMP DE LOS CAMPOS
            $atributos = '';
            $columnas = $conexion->consultar('DESCRIBE ' . $tabla[0]);
            while ($columna = $columnas->fetch_array(MYSQLI_ASSOC)) {
                $atributos .= $columna['Field'] . ',';
            }
            $query .= '(' . $atributos . ") VALUES \n";

            // HACIENDO DUMP DE LOS DATOS
            $datos = '';
            $insertados = $conexion->consultar('SELECT * FROM ' . $tabla[0]);
            while ($fila = $insertados->fetch_array(MYSQLI_ASSOC)) {
                $datos .= "(";
                foreach ($fila as $key => $value) {
                    $type = $this->table($tabla[0])->COLUMN_TYPE($key);
                    if ($type === 'STRING' || $type === 'DATE') {
                        $datos .= '\'' . $value . '\',';
                    } else {
                        $datos .= $value . ',';
                    }
                }
                $datos .= "),\n";
            }
            $query .= $datos . '~';

            // SI NO HAY DATOS, NO SE AÑADE EL INSERT
            if ($this->count_rows($insertados) > 0) {
                $query = str_replace(',)', ')', $query);
                $query = str_replace('(,', '(', $query);
                $query = str_replace("),\n~", ");", $query);
                $backup .= $query . "\n";
            }
            $backup .= "/*!40000 ALTER TABLE `" . $tabla[0] . "` ENABLE KEYS */;\n\n";
        }

        $backup .= "/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;\n";
        $backup .= "/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;\n";
        $backup .= "/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;\n";
        $backup .= "/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;\n";
        $backup .= "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $backup .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $backup .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        $backup .= "/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;\n\n";

        $backup .= "------------------------------------------------------------------------\n";
        $backup .= "--                    CONFIDENT BACKUP COMPLETADO                       \n";
        $backup .= "------------------------------------------------------------------------\n";

        file_put_contents(strtoupper($conexion->database) . '_BACKUP_' . date('d-M-Y') . '.sql', $backup);
        return $create . $backup;
    }

    public function consultar($consulta)
    {
        $conx = $this->conectar();
        $resultado = $conx->query($consulta);
        if ($conx->errno !==0) {
            http_response_code(400);
            $resultado = array('message' => "Database Error Number:" . $conx->errno . " :: " . $conx->error);
        }
        $conx->close();
        return $resultado;
    }

    public function ejecutar($consulta)
    {
        $conx = $this->conectar();
        $conx->query($consulta);
        $affected_rows = $conx->affected_rows;
        $conx->close();
        return $affected_rows;
    }

    
    /*
      CONVIERTE LAS FILAS DE MYSQLI EN UN ARREGLO DE OBJETOS
     */
    public static function listar($resultados)
    {
        $datos = array();
        while ($fila = $resultados->fetch_object()) {
            $datos[] = $fila;
        }
        return $datos;
    }

    /*
      CUENTA LOS DATOS DE UN ARRAY
     */
    public static function count_data($objetos)
    {
        if (is_array($objetos)) {
            $x = 0;
            foreach ($objetos as $obj) {
                $x++;
            }
            return $x;
        } else {
            throw new Exception("Error en count_data $objetos no es array", 5);
        }
    }

    /*
      CUENTA EL NUMERO DE FILAS DE UN  MYSQLI_RESULT
     */
    public static function count_rows($resultado)
    {
        return mysqli_num_rows($resultado);
    }

    /*
      VERIFICA SI UN STRING TIENE UN TAMAÑO ESPECIFICO
     */
    public static function size_string_verify($valor, $size)
    {
        if (is_string($valor) && is_numeric($size)) {
            if (strlen($valor) >= $size) {
                return true;
            } else {
                return false;
            }
        } else {
            if (!is_string($valor) && is_numeric($size)) {
                throw new Exception("Value no es un string", 4);
            } elseif (is_string($valor) && !is_numeric($size)) {
                throw new Exception("Size no es un numero", 4);
            } else {
                throw new Exception("Parametros Invalidos", 4);
            }
        }
    }

    /**
     * LIMPIA UNA CADENA SOLO QUITANDO APOSTROFE
     * @param String $cadena CADENA A LIMPIAR
     * @return String        CADENA LIMPIADA
     */
    public static function SQL_CLEAN_TEXT($cadena = '')
    {
        $caracteres = array('\'');
        $filtrada = str_replace($caracteres, '', $cadena);
        return $filtrada;
    }

    /**
     * QUITA CARACTERES ESPECIALES DE UNA CADENA
     * @param  String $cadena CADENA A LIMPIAR
     * @return String         CADENA LIMPIADA
     */
    public static function SQL_CLEAN($cadena)
    {
        $caracteres = array('\'', '"', '=', '!',
            '<', '>', '¿', '?', '¡', '$', '\\', '{',
            '}', '[', ']', '#', '&',
            '+');
        $filtrada = str_replace($caracteres, '', $cadena);
        $filtrada = str_replace(array('%', '*'), '%', $filtrada);
        return $filtrada;
    }

    public static function SQL_CLEAN_SPECIAL($cadena)
    {
        $caracteres = array('\'', '"', '=', '!',
            '<', '>', '¿', '?', '¡', '$', '\\', '{',
            '}', '[', ']', '#', '&', '(', ')',
            '+', '-', '%', '*');
        $filtrada = str_replace($caracteres, '', $cadena);
        return $filtrada;
    }

    /*
      CAPITALIZE PALABRAS
     */
    public static function capitalize($value, $allwords = false)
    {
        if ($allwords==true) {
            $textos_procesados = '';
            $textos = explode(' ', $value);
            foreach ($textos as $palabras) {
                $letter = substr($palabras, 0, 1);
                $word = substr($palabras, 1);
                $newWord = strtoupper($letter) . strtolower($word);
                $textos_procesados .= ' '.$newWord;
            }
            return substr($textos_procesados, 1);
        } else {
            $letter = substr($value, 0, 1);
            $word = substr($value, 1);
            $newWord = strtoupper($letter) . strtolower($word);
            return $newWord;
        }
        return strtoupper($letter) . strtolower($word);
    }

    /*
     * QUITA TODOS LOS ESPACIOS DE UNA CADENA
     */
    public static function trim($cadena)
    {
        return str_replace(array(' ', '\''), '', $cadena);
    }

    /*
      CONVERTIR A MAYUSCULAS UNA PALABRA O UN TEXTO
     */
    public static function mayuscula($cadena)
    {
        return strtoupper($cadena);
    }

    /*
      CONVERTIR A MINUSCULAS UNA PALABRA O UN TEXTO
     */
    public static function minuscula($cadena)
    {
        return strtolower($cadena);
    }
}
