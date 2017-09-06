<?php

require_once '../vendor/autoload.php';

use Confident\DataBase;

DataBase::setUser('root');
DataBase::setPassword('123456');
DataBase::setDataBase('confident');
DataBase::setHost('127.0.0.1');

var_dump(DataBase::table('usuarios')->limit(10, 5)->get());
