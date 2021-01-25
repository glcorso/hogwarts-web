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
use Lidere\Modules\TI\Models\Hour;


class Tickets extends Controller {

    public $url = 'ti/tickets';

    public function index()
    {
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        $companyObj = new \Lidere\Modules\TI\Models\Company();
        $typeObj = new \Lidere\Modules\TI\Models\Type();
        $priorityObj = new \Lidere\Modules\TI\Models\Priority();
        $statusObj = new \Lidere\Modules\TI\Models\Status();
        $userObj = new \Lidere\Modules\Auxiliares\Models\Usuario();
	    $aplicacaoObj = new \Lidere\Models\Aplicacao();
	 
    	$modulo = $aplicacaoObj->buscaModulo(array('m.url' => 'ti/tickets'));

    	$permissao = $aplicacaoObj->buscaModuloUsuario(array('m.id' => ' = '.$modulo['id'], 'u.id' => ' = '.$_SESSION['usuario']['id']));
    	if (!$permissao) {
    		$permissao['permissao'] = 3;
    	} 

        $get = null;
        $search = null;

        $get = $this->app->request()->get();
        if ($get == null) {
            $search[] = array('date' => array(date('Y-m').'-01', date('Y-m-t')));
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
                    $search[] = array('t.user_internal_id = ' => $get['user_internal_id']);
                }

                if (isset($get['category']) && $get['category'] != null) {
                    $search[] = array('t.category = ' => $get['category']);
                }

                if (isset($get['status_id']) && $get['status_id'] != null) {
                    $search[] = array('ts.status_id = ' => $get['status_id']);
                }

                if (isset($get['titulo']) && $get['titulo'] != null) {
                    $search[] = array("UPPER(t.title) LIKE "  => "%".strtoupper($get['titulo'])."%");
                }

                $search[] = array('t.billed != ' => 'ON'); // os faturados nao devem aparecer na listagem
            }
        }

        if (1 == 1) {
            $tickets = $ticketObj->getTickets(false, false, false, $search);
        } else {
            $search[] = array('t.category = ' => 'S'); // So mostra chamados de suporte para o cliente
            $tickets = $ticketObj->getTickets($_SESSION['usuario']['company_id'], false, false, $search);
        }

        if (count($tickets) > 0) {
            foreach ($tickets as $k => &$ticket) {
                $ticket['add_called'] = $_SESSION['usuario']['tipo'] == 'admin' ? true : false;
                $ticket['del_ticket'] = $_SESSION['usuario']['tipo'] == 'admin' && $ticket['billed'] == 'OFF' ? true : false;
            }
        }

        if (1 == 1) {
            $companies = $companyObj->getCompanies();
            $is_admin = true;
        } else {
            $companies = $companyObj->getCompanyByUserId($_SESSION['usuario']['id']);
            $is_admin = false;
        }
        $options['companies'] = $companies;

        $options['types'] = $typeObj->getTypes();
        $options['priorities'] = $priorityObj->getPriorities();
        $options['users'] = $userObj->getAdminUsers();
        $options['status'] = $statusObj->getStatus();

        $filtros_default['start_date'] = '01/'.date('m/Y');
        $filtros_default['end_date'] = date('t/m/Y');

        Assets::add('/stylesheets/css/sb-admin.css');
        Assets::add('/assets/js/tickets.js', 'TI');

        $this->app->render('ticket/index.html.twig', array('filtros_default' => $filtros_default, 'tickets' => $tickets, 'options' => $options, 'search' => $get, 'is_admin' => $is_admin, 'permissao' => $permissao));
    }

    public function form($id = null) {

        $companyObj = new \Lidere\Modules\TI\Models\Company();
        $projectObj = new \Lidere\Modules\TI\Models\Project();
        $typeObj = new \Lidere\Modules\TI\Models\Type();
        $priorityObj = new \Lidere\Modules\TI\Models\Priority();
        $statusObj = new \Lidere\Modules\TI\Models\Status();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        $userObj = new \Lidere\Modules\Auxiliares\Models\Usuario();
        $applicationObj = new \Lidere\Models\Aplicacao();

        if ($id) {
            if (!1 == 1) {
                $ticket = $ticketObj->getTicketById($id);
                if ($ticket['company_id'] != $_SESSION['usuario']['company_id']) {
                    $this->app->redirect('/ti/tickets');
                }
            }
        }

    	$modulo = $applicationObj->buscaModulo(array('m.url' => 'ti/tickets'));

    	$permissao = $applicationObj->buscaModuloUsuario(array('m.id' => ' = '.$modulo['id'], 'u.id' => ' = '.$_SESSION['usuario']['id']));
    	if (!$permissao) {
    		$permissao['permissao'] = 3;
    	}

        // POST: inclusão/edição
        if ($this->app->request->isPost()) {
            $data = $this->app->request()->post();
           // var_dump($data);die;
            if (!empty($data['title'])) {
                if (!isset($data['id'])) { // inclusão

                    if(empty($data['punctuation'])) {
                        unset($data['punctuation']);
                    }

                    $data['user_id'] = !empty($data['user_id']) ? $data['user_id'] : $_SESSION['usuario']['id'];
                    $data['user_created_id'] = $_SESSION['usuario']['id'];

                    if (isset($data['created_at']) && $data['created_at'] != null) {

                        $data['created_at'] = Core::data2Date(trim($data['created_at']));
                        $data['created_at'] = $data['created_at'] . ' ' . date("H:i:s");
                    } else {
                        $data['created_at'] = date('Y-m-d H:i:s');
                    }
                    $date = $data['created_at'];

                    $id = $applicationObj->insert('ticket', $data);

                    /* ATENDIMENTO ESCONDIDO - UTILIZADO PARA FAZER A BUSCA */
                    $call['date'] = $date;
                    $call['hour_start'] = '00:00:00';
                    $call['hour_finish'] = '00:00:00';
                    $call['description'] = 'Atendimento criado automaticamente';
                    $call['ticket_id'] = $id;
                    $call['created_at'] = $data['created_at'];
                    $call['user_id'] = $_SESSION['usuario']['id'];
                    $call['hour_id'] = 999;
                    $call['hour_value'] = 0;
                    $call['type'] = 'HIDDEN';
                    $applicationObj->insert('ticket_called', $call);

                    /* STATUS */
                    $stat['ticket_id'] = $id;
                    $stat['status_id'] = 1;
                    $stat['created_at'] = date('Y-m-d H:i:s');
                    $stat['description'] = 'Status gerado automaticamente pelo portal.';
                    $applicationObj->insert('ticket_status', $stat);

                    //Core::sendEmailNewTicket($id);

                    $message = 'incluído';
                } else { // edição

                    $id = $data['id'];
                    $status_id = $data['status_id'];
                    unset($data['id']);
                    unset($data['status_id']);

                    $data['updated_at'] = date('Y-m-d H:i:s');

                    $applicationObj->update('ticket', $id, $data);

                    // testa se houve alteração de status
                    $ticket = $ticketObj->getTicketById($id);
                    $ticketStatus = $ticketObj->getTicketStatus($ticket['id']);
                    $last_status = end($ticketStatus);
                    if ($last_status['id'] != $status_id) {
                        $insertStatus['ticket_id'] = $ticket['id'];
                        $insertStatus['status_id'] = $status_id;
                        $insertStatus['created_at'] = date('Y-m-d H:i:s');
                        $insertStatus['description'] = 'Status alterado pelo usuário ' . $_SESSION['usuario']['nome'] . '.';
                        $ticket_status_id = $applicationObj->insert('ticket_status', $insertStatus);
                     ///   Core::sendEmailUpdateTicketStatus($ticket['id'], $ticket_status_id);
                    }

                    $message = 'alterado';
                }

                $this->app->flash('success', 'Chamado <a href="/ti/tickets/form/'.str_pad($id, 5, '0', STR_PAD_LEFT).'"><strong>'.str_pad($id, 5, '0', STR_PAD_LEFT).'</strong></a> '.$message.' com sucesso!');
                $this->app->redirect('/ti/tickets');
            }
        }

        $ticket = null;
        if ($id) {
            if($_SESSION['usuario']['tipo'] != 'admin'){
                $ticket = $ticketObj->getTicketByIdForm($id);
            }else{
                $ticket = $ticketObj->getTicketById($id);
            }

            if(empty($ticket)){
                $this->app->redirect('/ti/tickets');
            }
            $last_status = $ticketObj->getTicketStatus($ticket['id']);
            $ticket['last_status'] = end($last_status);
            $ticket['add_file'] = true;
            $ticket['add_called'] = $_SESSION['usuario']['tipo'] == 'admin' ? true : false;
            $ticket['add_expense'] = $_SESSION['usuario']['tipo'] == 'admin' ? true : false;
        }

        if (1 == 1) {
            $companies = $companyObj->getCompanies();
        } else {
            $companies = $companyObj->getCompanyByUserId($_SESSION['usuario']['id']);
        }

        if (count($companies)>0) {
            foreach ($companies as $k => &$company) {
                if ($company['status'] == 'OFF') {
                    unset($company[$k]);
                }
                $company['projects'] = $projectObj->getProjectsByCompanyId($company['id']);
            }
        }

        $types = $typeObj->getTypes();
        $priorities = $priorityObj->getPriorities();
        $status = $statusObj->getStatus();
        $admin_users = $userObj->getAdminUsers();

        /* Atendimentos */
        $calleds = $ticketObj->getCalledsTicket($ticket['id']);
        $total_hours = null;
        if (count($calleds) > 0) {
            foreach ($calleds as &$called) {
                $called['hour_start'] = $called['hour_start'] == null ? ' - ' : $called['hour_start'];
                $called['hour_finish'] = $called['hour_finish'] == null ? ' - ' : $called['hour_finish'];
                $called['hour_total'] = $called['hour_total'] != null ? substr($called['hour_total'], 0, -3) : ' - ';
                $called['can_delete'] = $called['user_id'] == $_SESSION['usuario']['id'] && $called['billed_at'] == null ? true : false;
                $called['hour_value'] = Core::BRL($called['hour_value']);
            }
            $total_hours = substr($ticketObj->getTotalHoursCalleds($ticket['id']), 0, -3);
        }

        /* Despesas */
        $expenses = $ticketObj->getExpensesTicket($ticket['id'], false, 'ALL');
        $total_value_expenses = null;
        if (count($expenses) > 0) {
            foreach ($expenses as &$expense) {
                $expense['list'] = ($_SESSION['usuario']['tipo'] == 'admin') || ($_SESSION['usuario']['tipo'] != 'admin' && $expense['company_billing'] == 'OFF') ? true : false;
                $expense['type'] = $expense['type'];
                $expense['val'] = Core::BRL($expense['value']);
                $expense['value'] = Core::BRL($expense['value'], 'R$');
                $expense['can_delete'] = $expense['user_id'] == $_SESSION['usuario']['id'] ? true : false;
            }
        }

        /* Arquivos */
        $files = $ticketObj->getFilesTicket($ticket['id']);
        if (count($files) > 0) {
            foreach ($files as &$file) {
                $file['link'] = base64_encode(microtime().'!'.$file['id'].'!'.$file['ticket_id'].'!'.$file['size'].'!'.$file['name']);
                $file['size'] = Core::sizeFilesize($file['size']);
                $file['can_delete'] = $file['user_id'] == $_SESSION['usuario']['id'] || $_SESSION['usuario']['tipo'] == 'admin' ? true : false;
            }
        }

        $expense_types = $ticketObj->getExpensesTypes();
        if (count($expense_types) > 0) {
            foreach ($expense_types as &$type) {
                $type['name'] = $type['name'] . ($type['calc_qty'] != null ? ' ('.Core::BRL($type['calc_qty'], 'R$ ').'/km)' : '');
                if ($type['calc_qty'] != null) {
                    $comp = $companyObj->getCompany($ticket['company_id']);
                    $km = $comp != null ? $comp['km'] : 0;
                    $type['default_value'] = Core::BRL($km*$type['calc_qty']);
                }
            }
        }

        $history = $ticketObj->getTicketStatus($ticket['id']);

       // Assets::add('/stylesheets/sb-admin.css');
        Assets::add('/javascripts/base/bootstrap-uploadfile/css/fileinput.min.css');
        Assets::add('/javascripts/base/redactor/redactor.css');
        Assets::add('/javascripts/base/redactor/redactor.js');
        Assets::add('/stylesheets/css/sb-admin.css');
        Assets::add('/assets/js/jquery.maskMoney.js', 'TI');
        Assets::add('/assets/js/tickets_form.js', 'TI');

        if ($_SESSION['usuario']['tipo'] == 'admin') {
            $this->app->render('ticket/form.html.twig', array('is_admin' => $_SESSION['usuario']['tipo'] == 'admin', 'admin_users' => $admin_users, 'ticket' => $ticket, 'companies' => $companies, 'types' => $types, 'priorities' => $priorities, 'status' => $status, 'calleds' => $calleds, 'expenses' => $expenses, 'history' => $history, 'total_hours' => $total_hours, 'expense_types' => $expense_types, 'files' => $files, 'usuario_logado' => $_SESSION['usuario'], 'permissao' => $permissao));
        } else {
            $this->app->render('ticket/formClient.html.twig', array('is_admin' => $_SESSION['usuario']['tipo'] == 'admin', 'admin_users' => $admin_users, 'ticket' => $ticket, 'companies' => $companies, 'types' => $types, 'priorities' => $priorities, 'status' => $status, 'calleds' => $calleds, 'expenses' => $expenses, 'history' => $history, 'total_hours' => $total_hours, 'expense_types' => $expense_types, 'files' => $files, 'usuario_logado' => $_SESSION['usuario'], 'permissao' => $permissao));
        }
    }

    public function delete() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        if (!1 == 1) {
            $this->app->redirect('/ti/tickets');
        }

        $data = $this->app->request()->post();

        $stat = array('ticket_id' => $data['id']);
        $applicationObj->deleteByColumn('ticket_status', $stat);

        $call = array('ticket_id' => $data['id']);
        $applicationObj->deleteByColumn('ticket_called', $call);

        $applicationObj->delete('ticket', $data['id']);

        $this->app->flash('success', 'Chamado excluído com sucesso!');
        $this->app->redirect('/ti/tickets');

    }

    public function addCalled() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        $developmentProjectObj = new \Lidere\Modules\TI\Models\DevelopmentProject();
        $hourObj = new \Lidere\Modules\TI\Models\Hour();

        $return = new \stdClass();
        $return->error = false;
        $return->message = null;
        $id = false;

        if (!1 == 1) {
            $return->error = true;
        }

        $data = $this->app->request()->post();

        if (isset($data['notification'])) {
            $notification = $data['notification'];
            unset($data['notification']);
        }

        if(empty($data['add_to_billing_report']) || $data['add_to_billing_report'] != 'ON') {
            $data['add_to_billing_report'] = 'OFF';
        }

        if (isset($data['id']) && empty($data['id'])) {
            unset($data['id']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['date'] = Core::data2Date($data['date']);

        $ticket_proj = $ticketObj->getTicketById($data['ticket_id']);

        if(!empty($ticket_proj['development_project_id'])){ /// CHAMADOS DE PROJETO
            $user_in_project = $developmentProjectObj->getDevProjectByUser($data['user_id'],$ticket_proj['development_project_id'], $data['date']);
            if(!empty($user_in_project)){
                $hours_used = $ticketObj->getTotalHoursCalledsByProject($data['user_id'],$ticket_proj['development_project_id'], $data['date']);
                $hours_dev  = $user_in_project['hours_dev'];

                if (isset($data['closed_value']) && $data['closed_value'] == 'ON') { // hora fechada
                    if(!empty($ticket_proj['development_project_id']) && $data['hour_id'] != '1003'){
                        $return->error = true;
                        $return->message = 'Este chamado possui um projeto. Para valor fechado é necessário utilizar: Hora Projeto Fechado.';
                    }else{
                        $data['hour_value'] = Core::BRL2Float($data['hour_value']);
                        $id = $applicationObj->insert('ticket_called', $data);
                    }
                } else {
                    if (strtotime($data['hour_start']) >= strtotime($data['hour_finish'])) {
                        $return->error = true;
                        $return->message = 'Por favor, digite um horário válido!';
                    } else {
                        $hour = $hourObj->getHour($data['hour_id']);
                        if($hour['goal'] == 'ON'){ // se a hora considera na meta realiza validação de horas
                            // Valida Horas
                            $hours_lcto = strtotime($data['hour_finish']) - strtotime($data['hour_start']);
                            $hours_lcto = (($hours_lcto / 60) / 60);
                            $hours_used = (($hours_used / 60) / 60);
                            $hours_used = (float)$hours_used;
                            $hours_dif  = ((float)$hours_dev-($hours_used + (float)$hours_lcto));
                            $hours_dif_text =  gmdate("H:i", abs(($hours_dif*60)*60));

                            if($hours_dif >= 0 ){
                                $ticket = $ticketObj->getTicketById($data['ticket_id']);
                                $data['hour_value'] = $this->getValueHourByCompanyId($data['hour_id'], $ticket['company_id']);

                                if ($this->hasCalledInSameTime($data['ticket_id'], $data['date'], $data['hour_start'], $data['hour_finish'])) { // usuário já possui atendimento no mesmo dia e hora para o chamado
                                    $return->error = true;
                                    $return->message = 'Já existe atendimento para este intervalo.';
                                } else {
                                    $id = $applicationObj->insert('ticket_called', $data);
                                    $applicationObj->update('development_project',$ticket_proj['development_project_id'],array('status' => 'ATTENDANCE'));
                                }
                            }else{
                                $return->error = true;
                                $return->message = 'O Consultor excedeu o número de horas do projeto. Horas Excedidas: '.$hours_dif_text;
                            }
                        }else{
                            $ticket = $ticketObj->getTicketById($data['ticket_id']);
                            $data['hour_value'] = $this->getValueHourByCompanyId($data['hour_id'], $ticket['company_id']);

                            if ($this->hasCalledInSameTime($data['ticket_id'], $data['date'], $data['hour_start'], $data['hour_finish'])) { // usuário já possui atendimento no mesmo dia e hora para o chamado
                                $return->error = true;
                                $return->message = 'Já existe atendimento para este intervalo.';
                            } else {
                                $id = $applicationObj->insert('ticket_called', $data);
                                $applicationObj->update('development_project',$ticket_proj['development_project_id'],array('status' => 'ATTENDANCE'));
                            }
                        }
                    }
                }
            }else{
                $return->error = true;
                $return->message = 'O Consultor não faz parte do projeto associado a este chamado.';
            }
        }else{

            if (isset($data['closed_value']) && $data['closed_value'] == 'ON') { // hora fechada
                    $data['hour_value'] = Core::BRL2Float($data['hour_value']);
                    $id = $applicationObj->insert('ticket_called', $data);
            } else {
                if (strtotime($data['hour_start']) >= strtotime($data['hour_finish'])) {
                    $return->error = true;
                    $return->message = 'Por favor, digite um horário válido!';
                } else {
                    $ticket = $ticketObj->getTicketById($data['ticket_id']);
                    $data['hour_value'] = $this->getValueHourByCompanyId($data['hour_id'], $ticket['company_id']);

                    if ($this->hasCalledInSameTime($data['ticket_id'], $data['date'], $data['hour_start'], $data['hour_finish'])) { // usuário já possui atendimento no mesmo dia e hora para o chamado
                        $return->error = true;
                        $return->message = 'Já existe atendimento para este intervalo.';
                    } else {
                        $id = $applicationObj->insert('ticket_called', $data);
                    }
                }
            }

        }

        /* se o chamado já estiver faturado total, então altera o status de faturamento para PARCIAL */
        $ticket = $ticketObj->getTicketById($data['ticket_id']);
        if ($ticket['billed'] == 'ON') {
            $upd['billed'] = 'PARTIAL';
            $applicationObj->update('ticket', $data['ticket_id'], $upd);
        }

        if (!$id) {
            $return->error = true;
        } else {
            // insere o status de "EM ATENDIMENTO" se ainda não existir
        /*** Comentado pois no php 7 não funciona
            $last = $ticketObj->getTicketStatus($data['ticket_id']);
            $last_status = end($last);
        ***/
            $tk = ($ticketObj->getTicketStatus($data['ticket_id']));
            $last_status = end($tk);

            if ($last_status['id'] == 1) { // aguardando atendimento
                $status['ticket_id'] = $data['ticket_id'];
                $status['status_id'] = 2;
                $status['created_at'] = date('Y-m-d H:i:s');
                $status['description'] = 'Status gerado automaticamente pelo portal.';
                $applicationObj->insert('ticket_status', $status);
            }

            // envia o e-mail para o cliente se o usuário marcou para mandar
            if (isset($notification)) {
             //   Core::sendEmailNewCalled($data['ticket_id'], $id);
            }

            $return->message = 'Atendimento incluído com sucesso.';
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));

    }

    public function updateCalled() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        $developmentProjectObj = new \Lidere\Modules\TI\Models\DevelopmentProject();
        $hourObj = new \Lidere\Modules\TI\Models\Hour();

        $return = new \stdClass();
        $return->error = false;
        $return->message = null;

        if (!1 == 1) {
            $return->error = true;
        }

        $data = $this->app->request()->post();

        if (isset($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
        }

        if (isset($data['notification'])) {
            $notification = $data['notification'];
            unset($data['notification']);
        }

        if(empty($data['add_to_billing_report']) || $data['add_to_billing_report'] != 'ON') {
            $data['add_to_billing_report'] = 'OFF';
        }

        $data['date'] = Core::data2Date($data['date']);

        $ticket_proj = $ticketObj->getTicketById($data['ticket_id']);

        if(!empty($ticket_proj['development_project_id'])){ /// CHAMADOS DE PROJETO
            $user_in_project = $developmentProjectObj->getDevProjectByUser($data['user_id'],$ticket_proj['development_project_id'], $data['date']);
            if(!empty($user_in_project)){
                $hours_used = $ticketObj->getTotalHoursCalledsByProject($data['user_id'],$ticket_proj['development_project_id'], $data['date']);
                $hours_dev  = $user_in_project['hours_dev'];
                $hours_old  = $ticketObj->getTotalHourByCalled($id);

                if (isset($data['closed_value']) && $data['closed_value'] == 'ON') { // hora fechada
                    if(!empty($ticket_proj['development_project_id']) && $data['hour_id'] != '1003'){
                        $return->error = true;
                        $return->message = 'Este chamado possui um projeto. Para valor fechado é necessário utilizar: Hora Projeto Fechado.';
                    }else{
                    // insere hora inicial e final null e inclui o valor fechado
                        $data['hour_start'] = null;
                        $data['hour_finish'] = null;
                        $data['hour_value'] = Core::BRL2Float($data['hour_value']);
                        $applicationObj->update('ticket_called', $id, $data);
                        $return->message = 'Atendimento alterado com sucesso.';
                    }
                }else{
                    if (strtotime($data['hour_start']) >= strtotime($data['hour_finish'])) {
                        $return->error = true;
                        $return->message = 'Por favor, digite um horário válido!';
                    } else {

                        $hour = $hourObj->getHour($data['hour_id']);
                        if($hour['goal'] == 'ON'){ // se a hora considera na meta realiza validação de horas
                            $calledDate = $ticketObj->getDateCalledById($id);
                            // Valida Horas
                            $hours_lcto = strtotime($data['hour_finish']) - strtotime($data['hour_start']);
                            $hours_lcto = (($hours_lcto / 60) / 60);
                            $hours_used = (($hours_used / 60) / 60);
                            $hours_old  = (($hours_old / 60) / 60);

                            if (substr($calledDate, 0, strlen($calledDate) - 3) == substr($data['date'], 0, strlen($data['date']) - 3)) {
                                $hours_used = (float)$hours_used - $hours_old ; // diminui as horas atuais pq vao ser retiradas para as novas
                            }
                            $hours_dif  = ((float)$hours_dev-($hours_used + (float)$hours_lcto));
                            $hours_dif_text =  gmdate("H:i", abs(($hours_dif*60)*60));

                            if($hours_dif >= 0 ){
                                $ticket = $ticketObj->getTicketById($data['ticket_id']);
                                $data['hour_value'] = $this->getValueHourByCompanyId($data['hour_id'], $ticket['company_id']);
                                $data['closed_value'] = 'OFF';
                                    // valida a existência de atendimento no mesmo intervalo para o mesmo chamado e usuário
                               if ($this->hasCalledInSameTime($data['ticket_id'], $data['date'], $data['hour_start'], $data['hour_finish'], $id)) { // usuário já possui atendimento no mesmo dia e hora para o chamado
                                    $return->error = true;
                                    $return->message = 'Já existe atendimento para este intervalo.';
                                } else {
                                    $applicationObj->update('ticket_called', $id, $data);
                                    $return->message = 'Atendimento alterado com sucesso.';
                                }
                            }else{
                                $return->error = true;
                                $return->message = 'O Consultor excedeu o número de horas do projeto. Horas Excedidas: '.$hours_dif_text;
                            }
                        }else{
                            $ticket = $ticketObj->getTicketById($data['ticket_id']);
                            $data['hour_value'] = $this->getValueHourByCompanyId($data['hour_id'], $ticket['company_id']);
                            $data['closed_value'] = 'OFF';
                                // valida a existência de atendimento no mesmo intervalo para o mesmo chamado e usuário
                            if ($this->hasCalledInSameTime($data['ticket_id'], $data['date'], $data['hour_start'], $data['hour_finish'], $id)) { // usuário já possui atendimento no mesmo dia e hora para o chamado
                                $return->error = true;
                                $return->message = 'Já existe atendimento para este intervalo.';
                            } else {
                                $applicationObj->update('ticket_called', $id, $data);
                                $return->message = 'Atendimento alterado com sucesso.';
                            }
                        }
                        // envia o e-mail para o cliente se o usuário marcou para mandar
                        if (isset($notification)) {
                            Core::sendEmailUpdateCalled($data['ticket_id'], $id);
                        }
                    }
                }
            }else{
                $return->error = true;
                $return->message = 'O Consultor não faz parte do projeto associado a este chamado.';
            }
        }else{

            if (isset($data['closed_value']) && $data['closed_value'] == 'ON') { // hora fechada

                // insere hora inicial e final null e inclui o valor fechado
                $data['hour_start'] = null;
                $data['hour_finish'] = null;
                $data['hour_value'] = Core::BRL2Float($data['hour_value']);
                $applicationObj->update('ticket_called', $id, $data);
                $return->message = 'Atendimento alterado com sucesso.';
            } else {
                if (strtotime($data['hour_start']) >= strtotime($data['hour_finish'])) {
                    $return->error = true;
                    $return->message = 'Por favor, digite um horário válido!';
                } else {
                    $ticket = $ticketObj->getTicketById($data['ticket_id']);
                    $data['hour_value'] = $this->getValueHourByCompanyId($data['hour_id'], $ticket['company_id']);
                    $data['closed_value'] = 'OFF';
                    // valida a existência de atendimento no mesmo intervalo para o mesmo chamado e usuário
                    if ($this->hasCalledInSameTime($data['ticket_id'], $data['date'], $data['hour_start'], $data['hour_finish'], $id)) { // usuário já possui atendimento no mesmo dia e hora para o chamado
                        $return->error = true;
                        $return->message = 'Já existe atendimento para este intervalo.';
                    } else {
                        $applicationObj->update('ticket_called', $id, $data);
                        $return->message = 'Atendimento alterado com sucesso.';
                    }

                    // envia o e-mail para o cliente se o usuário marcou para mandar
                    if (isset($notification)) {
                        Core::sendEmailUpdateCalled($data['ticket_id'], $id);
                    }
                }
            }
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));

    }

    public function deleteCalled() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $data = $this->app->request()->post();

        if (!1 == 1) {
            $this->app->redirect('/ti/tickets/form/'.$data['ticket_id']);
        }

        $called = $ticketObj->getTicketCalled($data['id']);
        if ($called['user_id'] == $_SESSION['usuario']['id']) {
            $applicationObj->delete('ticket_called', $data['id']);
        }

        /* se o chamado já estiver faturado total ou parcial, então altera o status de faturamento, conforme a qtde de atendimentos */
        $ticket = $ticketObj->getTicketById($data['ticket_id']);
        if ($ticket['billed'] != 'OFF') {
            $concluido = false;
            $faturado = false;
            $calleds = $ticketObj->getCalledsTicket($data['ticket_id']);
            if (count($calleds) > 0) {
                foreach ($calleds as $call) {
                    if ($call['status'] == 'CONCLUIDO') {
                        $concluido = true;
                    }
                    if ($call['status'] == 'FATURADO') {
                        $faturado = true;
                    }
                }
            }
            if ($concluido && $faturado) {
                $upd['billed'] = 'PARTIAL';
            } elseif ($faturado && !$concluido) {
                $upd['billed'] = 'ON';
            }
            $applicationObj->update('ticket', $data['ticket_id'], $upd);
        }

        $this->app->redirect('/ti/tickets/form/'.$data['ticket_id']);

    }

    public function print($id = null) {
        // GET: relatório total (para faturamento)
        // POST: seleção de atendimentos (para impressão da RAT no cliente)
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        if (!1 == 1) {
            $this->app->redirect('/ti/tickets');
        }

        $ticket = $ticketObj->getTicketById($id);
        if ($ticket != null) {
            $ticket['date_br'] = substr($ticket['created_at_br'], 0, -6);
        }

        $ids = false;
        if ($this->app->request->isPost() && $this->app->request->post('calleds')) {
            $ids = $this->app->request->post('calleds');
            $calleds = $ticketObj->getTicketCalledsById($ticket['id'], $ids, 'ASC');
            $file = 'rat';
        } else {
            $calleds = $ticketObj->getCalledsTicket($ticket['id'], false, false, 'ASC', 'billing');
            $file = 'billing';
        }
        $total_hours = null;
        $billing_hours = 0;
        $billing_value = 0;
        if (count($calleds) > 0) {
            $total_hours = substr($ticketObj->getTotalHoursCalleds($ticket['id'], $ids, false, 'billing'), 0, -3);
            $billing_hours = substr($ticketObj->getTotalHoursCalleds($ticket['id'], false, true, 'billing'), 0, -3);

            foreach ($calleds as $c => &$called) {
                if ($this->app->request->isPost() || ($this->app->request->isGet() && $called['status'] == 'CONCLUIDO')) {
                    if ($called['closed_value'] == 'OFF') {
                        $called['hour_total'] = substr($called['hour_total'], 0, -3);

                        $val_minute = $called['hour_value'] / 60;
                        $called['value'] = $val_minute * $called['minutes'];
                    } else {
                        $called['hour_total'] = '-';
                        $called['value'] = $called['hour_value'];
                        $billing_hours = '-';
                    }
                    $called['description'] = nl2br($called['description']);
                    $billing_value += $called['value'];
                } else {
                    unset($calleds[$c]);
                }
            }
        }

        // despesas
        $expenses_value = 0;
        $expenses = $ticketObj->getExpensesTicketNotPay($ticket['id'], false, 'ON');
        if (count($expenses) > 0) {
            foreach ($expenses as &$expense) {
                $expense['value_br'] = Core::BRL($expense['value']);
                $expenses_value += $expense['value'];
            }
            $billing_value += $expenses_value;
        }

        $this->app->render('ticket/'.$file.'.html.twig', array('ticket' => $ticket, 'calleds' => $calleds, 'expenses' => $expenses, 'expenses_value' => ($expenses_value > 0 ? Core::BRL($expenses_value, 'R$ ') : $expenses_value), 'total_hours' => $total_hours, 'billing_hours' => ($billing_hours != null ? $billing_hours : '00:00'), 'billing_value' => Core::BRL($billing_value, 'R$')));

    }

    public function addExpense() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $return = new \stdClass();
        $return->error = false;
        $return->message = null;

        if (!1 == 1) {
            $return->error = true;
        }

        $data = $this->app->request()->post();

        if (!isset($data['company_billing'])) {
            $data['company_billing'] = 'OFF';
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['date'] = implode('-', array_reverse(explode('/', $data['date'])));
        $data['user_id'] = $_SESSION['usuario']['id'];
        $data['value'] = Core::BRL2Float($data['value']);

        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $applicationObj->update('ticket_expense', $id, $data);
            $return->message = 'Despesa alterada com sucesso.';
        } else {
            unset($data['id']);
            $applicationObj->insert('ticket_expense', $data);
            $return->message = 'Despesa incluída com sucesso.';
        }


        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));

    }

    public function deleteExpense() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $data = $this->app->request()->post();

        if (!1 == 1) {
            $this->app->redirect('/ti/tickets/form/'.$data['ticket_id']);
        }

        $expense = $ticketObj->getTicketExpenseById($data['id']);
        if ($expense['user_id'] == $_SESSION['usuario']['id']) {
            $applicationObj->delete('ticket_expense', $data['id']);
        }

        $this->app->redirect('/ti/tickets/form/'.$data['ticket_id']);

    }

    public function addFile() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $data = $this->app->request()->post();
        $file = $_FILES;

        $ticket = $ticketObj->getTicketById($data['ticket_id']);
        if ($file['file']['size'] > 0 && $file['file']['error'] === 0) {
            $insertFile['description'] = $data['description'];
            $insertFile['name'] = $file['file']['name'];
            $insertFile['type'] = $file['file']['type'];
            $insertFile['size'] = $file['file']['size'];
            $insertFile['content'] = addslashes(file_get_contents($file['file']['tmp_name']));
            $insertFile['created_at'] = date('Y-m-d H:i:s');
            $insertFile['ticket_id'] = $data['ticket_id'];
            $insertFile['user_id'] = $_SESSION['usuario']['id'];
            try {
                $applicationObj->insert('file', $insertFile);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        $this->app->redirect('/ti/tickets/form/'.$data['ticket_id']);
    }

    public function deleteFile() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $data = $this->app->request()->post();

        //if (!1 == 1) {
        //   $this->app->redirect('/ti/tickets/form/'.$data['ticket_id']);
        //}

        $file = $ticketObj->getFileById($data['id']);

        if (isset($file['user_internal_id']) && $file['user_internal_id'] == $_SESSION['usuario']['id'] || $file['user_id'] == $_SESSION['usuario']['id'] || $_SESSION['usuario']['tipo'] == 'admin') {
            $applicationObj->delete('file', $file['id']);
        }

        $this->app->redirect('/ti/tickets/form/'.$data['ticket_id']);

    }

    public function download($link = null) {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        if ($link == null) {
            echo "Operação inválida!";
        }

        $link = base64_decode($link);
        try {
            list($time, $id, $ticket_id, $size, $name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }

        $file = $ticketObj->getFileById($id);

        if ($file['ticket_id'] != $ticket_id) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['type']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['name']));
            $response->body($file['content']);
        }
    }

    public function getUsers() {
        $applicationObj = new \Lidere\Models\Aplicacao();
        $projectObj = new \Lidere\Modules\TI\Models\Project();
        $userObj = new \Lidere\Modules\Auxiliares\Models\Usuario();

        $return = new \stdClass();
        $return->error = false;
        $return->message = null;
        $return->content = null;

        $post = $this->app->request()->post();

        $project = $projectObj->getProjectById($post['project_id']);
        $return->content = $userObj->getUsers($project['company_id']);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));

    }

    private function getValueHourByCompanyId($hour_id, $company_id) {

        $hourObj = new Hour();

        // busca os valores específicos para o cliente e se não encontrar, busca os defaults
        $hour = $hourObj->getHourByCompanyId($hour_id, $company_id);
        if ( $hour == null ) {
            $hour = $hourObj->getHour($hour_id);
        }

        return $hour['value'];

    }

    private function hasCalledInSameTime($ticket_id = false, $date = false, $hour_start = false, $hour_finish = false, $id = false) {

        if ( !$ticket_id || !$date || !$hour_start || !$hour_finish ) {
            return true;
        }

        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        $calleds = $ticketObj->getCalledsTicket($ticket_id, $date, $_SESSION['usuario']['id']);

        if ( count($calleds) == 0 ) {
            return false;
        } else {
            foreach ( $calleds as $call ) {

                if ( $hour_start > $call['hour_start'] && $hour_start < $call['hour_finish'] ) {
                    // inicia entre intervalo existente
                    if ( $id && $id != $call['id'] ) { // se chegou ID é alteração, então no teste deve desconsiderar este mesmo registro
                        return true;
                    }
                } elseif ( $hour_finish > $call['hour_start'] && $hour_start < $call['hour_finish'] ) {
                    // termina entre intervalo existente
                    if ( $id && $id != $call['id'] ) { // se chegou ID é alteração, então no teste deve desconsiderar este mesmo registro
                        return true;
                    }
                } elseif ( $hour_start < $call['hour_start'] && $hour_finish > $call['hour_finish'] ) {
                    // inicia antes e termina depois de um intervalo existente
                    if ( $id && $id != $call['id'] ) { // se chegou ID é alteração, então no teste deve desconsiderar este mesmo registro
                        return true;
                    }
                }

            }

            return false;
        }

    }

}
