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
 * Efetua o carregamento das classes para teste no PHPUNIT.
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

define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', realpath(__DIR__.DS.'..').DS);
define('APP_LOGS', APP_ROOT.'storage'.DS.'logs'.DS);
define('EXT', '.php');

$environment = 'dev';

$_SERVER = [
    'REDIRECT_SCRIPT_URL' => '/home',
    'REDIRECT_SCRIPT_URI' => 'http://portal-default.dev/home',
    'REDIRECT_APPLICATION_ENV' => 'development',
    'REDIRECT_STATUS' => '200',
    'SCRIPT_URL' => '/home',
    'SCRIPT_URI' => 'http://portal-default.dev/home',
    'APPLICATION_ENV' => 'development',
    'HTTP_HOST' => 'portal-default.dev',
    'HTTP_CONNECTION' => 'keep-alive',
    'HTTP_CACHE_CONTROL' => 'max-age=0',
    'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.105 Safari/537.36 Vivaldi/1.92.917.43',
    'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
    'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
    'HTTP_ACCEPT_LANGUAGE' => 'pt-BR,pt;q=0.8,en-US;q=0.6,en;q=0.4',
    'HTTP_COOKIE' => 'PortalLidere=MSEjc2FnYQ%3D%3D; PHPSESSID=rve9ptmf8v8feanp9cam6p16f6',
    'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
    'SERVER_SIGNATURE' => 'Apache/2.4.18 (Ubuntu) Server at portal-default.dev Port 80',
    'SERVER_SOFTWARE' => 'Apache/2.4.18 (Ubuntu)',
    'SERVER_NAME' => 'portal-default.dev',
    'SERVER_ADDR' => '172.17.0.3',
    'SERVER_PORT' => '80',
    'REMOTE_ADDR' => '172.17.0.1',
    'DOCUMENT_ROOT' => '/var/www/html/portal-default/public/',
    'REQUEST_SCHEME' => 'http',
    'CONTEXT_PREFIX' => '',
    'CONTEXT_DOCUMENT_ROOT' => '/var/www/html/portal-default/public/',
    'SERVER_ADMIN' => 'ramon@lideresistemas.com.br',
    'SCRIPT_FILENAME' => '/var/www/html/portal-default/public/index.php',
    'REMOTE_PORT' => '54688',
    'REDIRECT_URL' => '/home',
    'GATEWAY_INTERFACE' => 'CGI/1.1',
    'SERVER_PROTOCOL' => 'HTTP/1.1',
    'REQUEST_METHOD' => 'GET',
    'QUERY_STRING' => '',
    'REQUEST_URI' => '/home',
    'SCRIPT_NAME' => '/index.php',
    'PHP_SELF' => '/index.php',
    'REQUEST_TIME_FLOAT' => 1508182061.0339999,
    'REQUEST_TIME' => 1508182061
];

$_SESSION['empresa'] = [
  'id' => '1',
  'razao_social' => 'Portal Default',
  'nome_fantasia' => 'Portal Default',
  'dominio' => 'portal-default.dev',
  'diretorio' => 'default',
  'situacao' => 'ativo',
  'cor_principal' => '#40112f',
  'api_token' => '51839493DDD53BA7EE8261617234F',
  'oracle_host' => '192.168.1.35',
  'oracle_porta' => '1521',
  'oracle_sid' => 'LIDERE',
  'oracle_usuario' => 'FOCCO3I',
  'oracle_senha' => 'FOCCO3I',
  'empr_id' => '1',
  'empr_nfe' => '1'
];

error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('log_errors', 'On');
define('ENVIRONMENT', 'dev');
define('BASEURL', 'http://'.$_SERVER['HTTP_HOST'].'/');
/**
* Ativa/Desativa o debug dos errros
* Se ativo não envia email de Exception e exibe o erro na tela
* Se desativado envia email de suporte para a lidere e exibe mensagem amigavel para o cliente
* @var boolean
*/
$debug = true;

/**
* Ativa/Desativa o registro de log do sistema
* Se ativo registro os logs no diretório logs/<date>.log
* @var boolean
*/
$log = true;

/**
 * Arquivo de configuração
 * @var string
 */
$env = '.env.local';

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
     'host'      => env('DB_HOST', 'mysql'),
     'database'  => env('DB_DATABASE', 'portal_default'),
     'username'  => env('DB_USERNAME', 'root'),
     'password'  => env('DB_PASSWORD', 'root'),
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
// PDO::FETCH_OBJ == object
// PDO::FETCH_ASSOC = array
$capsule->setFetchMode(PDO::FETCH_ASSOC);

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
    'log.writer' => $logWriter
));

// Set up the environment so that Slim can route
// $app->environment = Slim\Environment::mock(array(
//     'PATH_INFO'   => $pathInfo
// ));

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
    'Illuminate\Filesystem\FilesystemServiceProvider', // Driver File
    'Illuminate\Translation\TranslationServiceProvider', // Tradução
    'Illuminate\Validation\ValidationServiceProvider', // Validação do formulário
    'Illuminate\Pagination\PaginationServiceProvider', // Paginação
    //'Illuminate\Cache\CacheServiceProvider', // Cache
));


//require APP_ROOT.'src'.DS.'Exceptions'.EXT;

// CLI-compatible not found error handler
$app->notFound(function () use ($app) {
    $url = $app->environment['PATH_INFO'];
    echo "Error: Cannot route to $url";
    $app->stop();
});

// Format errors for CLI
$app->error(function (\Exception $e) use ($app) {
    echo $e;
    $app->stop();
});

$app->get('/cli(/?:id)', function ($id = null) {
    echo "cli ok! {$id}\n";
});

$app->get('/home', function ($id = null) {
});

/**
 * Inicia a aplicação
 */
$app->run();
