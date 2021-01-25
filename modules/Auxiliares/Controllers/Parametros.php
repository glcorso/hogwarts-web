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
 * @category Core
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Auxiliares\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Assets;

/**
 * Parametros
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Controllers\Parametros
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Parametros extends Controller
{
    public $url = 'auxiliares/parametros';

    public function index()
    {
        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list()
            )
        );
    }

    public function form($id = null)
    {
        Assets::add('/assets/js/parametros.js', 'Auxiliares');

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    public function edit()
    {
        $this->app->flash('success', $this->app->service->edit());
        $this->app->redirect($this->app->service->data['voltar']);
    }
}
