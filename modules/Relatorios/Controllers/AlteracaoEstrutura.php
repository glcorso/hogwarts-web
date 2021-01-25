<?php

namespace Lidere\Modules\Relatorios\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;

/**
 * AlteracaoEstrutura
 *
 * @package Lidere\Modules
 * @subpackage AlteracaoEstrutura\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class AlteracaoEstrutura extends Controller
{
    public $url = 'relatorios/alteracao-estrutura';

    public function pagina($pagina = 1)
    {

        Assets::add('/assets/js/alteracao-estrutura.js', 'Relatorios');
        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }
}