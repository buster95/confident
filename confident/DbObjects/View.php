<?php
namespace Confident\DbObjects;

use Confident\DataBase;
use Confident\Utilities\JsonHelper;

/**
 * SQL VIEW CONSULTA
*/
class View
{
    private $query;
    private $db_conexion;

    function __construct($consulta)
    {
        $this->db_conexion = new DataBase();
        $this->query = 'SELECT * FROM '.$consulta;
        return $this;
    }

    public function getSQL()
    {
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
}
