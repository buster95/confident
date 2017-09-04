<?php
namespace Confident\DbObjects;

use Confident\DataBase;
use Confident\Utilities\JsonHelper;
/**
 * 	SQL QUERY CONSULTA
 */
 class Query {
    private $query;
    private $db_conexion;

    function __construct($consulta) {
        $this->db_conexion = new DataBase();
        $this->query = $consulta;
        return $this;
    }

    public function execute(){
        $resultado = $this->db_conexion->ejecutar($this->query);
        if($resultado>0){
            return true;
        }else{
            return false;
        }
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