<?php
namespace Confident\DbObjects;

use Confident\DataBase;
use Confident\Utilities\JsonHelper;
/**
 * SQL PROCEDURE CONSULTA
 */
class Procedure{
    private $query;
    private $db_conexion;

    function __construct($proc, $prms) {
        $this->db_conexion = new DataBase();
        if($prms==null || $prms==''){
            $this->query = 'CALL '.$proc.'()';
        }else{
            $this->query = 'CALL '.$proc.'('.$this->CreateStatement($prms).')';
        }
        return $this;
    }

    private function CreateStatement($prms){
        $query_params = "";
        if(is_array($prms)){
            foreach ($prms as $p) {
                if (is_numeric($p) || is_bool($p)){
                    $query_params .= ','.$p;
                }else{
                    $query_params .= ",'".$p."'";
                }
            }
        }
        return substr($query_params, 1);
    }

    public function getSQL(){
        return $this->query;
    }

    public function get()
    {
        $resultados = $this->db_conexion->consultar($this->query);
        return JsonHelper::getListArray($resultados);
    }

    public function getFirst()
    {
        $resultado = $this->db_conexion->consultar($this->query);
        return JsonHelper::getFirstArray($resultado);
    }

    public function getJSON()
    {
        return JsonHelper::Serialize($this->get());
    }

    public function getFirstJSON()
    {
        return JsonHelper::Serialize($this->getFirst());
    }

    public function isExists($sensitive = false) {
        $consulta = $this->query;
        if ($sensitive) { $consulta = str_replace('LIKE', 'LIKE BINARY', $consulta); }
        $consulta.=';';
        $consulta = str_replace(' ;', ';', $consulta);

        $resultados = $this->db_conexion->consultar($consulta);
        $fila = $resultados->fetch_object();

        if ($fila !== null) {
            return true;
        } else {
            return false;
        }
    }
}