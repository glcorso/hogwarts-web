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
use Lidere\Models\Aplicacao;

class Hours extends Controller 
{   
    public $url = 'ti/type-hours';

    public function index() 
    {
        $hourObj = new \Lidere\Modules\TI\Models\Hour();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $hours = $hourObj->getHours();
        if(count($hours) > 0){
            foreach($hours as &$hour){
                $hour['calleds'] = $ticketObj->getCalledsByHourId($hour['id']);
                $hour['value'] = Core::BRL($hour['value'], 'R$');
            }
        }

        $this->app->render('type-hour/index.html.twig', array('hours' => $hours));
    }

    public function form($id = null) {
        $hourObj = new \Lidere\Modules\TI\Models\Hour();
        $applicationObj = new Aplicacao();

        // POST: inclusão/edição
        if ( $this->app->request->isPost() ) {

            $data = $this->app->request()->post();

            if ( !empty($data['name']) && !empty($data['value']) ) {

                if ( !isset($data['id']) ) { // inclusão

                    $data['value'] = Core::BRL2Float($data['value']);
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $id = $applicationObj->insert('hour', $data);
                    $message = 'incluído';

                } else { // edição

                    $id = $data['id'];
                    unset($data['id']);
                    $data['value'] = Core::BRL2Float($data['value']);
                    $applicationObj->update('hour', $id, $data);
                    $message = 'alterado';
                }
                $this->app->flash('success', 'Tipo de hora '.$message.' com sucesso!');
                $this->app->redirect('/ti/type-hours');
            }
        }

        $hour = null;
        if ( $id ) {
            $hour = $hourObj->getHour($id);
            $hour['value'] = Core::BRL($hour['value']);
        }

        Assets::add('/assets/js/jquery.maskMoney.js', 'TI');

        $this->app->render('type-hour/form.html.twig', array('hour' => $hour));
    }

    public function delete() {
        $id = $this->app->request()->post('id');

        $success = false;
        if ( $id ) {
            $applicationObj = new Aplicacao();
            $success = $applicationObj->delete('hour', $id);
        }

        if ( $success ) {
            $this->app->flash('success', 'Tipo de hora excluído com sucesso!');
        } else {
            $this->app->flash('error', 'Falha ao excluir o tipo de hora!');
        }
        $this->app->redirect('/ti/type-hours');

    }
}
