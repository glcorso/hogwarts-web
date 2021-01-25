<?php
if (PHP_SAPI != 'cli') {
    $copy = <<<EOF
This file is part of the Lidere Sistemas (http://lideresistemas.com.br)<br/>
<br/>
Copyright(c) 2018  Lidere Sistemas (http://lideresistemas.com.br)<br/>
EOF;
    echo $copy;
    die;
}
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

use Lidere\WebSocket\Server;

/**
 * Lidere Sistemas
 * Efetua o carregamento das classes.
 *
 * @package  CMS
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */
define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', realpath(__DIR__.DS.'..'.DS.'..').DS);
define('APP_LOGS', APP_ROOT.'storage'.DS.'logs'.DS);
define('EXT', '.php');
$time = time();

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
define('ENVIRONMENT', 'dev');

$port = 9000;

$composer_autoload = APP_ROOT.'vendor'.DS.'autoload.php';
if (!file_exists($composer_autoload)) {
    die('Please use the composer to install http://getcomposer.org');
}
echo "============Carregando composer=============\n";
echo $composer_autoload."\n";
require $composer_autoload;
echo "============Carregado composer=============\n";

/**
 * Carregamento dos helpers
 */
$helpers = glob(APP_ROOT.'src'.DS.'Helpers'.DS.'**'.EXT);
foreach ($helpers as $helper) {
    require $helper;
}

$app = new \Slim\Slim();

echo "===========Iniciando o servidor websocket:{$port}===============\n";
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Server()
        )
    ),
    $port
);

$server->run();
