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
use Lidere\Assets;
use Lidere\Controllers\Controller;

/**
 * Classe para controle das rotas do modulo de atendimentos
 *
 * @author Ramon Barros
 * @package Lidere\Modules\TI
 * @category Models
 */
class Attendances extends Controller
{   
    public $url = 'ti/attendance';

    public function index()
    {

        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        $companyObj = new \Lidere\Modules\TI\Models\Company();
        $typeObj = new \Lidere\Modules\TI\Models\Type();
        $priorityObj = new \Lidere\Modules\TI\Models\Priority();
        $statusObj = new \Lidere\Modules\TI\Models\Status();
        $userObj = new \Lidere\Modules\Auxiliares\Models\Usuario();
        $hourObj = new \Lidere\Modules\TI\Models\Hour();


        $get = null;
        $search = null;
        $search_user = null;
        $get = $this->app->request()->get();

        if ($get == null) {
            $search[] = array('date' => array(date('Y-m-d'), date('Y-m-d'))); // se não estiver preenchido pega o dia
        } else {
            if (isset($get['id']) && $get['id'] != null) { // se informar o id, desconsidera todos os outros filtros
                $search[] = array('t.id = ' => $get['id']);
            } else {
                $start = isset($get['date_start']) && $get['date_start'] != null ? Core::data2Date($get['date_start']) : date('Y').'-01-01';
                $end = isset($get['date_end']) && $get['date_end'] != null ? Core::data2Date($get['date_end']) : date('Y').'-12-31';
                $search[] = array('date' => array($start, $end));

                if (isset($get['company_id']) && $get['company_id'] != null) {
                    $search[] = array('c.id = ' => $get['company_id']);
                }

                if (isset($get['type_id']) && $get['type_id'] != null) {
                    $search[] = array('t.type_id = ' => $get['type_id']);
                }

                if (isset($get['priority_id']) && $get['priority_id'] != null) {
                    $search[] = array('t.priority_id = ' => $get['priority_id']);
                }

                if (isset($get['user_internal_id']) && $get['user_internal_id'] != null) {
                    $search_user['id'] = ' = '.$get['user_internal_id'];
                }

                if (isset($get['category']) && $get['category'] != null) {
                    $search[] = array('t.category = ' => $get['category']);
                }

                if (isset($get['hour_id']) && $get['hour_id'] != null) {
                    $search[] = array('h.id = ' => $get['hour_id']);
                }
            }
        }

        $users = $userObj->getAdminUsersByAttendance($search_user);

        if(!empty($users)){
            foreach($users as &$user){
                $calleds = $ticketObj->getCalledsByAttendance($user['id'],$search);
                $billing_value = 0;
                if ( !empty($calleds)) {
                    foreach ( $calleds as &$called ) {
                        $called['date_br'] = Core::date2Data($called['date']);
                        $called['hour_start'] = $called['hour_start'] == null ? ' - ' : $called['hour_start'];
                        $called['hour_finish'] = $called['hour_finish'] == null ? ' - ' : $called['hour_finish'];
                        if ($called['closed_value'] == 'OFF') {
                            $called['hour_total'] = substr($called['hour_total'], 0, -3);

                            $val_minute = $called['hour_value'] / 60;
                            $called['value'] = $val_minute * $called['minutes'];
                        } else {
                            $called['hour_total'] = '-';
                            $called['value'] = $called['hour_value'];
                            $billing_hours = '-';
                        }

                        $billing_value += $called['value'];
                    }

                    $user['billing_value'] = $billing_value;
                    $user['total_hours'] = $ticketObj->getTotalHoursCalledsByAttendance($user['id'],$search);
                    $user['total_hours'] = substr($user['total_hours'], 0,5);
                }
                $user['calleds'] = $calleds;
            }
        }

        $companies = $companyObj->getCompanies();
        $is_admin = true;

        $options['companies'] = $companies;

        $options['types'] = $typeObj->getTypes();
        $options['priorities'] = $priorityObj->getPriorities();
        $options['users'] = $userObj->getAdminUsers();
        $options['status'] = $statusObj->getStatus();
        $options['hours'] = $hourObj->getHours('ON');

        $filtros_default['start_date'] = date('d/m/Y');
        $filtros_default['end_date'] = date('d/m/Y');

	    Assets::add('/assets/js/attendance.js', 'TI');

        $this->app->render('attendance/index.html.twig', array('filtros_default' => $filtros_default,'users' => $users,'search' => $get, 'options' => $options));
    }
}
