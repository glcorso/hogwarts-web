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
use Lidere\Config;
use Lidere\Assets;
use Lidere\Controllers\Controller;
use Lidere\Modules\TI\Models\Ticket;
use Lidere\Modules\TI\Models\Hour;

/**
 * Classe para controle das rotas do modulo de clientes/empresas
 *
 * @author Ramon Barros
 * @package Lidere\Modules\TI
 * @category Models
 */
class Companies extends Controller
{   
    public $url = 'ti/companies';

    public function index()
    {
       
        $companyObj = new \Lidere\Modules\TI\Models\Company();
        $projectObj = new \Lidere\Modules\TI\Models\Project();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        //$userObj = new \Lidere\Modules\Users\Models\User();

        $search = $this->app->request()->get('search') ? $this->app->request()->get('search') : false;
        $companies = $companyObj->getCompanies($search);

        if(count($companies) > 0){
            foreach($companies as &$company){
                $company['projects'] = $projectObj->getProjectsByCompanyId($company['id']);
                $company['tickets'] = $ticketObj->getTickets($company['id']);
                //$company['users'] = $userObj->getUsers($company['id']);
            }
        }

        Assets::add('/assets/js/companies.js', 'TI');

        $this->app->render('company/index.html.twig', array('companies' => $companies, 'search' => $search));
    }

    public function form($id = null) {
       
        $companyObj = new \Lidere\Modules\TI\Models\Company();
        $projectObj = new \Lidere\Modules\TI\Models\Project();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();
        $hourObj = new \Lidere\Modules\TI\Models\Hour();
        $applicationObj = new \Lidere\Models\Aplicacao();

        // POST: inclusão/edição
        if ( $this->app->request->isPost() ) {

            $data = $this->app->request()->post();

            if ( !empty($data['name']) ) {

                if ( !isset($data['id']) ) { // inclusão

                    $data['created_at'] = date('Y-m-d H:i:s');
                    $data['tax_percentage'] = !empty($data['tax_percentage']) ? Core::BRL2Float($data['tax_percentage']) : 0.00;
                    $data['km'] = !empty($data['km']) ? $data['km'] : null;
                    $id = $applicationObj->insert('company', $data);
                    $message = 'incluído';

                } else { // edição

                    $data['tax_percentage'] = !empty($data['tax_percentage']) ? Core::BRL2Float($data['tax_percentage']) : 0.00;

                    $id = $data['id'];
                    unset($data['id']);
                    $data['km'] = !empty($data['km']) ? $data['km'] : null;
                    $applicationObj->update('company', $id, $data);
                    $message = 'alterado';

                    // ao inativar o cliente, deve inativar os usuários relacionados
                    if($data['status'] == 'OFF'){
                        $update['status'] = $data['status'];
                        $applicationObj->updateByColumn('user', array('company_id' => $id), $update);
                    }

                }
                $this->app->flash('success', 'Cliente '.$message.' com sucesso!');
                $this->app->redirect('/ti/companies');
            }
        }

        $company = null;
        $hours = null;
        $projects = null;

        if ( $id ) {

            $company = $companyObj->getCompany($id);
            $hours = $hourObj->getHours('ON');
            if ( count($hours) > 0 ) {
                foreach ( $hours as &$hour ) {
                    $hour['value'] = Core::BRL($this->getValueHourByCompanyId($hour['id'], $id));
                }
            }

            $projects = $projectObj->getProjectsByCompanyId($id);
            if(count($projects)>0){
                foreach($projects as &$project){
                    $project['can_delete'] = count($ticketObj->getTickets(false, false, $project['id'])) > 0 ? false : true;
                }
            }

        }

        Assets::add('/assets/js/jquery.maskMoney.js', 'TI');
        Assets::add('/assets/js/companies.js', 'TI');

        $this->app->render('company/form.html.twig', array('company' => $company, 'hours' => $hours, 'projects' => $projects));
    }

    public function delete() {
       
        $id = $this->app->request()->post('id');

        $success = false;
        if ( $id ) {
            $applicationObj = new \Lidere\Models\Aplicacao();
            $success = $applicationObj->delete('company', $id);
        }

        if ( $success ) {
            $this->app->flash('success', 'Cliente excluído com sucesso!');
        } else {
            $this->app->flash('error', 'Falha ao excluir o cliente!');
        }
        $this->app->redirect('/ti/companies');
    }

    public function projectCreate() {
       
        $data = $this->app->request()->post();

        if($data['name'] == null){
            $this->app->redirect('/ti/companies/form/'.$data['company_id']);
        }

        $applicationObj = new \Lidere\Models\Aplicacao();

        if ( $data['company_id'] ) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $applicationObj->insert('project', $data);
        }

        $this->app->redirect('/ti/companies/form/'.$data['company_id']);
    }

    public function projectEdit() {
       
        $data = $this->app->request()->post();

        if($data['id'] == null || $data['name'] == null){
            $this->app->redirect('/ti/companies/form/'.$data['company_id']);
        }

        $applicationObj = new \Lidere\Models\Aplicacao();

        if ( $data['company_id'] && $data['id'] ) {
            $id = $data['id'];
            unset($data['id']);
            $applicationObj->update('project', $id, $data);
        }

        $this->app->redirect('/ti/companies/form/'.$data['company_id']);
    }

    public function projectDelete($company = null, $id = null) {
       
        $applicationObj = new \Lidere\Models\Aplicacao();
        $applicationObj->delete('project', $id);

        $this->app->redirect('/ti/companies/form/'.$company);
    }

    public function hourEdit() {
       
        $return = new \stdClass();
        $return->error = false;
        $return->message = null;

        $data = $this->app->request()->post();

        $applicationObj = new \Lidere\Models\Aplicacao();
        $hourObj = new \Lidere\Modules\TI\Models\Hour();

        if ( $data['value'] == null ) {
            $data['value'] = '00,00';
        }

        $data['value'] = Core::BRL2Float($data['value']);

        $hour = $hourObj->getHourByCompanyId($data['hour_id'], $data['company_id']);
        if ( $hour != null ) {
            $applicationObj->update('company_hours', $hour['id'], $data);
        } else {
            $applicationObj->insert('company_hours', $data);
        }
        $return->message = 'Valor da hora alterado com sucesso!';

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function ranking() {
       
        $get = $this->app->request()->get();

        $modelTicket = new \Lidere\Modules\TI\Models\Ticket();
        $modelCompany = new \Lidere\Modules\TI\Models\Company();

        $dataInicial = false;
        $dataFinal = false;
        $clienteId = false;
        $apenasClientesComFaturamento = $get ? false : true;

        if (isset($get['date_start'])) {
            $dataInicial = Core::data2Date($get['date_start']);
        }

        if (isset($get['date_end'])) {
            $dataFinal = Core::data2Date($get['date_end']);
        }

        if (isset($get['company_id'])) {
            $clienteId = $get['company_id'];
        }

        if (isset($get['only_company_with_billing'])) {
            $apenasClientesComFaturamento = filter_var($get['only_company_with_billing'], FILTER_VALIDATE_BOOLEAN);
        }

        $data = array();

        $data['accumulatedValue'] = $modelTicket->getAccumulatedValuePerCompanyWithIndex($dataInicial, $dataFinal, $clienteId, $apenasClientesComFaturamento);
        $data['total'] = $modelTicket->getSumOfAccumulatedValuePerCompanyWithIndex($dataInicial, $dataFinal, $clienteId, $apenasClientesComFaturamento);
        $data['companies'] = $modelCompany->getCompanies();
        $data['search'] = $get;

        $this->app->render('company/ranking.html.twig', array('data' => $data));
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

}
