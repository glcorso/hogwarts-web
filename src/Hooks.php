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
 * Hooks - metodos executados antes/depois das rotas
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */

use Lidere\Core;
use Lidere\Config;
use Lidere\ChangeLog;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Modules\Auxiliares\Models\Usuario;
use Lidere\Modules\TI\Models\Hour;

$app->hook('slim.before', function () use ($app) {
    $aplicacaoObj = new Aplicacao();
    $empresaObj = new Empresa();
    $usuarioObj = new Usuario();
    $change = new ChangeLog();
    $hourObj = new Hour();

    $empresa = Empresa::where('dominio', 'like', "%{$_SERVER['HTTP_HOST']}%")->first();
    if ($empresa) {
        $empresa = $empresa->toArray();
    }

    /* se não encontrar a empresa pelo domínio ou está inativo, torna offline */
    if (!$empresa || isset($empresa) && $empresa['situacao'] == 'inativo') {
        $app->render('error.html');
        die;
    }

    if (!isset($_SESSION['empresa'])) {
        $_SESSION['empresa'] = $empresa;
    }

    if (isset($_SESSION['diretorio'])) {
        $empresa['diretorio'] = $_SESSION['diretorio'];
    }

    $app->view->setData('v', Config::read('APP_VERSION').'-'.$change->lastCommit());
    $app->view->setData('tags', $change->tags());
    $app->view->setData('commit', $change->title());
    $app->view->setData('empresa', $empresa);

    if (isset($_SESSION['usuario'])) {
        $_SESSION['empresa_padrao'] = $_SESSION['usuario']['empresa_id'];

        // Verifica se existe um perfil setado e obedece as regras dele
        if(!empty($_SESSION['usuario']['perfil_id'])){
            $modulos = Core::modulosPerfis(null, $_SESSION['usuario']['perfil_id'], null, true, 'S');
        }else{
            $modulos = Core::modulos(null, null, null, true, 'S');
        }

        $modulo_ativo = Core::retornaElementosUrl($app->request()->getResourceUri());

        $app->view->setData('usuario', $_SESSION['usuario']);
        $app->view->setData('modulos', $modulos);

        if(!empty($modulos)){
           // var_dump('aaa');die;
            if($_SESSION['usuario']['id'] != 1 ){
                $existe = false;
                foreach ($modulos as $m) {
                    if(!empty($m['sub'])){
                        $key = Core::multidimensionalSearchArray($m['sub'],array('url' => 'auxiliares/usuarios'));
                    }
                }
                
                $possui_modulo_usuarios = $key ==! false ? true : false;
                $app->view->setData('possui_modulo_usuarios', $possui_modulo_usuarios);
                //var_dump($possui_modulo_usuarios);die;
            }
        }

        $app->view->setData('menu', Core::menu($modulos, $modulo_ativo));
        $app->view->setData('modulo_ativo', $modulo_ativo);
         // chamados
        $app->view->setData('type_hours', $hourObj->getHours('ON'));
        $app->view->setData('users_admin', $usuarioObj->getAdminUsers());
    }

    $app->view->setData('APP_NAME', Config::read('APP_NAME'));
    $app->view->setData('APP_ENV', Config::read('APP_ENV'));
    $app->view->setData('APP_DEBUG', Config::read('APP_DEBUG'));
    $app->view->setData('APP_PERPAGE', Config::read('APP_PERPAGE'));
    $app->view->setData('APP_VERSION', Config::read('APP_VERSION'));
});

$app->get('/assets/css(/:file)', '\Lidere\Assets:routerCss');
$app->get('/assets/js(/:file)', '\Lidere\Assets:routerJs');
