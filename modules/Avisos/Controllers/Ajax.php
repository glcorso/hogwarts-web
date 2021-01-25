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
namespace Lidere\Modules\Relatorios\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Modules\Relatorios\Models\EstruturaProduto as modelRelatorioEstruturaProduto;

/**
 * Ajax
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Comercial\Controllers\Ajax
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */

class Ajax extends Controller
{


    public function retornaItensPn()
    {
        $return = new \stdClass();
        $modelRelatorioEstruturaProduto = new modelRelatorioEstruturaProduto();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelRelatorioEstruturaProduto->retornaItensPn($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

}