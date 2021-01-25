<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Logs\Controllers;

use Lidere\Config;
use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Modules\Logs\Models\Log;

/**
 * Logs
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Logs\Controllers\Logs
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Logs extends Controller
{
    public $url = 'logs';

    public function index()
    {
        $this->app->redirect(
            '/'.$this->modulo['url'].'/pagina/1?'.$_SERVER['QUERY_STRING']
        );
    }

    public function pagina($pagina = 1)
    {
        $get = $this->app->request()->get();

        $logObj = new Log();

        $tipo = false;

        /* Total sem paginação */
        $total = $logObj->whereEmpresaId($_SESSION['empresa']['id'])
                        ->count();

        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        /**
        * records = qtd de registros
        * offset = inicia no registro n
        */
        $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $offset = Config::read('APP_PERPAGE');

        $logs = $logObj->whereEmpresaId($_SESSION['empresa']['id'])
                       ->skip($records)
                       ->take($offset)
                       ->orderBy('data', 'desc')
                       ->get();

        $total_tela = count($logs);

        $data['modulo'] = $this->modulo;
        $data['resultado'] = !empty($logs) ? $logs->toArray() : array();
        $data['permissao'] = $this->permissao;
        $data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/'.$this->modulo['url'].'/pagina',
            $_SERVER['QUERY_STRING']
        );

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $data
            )
        );
    }
}
