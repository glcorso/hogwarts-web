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
 * @package  CMS
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */
date_default_timezone_set('America/Sao_Paulo');
session_cache_limiter(false);
session_set_cookie_params(864000);
ini_set('session.gc_maxlifetime', 50000);

session_start();

define('DS', DIRECTORY_SEPARATOR);
define('APP_ROOT', realpath(__DIR__.DS.'..').DS);
define('APP_LOGS', APP_ROOT.'storage'.DS.'logs'.DS);
define('EXT', '.php');

if (file_exists(APP_ROOT.'storage'.DS.'queue'.DS.'key.queue')) {
    # ======= Executar processos em background ========
    # recupera o id salvo para poder enviar as mesagens em background
    $uniqid = file_get_contents(APP_ROOT.'storage'.DS.'queue'.DS.'key.queue');
    if (!empty($uniqid)) {
        # Some unique ID
        define('QUEUE_KEY', $uniqid);

        # Different type of actions
        define('QUEUE_TYPE_START', 1);
        define('QUEUE_TYPE_END', 2);
    }
    # ======= Executar processos em background ========
}

$time = time();

header('Cache-control: private'); // IE 6 FIX
header('Last-Modified: '.gmdate('D, d M Y H:i:s', $time).'GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *, Cache-Control, Pragma, Origin, Authorization, X-Requested-With, GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: *, Content-Type, GET, OPTIONS, X-Token, X-Empresa, X-Usuario, X-XSRF-TOKEN, X-Requested-With, X-File-Name, X-File-Size, X-File-Last-Modified");

try {

    /**
     * Recupera host acessado
     * @var string
     */
    $host = strstr($_SERVER['HTTP_HOST'], '192.168.0.') !== false ? 'localhost' : $_SERVER['HTTP_HOST'];
    /**
     * Recupera somente dominio sem extensão
     */
    $domain = preg_replace('/(?:\.\w{2,5})?(?:\.\w{2,5})$/', '', $host);

    /**
     * Ambiente do sistema
     */
    switch ($host) {
        case 'localhost':
        case '192.168.1.75':
        case '192.168.15.9':
        case "{$domain}.lid":
        case "{$domain}.lidere":
            $environment = 'dev';
            break;
        case "{$domain}.hom":
            $environment = 'hom';
            break;
        default:
            $environment = 'pro';
            break;
    }

    if ($environment == 'dev') {
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
    } elseif ($environment == 'hom') {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        ini_set('log_errors', 'On');
        define('ENVIRONMENT', 'hom');
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
        $env = '.env.hom';
    } else {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        ini_set('log_errors', 'On');
        define('ENVIRONMENT', 'pro');
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
        $env = '.env';
    }

    /**
     * Recupera o token de conexão da API
     * @var string
     */
    $token = !empty($_SERVER['HTTP_X_TOKEN']) ? $_SERVER['HTTP_X_TOKEN'] : null;
    if (@preg_match("/^[A-z0-9]{32}$/i", $token) && !(strlen($token) & 1)) {
        $settings = glob(APP_ROOT.'.env'.DS.'*');
        foreach ($settings as $setting) {
            $content = file_get_contents($setting);
            if (strpos($content, 'APP_ID='.$token) !== false ||
                strpos($content, 'WS_ID='.$token) !== false) {
                $pathinfo = pathinfo($setting);
                if (!empty($pathinfo)) {
                    $filename = $pathinfo['filename'];
                    $env = '.env'.DS.$filename;
                    break;
                }
            }
        }
    }

    /**
     * Carregamento das classe e arquivos de configurações
     */
    require APP_ROOT.'src'.DS.'bootstrap.php';
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'SQLSTATE[28000]') !== false) {
        echo 'Você deve configurar o arquivo '.APP_ROOT.$env.' para conexão com o banco<br/>';
    } elseif (strpos($e->getMessage(), 'SQLSTATE[42S22]') !== false) {
        echo 'Coluna não encontrada no banco de dados<br/>';
    }
    echo 'Error:'.$e->getMessage().' file:'.$e->getFile().' line:'.$e->getLine().'<br/>';
} catch (InvalidArgumentException $e) {
    if (strpos($e->getMessage(), 'LogWriter') !== false) {
        echo 'Você deve dar permissão de escrita para o diretório '.$fileLog.'<br/>';
    }
    echo 'Error:'.$e->getMessage().' file:'.$e->getFile().' line:'.$e->getLine().'<br/>';
} catch (Exception $e) {
    echo 'Error:'.$e->getMessage().' file:'.$e->getFile().' line:'.$e->getLine().'<br/>';
}
