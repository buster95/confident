<?php

require_once '../vendor/autoload.php';

use Confident\DataBase;
use Confident\Utilities\JsonHelper;

DataBase::setUser('root');
DataBase::setPassword('123456');
DataBase::setDataBase('confident');
DataBase::setHost('127.0.0.1');

$ar = array(
    'nombre' => 'Walter Ramon Corrales Diaz',
    'detalle' => array(
        array(
            'idProducto' => 1,
            'nombre' => 'pasta de diente'
        ),
        array(
            'idProducto' => 2,
            'nombre' => 'cepillo de diente'
        )
    )
);

$user = DataBase::table('usuarios')->getFirst();
$user['detalle'] = array(array(
                            'idProducto' => 1,
                            'nombre' => 'pasta de diente'
                        ),
                        array(
                            'idProducto' => 2,
                            'nombre' => 'cepillo de diente'
                        ));
echo JsonHelper::serialize($user);
