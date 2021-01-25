<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2019
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Assistencia\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Assets;
use Lidere\Core;

/**
 * Consulta
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Controllers\Consulta
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Relatorios extends Controller {

    public $url = 'assistencia-tecnica/relatorios/defeitos-item';

    public function defeitosItem()
    {
        
        Assets::add('/assets/js/relatorios.js', 'Assistencia');
        
        $this->app->render(
            'defeitosItem.html.twig',
            array(
                'data' => $this->app->service->defeitosItem()
            )
        );
    }


    public function listagem()
    {
        
        Assets::add('/assets/js/relatorios.js', 'Assistencia');

        $this->app->render(
            'listagem.html.twig',
            array(
                'data' => $this->app->service->listagem()
            )
        );
    }


    public function defeitosItemImprimir()
    {
        
        //Assets::add('/assets/js/relatorios.js', 'Assistencia');
        
        $this->app->render(
            'defeitosItemImprimir.html.twig',
            array(
                'data' => $this->app->service->defeitosItem()
            )
        );
    }

    public function listagemImprimir()
    {
        
        //Assets::add('/assets/js/relatorios.js', 'Assistencia');
        
        $this->app->render(
            'listagemImprimir.html.twig',
            array(
                'data' => $this->app->service->listagem()
            )
        );
    }

}