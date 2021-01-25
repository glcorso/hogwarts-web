<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2017  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\TI\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;

class Panels extends Controller 
{
    public $url = 'ti/panel';

    public function index() {
    
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $get = null;
        $search = null;
        $tickets = null;

        $get = $this->app->request()->get();

        $search[] = array('t.category = ' => 'S');
        $search[] = array('ts.status_id <> ' => '6');

        $tickets = $ticketObj->getTicketsPanel(false, false, false, $search);
        //Assets::add('/assets/js/panel.js', 'Panels');
        $this->app->render('panel/index.html.twig', array('tickets' => $tickets));
    }
}
