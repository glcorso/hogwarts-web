<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

date_default_timezone_set('America/Sao_Paulo');

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
use SlimServices\ServiceManager;
use Lidere\Core;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| This application is installed by the Composer,
| that provides a class loader automatically.
| Use it to seamlessly and feel free to relax.
|
*/
$composer_autoload = APP_ROOT.'vendor'.DS.'autoload.php';
if (!file_exists($composer_autoload)) {
    die('Please use the composer to install http://getcomposer.org');
}
require $composer_autoload;

/**
 * Carregamento dos modules
 */
$helpers = glob(APP_ROOT.'src'.DS.'Helpers'.DS.'**'.EXT);
foreach ($helpers as $helper) {
    require $helper;
}

/**
 * Configuração do banco de dados
 */
if (!file_exists(APP_ROOT.$env)) {
    file_put_contents(APP_ROOT.$env, file_get_contents(APP_ROOT.'.env.example'));
    throw new Exception('Você deve configurar o arquivo '.APP_ROOT.$env.' para conexão com o banco');
}

/**
 * Log de Eventos
 * @var string
 */
$fileLog = APP_LOGS.'log-'.date('Y-m-d').'.log';
if (!is_dir(APP_LOGS)) {
    mkdir(APP_LOGS, 0755);
}
if (!file_exists($fileLog)) {
    file_put_contents($fileLog, null);
    chmod($fileLog, 0777);
}

/**
 * Carregamento do arquivo de configuração do sistema
 */
$dotenv = new Dotenv\Dotenv(APP_ROOT, $env);
$dotenv->load();

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
    // $db->setDateFormat('DD-MM-YYYY HH24:MI:SS');
    $db->setDateFormat('DD-MM-YYYY');
    return $db;
});

/**
 * Adiciona as conexões do oracle (oracle.id) da tabela tempresas.
 */
Core::addConnection($capsule);

// Set the event dispatcher used by Eloquent models... (optional)
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

// Configura o retorno da consulta
// PDO::FETCH_OBJ = object
// PDO::FETCH_ASSOC = array
$capsule->setFetchMode(PDO::FETCH_ASSOC);

if (!is_writable(APP_LOGS)) {
    die("Sem permiss&atilde;o para criar os logs, de uma olhada no arquivo README!\n");
}

/**
 * Ativa o registro de logs do sistema
 * não funciona junto com o whoops
 * @var \Slim\LogWriter
 */
$logWriter = new \Slim\LogWriter(fopen($fileLog, 'a+'));

/**
 * Controla a versão da aplicação caso o controle por hash do commit não seja utilizado
 */
define('VERSION', \Lidere\Config::read('APP_VERSION', '1.0.0'));


/**
 * Twig View para o Slim
 * @var object
 */
$twigView = new \Slim\Views\Twig();

/**
 * Ativa o dump no twig
 * @var array
 */
$twigView->parserOptions = array(
    'debug' => true,
    'cache' => APP_ROOT.'storage'.DS.'cache'
);
$twigView->parserExtensions = array(
    new \Twig_Extension_Debug(),
    new \Lidere\Twig\Extension\Url(),
    new \Lidere\Twig\Extension\View(),
);


/**
 * Configurações do Slim
 * @var object
 */
$app = new \Slim\Slim(array(
     // paths
    'path' => APP_ROOT,
    'path.lang' => APP_ROOT.'src'.DS.'Resources'.DS.'lang',
    'path.modules' => APP_ROOT.'modules',
    'path.storage' => APP_ROOT.'storage',
    // app
    'app.locale' => 'pt',
    'view' => $twigView,
    'view.ext' => '.twig',
    'templates.path' => APP_ROOT.'src'.DS.'Resources'.DS.'views',
    'app.version' => VERSION,
    'debug' => $debug,
    'log.enabled' => $log,
    'log.level' =>      \Slim\Log::DEBUG,
    'log.writer' => $logWriter
));

// Set up the environment so that Slim can route
$app->environment = Slim\Environment::mock(array(
    'PATH_INFO'   => $pathInfo
));

/**
 * Adiciona a instancia do banco ao Slim
 */
$app->container->singleton('db', function () use ($capsule) {
    return $capsule;
});

//require APP_ROOT.'src'.DS.'Exceptions'.EXT;

// CLI-compatible not found error handler
$app->notFound(function () use ($app) {
    $url = $app->environment['PATH_INFO'];
    echo "Error: Rota para o worker não encontrada $url\n";
    echo "Verifique o arquivo ".APP_ROOT.DS."modules".DS."Worker".DS."cli.php";
    $app->stop();
});

// Format errors for CLI
$app->error(function (\Exception $e) use ($app) {
    echo $e;
    $app->stop();
});

$app->get('/', function ($id = null) {
    $helper = <<<EOF
# Cron
Para execução dos cron's é necessário adicionar somente uma linha no crontab -e

* * * * * /usr/bin/php /var/www/portal-default/.etc/cron/cron.php schedule env="prod"

Exemplo de retorno:

Controller/Tasks::index() - Buscando jobs
task_id:2 job:worker/envia-emails
*/2 * * * *
task_id:4 job:worker/env
* * * * *
Execução do comando agendado: (...) > /dev/null 2>&1 &
Task is complete... task_id:2 job:worker/envia-emails
Execução do comando agendado: (...) > /dev/null 2>&1 &
Task is complete... task_id:4 job:worker/env
Controller/Tasks::index() - Finalizado busca por jobs

EOF;
    echo $helper;
});

$app->get('/cli(/?:id)', function ($id = null) {
    echo "cli ok! {$id}\n";
});

/**
 * Carregamento dos modules do Core
 */
$routers = glob(APP_ROOT.'src'.DS.'Modules'.DS.'**'.DS.'cli'.EXT);
foreach ($routers as $router) {
    require $router;
}

/**
 * Carregamento dos modules
 */
$routers = glob(APP_ROOT.'modules'.DS.'**'.DS.'cli'.EXT);
foreach ($routers as $router) {
    require $router;
}

/**
 * Inicia a aplicação
 */
$app->run();
