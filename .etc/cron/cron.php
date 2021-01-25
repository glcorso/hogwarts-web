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

/**
 * Script para iniciar um Worker
 *
 * @package Core
 * @subpackage Jobs
 * @category Worker
 * @author Ramon Barros
 */
define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', realpath(__DIR__.DS.'..'.DS.'..').DS);
define('APP_LOGS', APP_ROOT.'storage'.DS.'logs'.DS);
define('EXT', '.php');

// convert all the command line arguments into a URL
$settings = array();
$params = array();
$argv = $GLOBALS['argv'];
array_shift($GLOBALS['argv']);
foreach ($argv as $arg) {
    if (strpos($arg, '=') !== false) {
        $param = explode('=', $arg);
        if (strpos($arg, 'setting')  !== false) {
            if (strpos($param[1], ',') !== false) {
                $settings[$param[0]] = preg_split('/,/', $param[1]);
            } else {
                $settings[$param[0]] = $param[1];
            }
        } else {
            if (strpos($param[1], ',') !== false) {
                $params[$param[0]] = preg_split('/,/', $param[1]);
            } else {
                $params[$param[0]] = $param[1];
            }
        }
    } else {
        $params[] = $arg;
    }
}

$environment = !empty($params['env']) ? $params['env'] : 'prod';
// list($_SESSION['usuario']['id'], $_SESSION['usuario']['nome']) = explode('|', $params['usuario']);
// list($_SESSION['empresa']['id'], $_SESSION['empresa']['empr_id']) = explode('|', $params['empresa']);
unset($params['env']);

$pathInfo = '/' . implode('/', $params);
$pathInfo = str_replace('/?', '?', $pathInfo);
$pathInfo = str_replace('/&', '&', $pathInfo);

if ($environment == 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');
    define('ENVIRONMENT', 'dev');
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
} elseif ($environment == 'hom') {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');
    define('ENVIRONMENT', 'hom');
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
    $env = '.env.hom';
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');
    define('ENVIRONMENT', 'pro');
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
    $env = '.env';
}
define('APP_DEBUG', $debug);
define('APP_LOG', $log);
define('APP_ENV', $env);

/**
 * Carregamento das classe e arquivos de configurações
 */
require APP_ROOT.'src'.DS.'Jobs'.DS.'Cron'.DS.'cli'.EXT;
