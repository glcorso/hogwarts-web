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
namespace Lidere\Modules\PlanoProducao\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Assets;
use Lidere\Core;

/**
 * Consulta
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage PlanoProducao\Controllers\Consulta
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Consulta extends Controller {

    public $url = 'plano-producao/consulta';

    public function index()
    {
    
        Assets::add('/assets/js/consulta.js', 'PlanoProducao');
        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->index()
            )
        );
    }

}