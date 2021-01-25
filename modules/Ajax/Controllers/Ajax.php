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
namespace Lidere\Modules\Ajax\Controllers;

use Lidere\Config;
use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Models\Auxiliares;
use Lidere\Models\Aplicacao;
use Lidere\Models\Erp;
use Lidere\Models\Empresa;
use Lidere\Modules\Orcamento\Models\Orcamento;
use Lidere\Modules\Orcamento\Models\Item;
use Lidere\Modules\Orcamento\Models\ItemConfiguracao;

/**
 * Ajax
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Ajax\Controllers\Ajax
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Ajax extends Controller
{
    public $url = false;

    public function teste()
    {
        $return = new \stdClass();

        //{"post":[],"get":{"q":"1005252"},"body":"","teste":"teste"}
        $return->post = $this->app->request()->post();
        $return->get = $this->app->request()->get();
        $return->body = $this->app->request()->getBody();

        $return->teste = 'teste';

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function controlaEmpresaPrincipal()
    {
        $empresasObj = new Empresa;
        $return = new \stdClass();

        $post = $this->app->request()->post();

        $empresa = $empresasObj->buscaEmpresas('row', array('id' => $post['empr_id']));
        $_SESSION['empresa'] =  $empresa;

        $diretorio = $_SESSION['empresa']['diretorio'];
        $_SESSION['diretorio'] = $diretorio;
        $return->diretorio = $diretorio;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function cliente()
    {
        $return = new \stdClass();
        $return->items = null;

        $erpObj = new Erp();

        $string = $this->app->request()->get('q');

        $clientes_erp = $erpObj->buscaClienteErp(null, $string);

        $content = array();
        if (!empty($clientes_erp)) {
            foreach ($clientes_erp as $k => $cliente) {
                $content[$k]['id'] = $cliente['ID'].'#'.$cliente['COD_CLI'].'#'.$cliente['DESCRICAO'];
                $content[$k]['cod'] = $cliente['COD_CLI'];
                $content[$k]['desc'] = $cliente['DESCRICAO'];
            }
        }
        $return->items = $content;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }
}
