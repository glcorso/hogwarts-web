<?php

namespace Lidere\Modules\Php\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\Php\Services\Info as InfoService;

/**
 * Info
 *
 * @package Lidere\Modules
 * @subpackage Php\Controllers
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Info extends Controller
{
    public $url = 'php/info';

    public function index()
    {
        $get = $this->app->request()->get();

        $service = new InfoService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            $get
        );

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $service->list()
            )
        );
    }
}
