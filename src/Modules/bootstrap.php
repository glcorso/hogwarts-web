<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

/**
 * Lidere Sistemas
 * Efetua o carregamento das classes.
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connectors\ConnectionFactory;
use Yajra\Oci8\Connectors\OracleConnector;
use Yajra\Oci8\Oci8Connection;
use Lidere\Core;

$capsule = new Capsule;

$capsule->addConnection(array(
     'driver'    => 'mysql',
     'host'      => env('DB_HOST', 'localhost'),
     'database'  => env('DB_DATABASE', 'portal'),
     'username'  => env('DB_USERNAME', 'root'),
     'password'  => env('DB_PASSWORD', ''),
     'charset'   => 'utf8',
     'collation' => 'utf8_unicode_ci',
     'prefix'    => ''
));

$capsule->getDatabaseManager()->extend('oracle', function ($config) {
    $oracleConnector = new OracleConnector();
    $connection = $oracleConnector->connect($config);
    $db = new Oci8Connection($connection, $config["database"], $config["prefix"]);
    $db->setDateFormat('DD-MM-YYYY HH24:MI:SS');
    return $db;
});

// Set the event dispatcher used by Eloquent models... (optional)
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

// Configura o retorno da consulta
// PDO::FETCH_OBJ == object
// PDO::FETCH_ASSOC = array
$capsule->setFetchMode(PDO::FETCH_ASSOC);

/**
 * Adiciona as conexões do oracle (oracle.id) da tabela tempresas.
 */
Core::addConnection($capsule);

$app = \Slim\Slim::getInstance();

/**
 * Adiciona a instancia do banco ao Slim
 */
$app->container->singleton('db', function () use ($capsule) {
    return $capsule;
});
