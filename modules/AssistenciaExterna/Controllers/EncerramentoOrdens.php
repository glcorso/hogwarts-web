<?php

namespace Lidere\Modules\AssistenciaExterna\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\AssistenciaExterna\Services\EncerramentoOrdens as EncerramentoOrdensService;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoArquivos as modelOrdemServicoArquivos;

/**
 * EncerramentoOrdens
 *
 * @package Lidere\Modules
 * @subpackage EncerramentoOrdens\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class EncerramentoOrdens extends Controller
{
    public $url = 'assistencia-externa/encerramento-ordens';

    public function pagina($pagina = 1)
    {

        Assets::add('/assets/js/encerramentoOrdens.js', 'AssistenciaExterna');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }

    public function add(){
        $post = $this->app->request->post();
        $encerramento = $this->app->service->add($post['ids']);
        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($encerramento));
    }

}
