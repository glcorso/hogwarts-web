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
namespace Lidere\Modules\PlanoProducao\Controllers;

use Lidere\Config;
use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Models\Auxiliares;
use Lidere\Models\Aplicacao;
use Lidere\Modules\PlanoProducao\Models\Consulta as modelConsulta;
/**
 * Ajax
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage PlanoProducao\Controllers\Ajax
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Ajax extends Controller
{
    public $url = false;

    public function retornaDemandasOrdem()
    {
        $return = new \stdClass();
        $modelConsulta = new modelConsulta();
        $post = $this->app->request()->post();
        $return->error = true;
        $demandas = false;
        $error = true;

        if (!empty($post['ordem_id'])){
            $demandas = $modelConsulta->getDemandasOrdem($post['ordem_id']);
            if(!empty($demandas)){
                foreach ($demandas as &$dem) {
                    if($dem['ac_qtde_frac'] != '0' ){
                        $dem['qtde'] = Core::BRL($dem['qtde']);
                    }
                }
            }
            $error = !empty($demandas) ? false : true;
        }

        $return->error = $error;
        $return->demandas = $demandas;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }
}