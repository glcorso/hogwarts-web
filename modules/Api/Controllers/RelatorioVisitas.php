<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Api\Controllers;

use Lidere\Core;
use Lidere\Modules\Api\Controllers\Core\Api;
use Lidere\Modules\Comercial\Services\RelatorioVisitas as RelatorioVisitasService;
use Lidere\Models\Usuario;
/**
 * RelatorioVisitas
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class RelatorioVisitas extends Api
{
    public $url = false;

    public function index()
    {
        $get = (array)$this->app->request()->get();

        $service = new RelatorioVisitasService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            []
        );

        $data = $service->list(false);

        //dlog('log', $data);

        if (!empty($data['resultado'])) {
            $this->setData([
                'relatorioVisitas' => $data['resultado']
            ]);
            $this->response();
        } else {
            $this->setError('RELATORIOVISITAS', 'Nenhum relatório de visitas.')
                 ->response(403);
        }
    }

    public function add()
    {
        $post = (array)$this->getRequestBodyData();

        $service = new RelatorioVisitasService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            $post
        );

        $visita = $service->add();
        if (!empty($visita)) {
            $this->setData($visita);
            $this->response();
        } else {
            $this->setError('RELATORIOVISITAS', 'Nenhum relatório adicionado.')
                ->response(403);
        }
    }

    public function upload()
    {
        $post = $this->app->request()->post();

        $service = new RelatorioVisitasService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            $post
        );

        $arquivo = $service->upload(!empty($_FILES['file']) ? $_FILES['file'] : null);
        if (!empty($arquivo)) {
            $this->setData($arquivo);
            $this->response();
        } else {
            $this->setError('RELATORIOVISITAS', 'Nenhum relatório adicionado.')
                ->response(403);
        }
    }
}
