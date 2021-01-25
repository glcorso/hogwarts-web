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
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Assistencia\Controllers;

use Lidere\Config;
use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Models\Auxiliares;
use Lidere\Models\Aplicacao;
use Lidere\Modules\Assistencia\Models\Atendimento as modelAssistencia;
use Lidere\Modules\Assistencia\Models\Motivos as motivosModel;
use Lidere\Modules\Assistencia\Models\Defeitos as defeitosModel;
use Lidere\Models\Usuario;
use Lidere\Modules\Assistencia\Models\RastreabilidadeGarantia as modelRastreabilidadeGarantia;

/**
 * Ajax
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Controllers\Ajax
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Ajax extends Controller
{
    public $url = false;

    public function retornaClienteAssistencia()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $post = $this->app->request()->post();
        $return->error = true;
        $cliente = false;
        $error = true;

        if (!empty($post['cpf_cnpj'])){
            $cliente = $modelAssistencia->getClienteAssistencia($post['cpf_cnpj']);
            $error = !empty($cliente) ? false : true;
        }

        $return->error = $error;
        $return->cliente = $cliente;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaClienteERP()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $post = $this->app->request()->post();
        $cliente = false;
        $return->error = true;
        $error = true;

        if (!empty($post['cpf_cnpj'])){
            $cliente = $modelAssistencia->getClienteERP($post['cpf_cnpj']);
            $error = !empty($cliente) ? false : true;
        }

        $return->error = $error;
        $return->cliente = $cliente;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }


    public function cadastrarCliente()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $post = $this->app->request()->post();
        $cliente = false;
        $return->error = true;
        $cliente_id = false;
        $error = true;

        if (!empty($post)){
            $cliente_id = $modelAssistencia->cadastrarCliente($post);
            $error = !empty($cliente_id) ? false : true;
            $post['id'] = $cliente_id;
        }

        $return->error = $error;
        $return->cliente = $post;
        $return->cliente_id = $cliente_id;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaSerieSequencial()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $post = $this->app->request()->post();
        $dadosSerie = false;
        $return->error = true;
        $error = true;

        if (!empty($post)){
            $dadosSerie = $modelAssistencia->getNumeroSerieOrSequencial($post['codigo'],$post['tipo']);
            $error = !empty($dadosSerie) ? false : true;
        }

        $return->error = $error;
        $return->dadosSerie = $dadosSerie;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }


    public function retornaItens()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $testaParametro = $get['testaParametro'];
        $return->items = $modelAssistencia->retornaItemDescricaoTecnica($string,false, $testaParametro);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaClientesSelect2()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelAssistencia->retornaClientes($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaMotivosSelect2()
    {
        $return = new \stdClass();
        $motivosModel = new motivosModel();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $motivosModel->retornaMotivosSelect2($string);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }


    public function retornaHistoricoAtendimentos()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $get = $this->app->request()->get();
        $historico = false;
        $return->error = true;
        $error = true;

        if (!empty($get)){
            $historico = $modelAssistencia->getAssistenciaRegistro('result',array('v.cpf_cnpj_sem_mascara = '=> "'".$get['cpf_cnpj']."'".' OR v.cpf_cnpj = '."'".$get['cpf_cnpj']."'"));
            $error = !empty($historico) ? false : true;
        }

        $return->error = $error;
        $return->historico = $historico;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaDefeitosSelect2()
    {
        $return = new \stdClass();
        $defeitosModel = new defeitosModel();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $defeitosModel->retornaDefeitosSelect2($string);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function verificaDefeitoObrigatorio()
    {
        $return = new \stdClass();
        $motivosModel = new motivosModel();

        $return->defeito_obrigatorio = false;

        $get = $this->app->request->get();

        $motivo = $motivosModel->getMotivos('row',array('m.id =' => $get['id']));

        if($motivo['defeito_obrigatorio'] == '1'){
            $return->defeito_obrigatorio = true;
        }else{
            $return->defeito_obrigatorio = false;
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function verificaProtocoloCliente() {

        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $get = $this->app->request()->get();
        $dadosChamado = false;
        $return->error = true;
        $error = true;

        if (!empty($get)){
            $dadosChamado = $modelAssistencia->getAssistenciaRegistro('row',array('v.cpf_cnpj_sem_mascara = '=> "'".$get['cpf_cnpj']."'", 'v.status_id <>' => '3'));
            $error = !empty($dadosChamado) ? false : true;
        }

        $return->error = $error;
        $return->dadosChamado = $dadosChamado;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));

    }

    public function retornaClientesAssistenciaSelect2()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelAssistencia->retornaClienteAssistenciaSelect2($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaFornecedoresSelect2()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelAssistencia->retornaFornecedores($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function adicionarAtendimentoProtocolo()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $post = $this->app->request()->post();
        $atendimento = false;
        $return->error = true;
        $atendimento_id = false;
        $error = true;

        if (!empty($post)){
            $atendimento_id = $modelAssistencia->adicionarAtendimentoProtocolo($post);
            $error = !empty($atendimento_id) ? false : true;
        }

        $return->error = $error;
        $return->atendimento_id = $atendimento_id;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function editarAtendimentoProtocolo()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $post = $this->app->request()->post();
        $atendimento    = false;
        $return->error  = true;
        $atendimento_id = false;
        $error = true;

        if (!empty($post)){
            $atendimento_id = $modelAssistencia->editarAtendimentoProtocolo($post);
            $error = !empty($atendimento_id) ? false : true;
        }

        $return->error = $error;
        $return->atendimento_id = $atendimento_id;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaChamadosErpSelect2() {

        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelAssistencia->getChamadosErp($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));

    }

    public function retornaClienteERPById()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();
        $post = $this->app->request()->post();
        $return->error = true;
        $cliente = false;
        $error = true;

        if (!empty($post['id'])){
            $cliente = $modelAssistencia->getClienteERPId($post['id']);
            $error = !empty($cliente) ? false : true;
        }

        $return->error = $error;
        $return->cliente = $cliente;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaClientesConsultaSelect2()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelAssistencia->retornaClienteConsultaSelect2($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }


    public function confirmarRecebimento()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->error = true;
        $atualizou = false;
        $post = $this->app->request->post();

        if(!empty($post)){
            $atualizou = $modelAssistencia->atualizaRecebimentoMaterial($post['registro_id']);
            if(!empty($post['chave_acesso'])){
                $upd['recebido_em'] = date('d/m/Y');
                $row = modelRastreabilidadeGarantia::where('chave_acesso',$post['chave_acesso']);
                $at = $row->update($upd);
            }
        }

        $return->error = !$atualizou ? true : false;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaClientesCnpjSelect2()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelAssistencia->retornaClientesCnpj($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaClientesUsuariosSelect2()
    {
        $return = new \stdClass();
        $modelAssistencia = new modelAssistencia();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = $modelAssistencia->retornaClientesUsuariosSelect2($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaUsuariosConsultaSelect2(){
        $return = new \stdClass();


        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $return->items = Usuario::retornaUsuarioAssitencias($string,false);

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }
}
