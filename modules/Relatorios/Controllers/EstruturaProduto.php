<?php

namespace Lidere\Modules\Relatorios\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\Relatorios\Services\EstruturaProduto as EstruturaProdutoService;

/**
 * EstruturaProduto
 *
 * @package Lidere\Modules
 * @subpackage EstruturaProduto\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class EstruturaProduto extends Controller
{
    public $url = 'relatorios/estrutura-produto';

    public function index()
    {
       
        Assets::add('/assets/js/estrutura-produto.js', 'Relatorios');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->index()
            )
        );
    }

    public function imprimir()
    {
       
        Assets::add('/assets/js/estrutura-produto.js', 'Relatorios');

        $this->app->render(
            'imprimir.html.twig',
            array(
                'data' => $this->app->service->index()
            )
        );
    }
}