<?php

namespace Lidere\Modules\AssistenciaExterna\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Assets;

/**
 * PagamentoOrdem
 *
 * @package Lidere\Modules
 * @subpackage PagamentoOrdem\Controllers
 * @author Humberto Viezzer de Carvalho
 * @copyright 2019 Lidere Sistemas
 */
class PagamentoOrdem extends Controller
{
    public $url = 'assistencia-externa/pagamento-ordem';

    public function pagina($pagina = 1)
    {
        Assets::add('/assets/js/pagamentoOrdem.js', 'AssistenciaExterna');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }

    public function geraPagamento()
    {
        $return = $this->app->service->geraPagamento();

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));


    }
}
