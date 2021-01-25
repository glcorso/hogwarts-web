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
use Lidere\ChangeLog;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Modules\Auxiliares\Models\Usuarios;

/**
 * @apiDefine Erro404
 * @apiError 404 Not Found
 */
$app->notFound(function () use ($app) {

    /**
     * Retorno para o cliente
     * @var array
     */
    $return = array(
        'method' => $app->request->getMethod(),
        'router' => '404 - Not Found',
        'message' => 'Esta rota não existe'
    );
    /**
     * Recupera o cabeçalho da requisição
     * @var object
     */
    $response = $app->response();
    /**
     * Altera o Content-Type para application/json
     */
    
    $app->render(
            'error.html'
        );
    //$response['Content-Type'] = 'application/json';

    /**
     * Altera o body para retornar um json
     */
   // echo $response->body(json_encode($return));
});
