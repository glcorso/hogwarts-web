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
    try {
        $oracleConnector = new OracleConnector();
        $connection = $oracleConnector->connect($config);
        $db = new Oci8Connection($connection, $config["database"], $config["prefix"]);
        $db->setDateFormat('DD-MM-YYYY HH24:MI:SS');
    } catch (\Exception $e) {
        array_map('unlink', glob(APP_ROOT.'storage'.DS.'cache'.DS.'*.cache'));
        dlog('error', $e->getMessage());
        die($e->getMessage());
    }
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

if (!is_writable(APP_LOGS)) {
    die('Sem permiss&atilde;o para criar os logs, de uma olhada no arquivo README!');
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
 * Constantes da aplicação, considera primeiro a do arquivo .env
 * depois o definido no index.php
 */
define('APP_DEBUG', \Lidere\Config::read('APP_DEBUG', $debug));
define('APP_LOG', \Lidere\Config::read('APP_LOG', $log));
define('APP_ENV', \Lidere\Config::read('APP_ENV', $env));

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
    'app.version' => VERSION,
    'debug' => $debug,
    'log.enabled' => $log,
    'log.level' =>      \Slim\Log::DEBUG,
    'log.writer' => $logWriter,
    'view' => $twigView,
    'view.ext' => '.twig',
    'templates.path' => APP_ROOT.'src'.DS.'Resources'.DS.'views',
    'cookies.lifetime' => '300 days',
    'cookies.secret_key' => env('APP_COOKIE'),
    // session
    'session.driver' => 'file',
    'session.lifetime' => 40000,
    'session.expire_on_close' => false,
    'session.encrypt' => false,
    'session.files' => APP_ROOT.'storage'.DS.'session',
    'session.connection' => null,
    'session.table' => 'sessions',
    'session.lottery' => array(2, 100),
    'session.cookie' => 'portal_lidere_session',
    'session.path' => '/',
    'session.domain' => null,
    'session.secure' => false,
    'session.http_only' => true,
));

/**
 * Adiciona o WhoopsMiddleware ao Slim
 */
//$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);
$app->add(new \Lidere\Controllers\ControllerApi);

/**
 * Adiciona a instancia do banco ao Slim
 */
$app->container->singleton('db', function () use ($capsule) {
    return $capsule;
});

/**
 * Faz o carregamento dos ServiceProvider para a instância do slim
 * @var ServiceManager
 */
$services = new ServiceManager($app);
$services->registerServices(array(
    // https://laravel.com/docs/5.2
    'Lidere\Modules\Services\ServicesServiceProvider', // Service
    'Illuminate\Filesystem\FilesystemServiceProvider', // Driver File
    'Illuminate\Translation\TranslationServiceProvider', // Tradução
    'Illuminate\Validation\ValidationServiceProvider', // Validação do formulário
    'Illuminate\Pagination\PaginationServiceProvider', // Paginação
    //'Illuminate\Cache\CacheServiceProvider', // Cache
));

/**
 * Carregamento dos hooks
 */
require APP_ROOT.'src'.DS.'Hooks'.EXT;

/**
 * Carregamento das exeções da aplicação
 */
require APP_ROOT.'src'.DS.'Slim'.DS.'Exception'.DS.'Error'.EXT;

/**
 * Carregamento da mensagem de exibição em caso de rota não encontrada.
 */
require APP_ROOT.'src'.DS.'Slim'.DS.'NotFound'.EXT;

/**
 * Carregamento dos modules do Core
 */
$routers = glob(APP_ROOT.'src'.DS.'Modules'.DS.'**'.DS.'router'.EXT);
foreach ($routers as $router) {
    require $router;
}

/**
 * Carregamento dos modules
 */
$routers = glob(APP_ROOT.'modules'.DS.'**'.DS.'router'.EXT);
foreach ($routers as $router) {
    require $router;
}

/**
 * Inicia a aplicação
 */
$app->run();
