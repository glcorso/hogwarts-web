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
namespace Lidere\Modules\Assistencia\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Assistencia\Models\Atendimento as atendimentoModel;
use Lidere\Modules\Assistencia\Models\Motivos as motivosModel;
use Lidere\Modules\Assistencia\Models\Defeitos as defeitosModel;


/**
 * Consulta
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Consulta
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Consulta extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $atendimentoModel = new atendimentoModel();
        $auxiliaresModel  = new Auxiliares();
        $motivosModel     = new motivosModel();
        $defeitosModel    = new defeitosModel();

        
        $this->filtros = array();

        if (!empty($this->input['protocolo'])) {
            $this->filtros['v.protocolo'] = " = '".$this->input['protocolo']."'";
        }

        if (!empty($this->input['cliente_origem_id'])) {
            $this->filtros['v.cliente_origem_id'] = ' = '."'".$this->input['cliente_origem_id']."'";
            $cliente = $atendimentoModel->retornaClientes(false,$this->input['cliente_origem_id']);
            if(!empty($cliente)){
                $this->data['filtros']['cod_cli_origem']       = $cliente['codigo'];
                $this->data['filtros']['descricao_cli_origem'] = $cliente['descricao'];
            }
        }

        if (!empty($this->input['cliente_id'])) {
            $cli = explode('-', $this->input['cliente_id']);
            if($cli['0'] == 'I'){
                $this->filtros['v.cliente_assistencia_id'] = ' = '."'".$cli['1']."'";
                $cliente_ass = $atendimentoModel->retornaClienteAssistenciaSelect2(false,$cli['1']);
                if(!empty($cliente_ass)){
                    $this->data['filtros']['cod_cli_assistencia']       = $cliente_ass['codigo'];
                    $this->data['filtros']['descricao_cli_assistencia'] = $cliente_ass['descricao'];
                }
            }else{
                $this->filtros['v.cliente_assistencia_erp_id'] = ' = '."'".$cli['1']."'";
                $cliente_e = $atendimentoModel->retornaClientes(false,$cli['1']);
                if(!empty($cliente_e)){
                    $this->data['filtros']['cod_cli_assistencia']       = $cliente_e['codigo'];
                    $this->data['filtros']['descricao_cli_assistencia'] = $cliente_e['descricao'];
                }
            }
        }
       
        if (!empty($this->input['item_id'])) {

            $item = explode('-', $this->input['item_id']);
            if($item['0'] == 'E'){
                $this->filtros['v.item_id'] = " = '".$item['1']."'";
                $item_erp = $atendimentoModel->retornaItemErp($item['1']);
                if(!empty($item_erp)){
                    $this->data['filtros']['cod_item']     = $item_erp['cod_item'];
                    $this->data['filtros']['desc_tecnica'] = $item_erp['desc_tecnica'];
                }
            }else{
                $this->filtros['item_interno_id']  = " = '".$item['1']."'";
                $item_ass = $atendimentoModel->retornaItemAss($item['1']);
                if(!empty($item_ass)){
                    $this->data['filtros']['cod_item']     = $item_ass['cod_item'];
                    $this->data['filtros']['desc_tecnica'] = $item_ass['desc_tecnica'];
                }
            }
           
        }

        if (!empty($this->input['responsavel_id'])) {
            if($this->input['responsavel_id'] != 'TODOS') {
                $this->filtros['v.responsavel_id'] = " = '".$this->input['responsavel_id']."'";
            }
        }else{
            if($_SESSION['usuario']['id'] == '8' ){
                $this->filtros['v.responsavel_id'] = " = '".$_SESSION['usuario']['id']."'";
            }
            
        }

        if (!empty($this->input['serie'])) {
            $this->filtros['v.serie'] = " = '".$this->input['serie']."'";
        }
        if (!empty($this->input['material_recebido'])) {
            if($this->input['material_recebido'] == 'N'){
                $this->filtros['v.material_recebido'] = " IS NULL OR  v.material_recebido = '0'";
            }else{
                $this->filtros['v.material_recebido'] = " = '1'";
            }
        }

        if (!empty($this->input['nota_fiscal'])) {
            $this->filtros['v.nota_fiscal'] = " = '".$this->input['nota_fiscal']."'";
        }

        if (!empty($this->input['sequencial'])) {
            $this->filtros['v.sequencial'] = " = '".$this->input['sequencial']."'";
        }

        if (!empty($this->input['agrupador_item'])) {
            $this->filtros['v.agrupador_item'] = " = '".$this->input['agrupador_item']."'";
        }


        if (!empty($this->input['defeito_principal_id'])) {
            $this->filtros['v.defeito_principal_id'] = " = '".$this->input['defeito_principal_id']."'";
            $defeito = $defeitosModel->getDefeitos('row', array('id = '=> $this->input['defeito_principal_id'] ));
            if(!empty($defeito)){
                $this->data['filtros']['cod_defeito']       = $defeito['cod_defeito'];
                $this->data['filtros']['descricao_defeito'] = $defeito['descricao'];
            }
        }

        if (!empty($this->input['motivo_id'])) {
            $this->filtros['v.motivo_id'] = " = '".$this->input['motivo_id']."'";   
            $motivo = $motivosModel->getMotivos('row', array('id = '=> $this->input['motivo_id'] ));
            if(!empty($motivo)){
                $this->data['filtros']['cod_motivo']       = $motivo['cod_motivo'];
                $this->data['filtros']['descricao_motivo'] = $motivo['descricao'];
            }
        }

        if (!empty($this->input['data']) && $this->input['data'] != null) {
            $this->input['data'] = trim($this->input['data']);
            if (strpos($this->input['data'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['data']);
                $this->filtros['TRUNC(v.criado_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $this->filtros['TRUNC(v.criado_em)'] = " = '" . $this->input['data'] . "'";
            }
        }
       /* }else{
            $this->filtros['TRUNC(v.criado_em)'] = " BETWEEN TRUNC(SYSDATE)-30 AND TRUNC(SYSDATE)";
            $date = new \DateTime('-30 day');
            $date = $date->format('d/m/Y');
            $this->data['data_default_criado'] = $date."|".date('d/m/Y');
        }*/

        if (!empty($this->input['data_recebimento']) && $this->input['data_recebimento'] != null) {
            $this->input['data_recebimento'] = trim($this->input['data_recebimento']);
            if (strpos($this->input['data_recebimento'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['data_recebimento']);
                $this->filtros['TRUNC(v.recebido_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $this->filtros['TRUNC(v.recebido_em)'] = " = '" . $this->input['data_recebimento'] . "'";
            }
        }

        if (!empty($this->input['saida_produto'])) {
            $this->filtros['v.saida_produto'] = " = '".$this->input['saida_produto']."'";
        }
       
        $usuarios = $auxiliaresModel->usuarios('result');

        /* Total sem paginação */
        $total = count($atendimentoModel->getAssistenciaRegistro('result', $this->filtros));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $registros = $atendimentoModel->getAssistenciaRegistro('result', $this->filtros, array($inicio, $fim));
        $total_tela = count($registros);

        if (!empty($registros)) {
            foreach ($registros as &$registro) {
                $key = false;
                $key = Core::multidimensionalSearchArray($usuarios,array('id' => $registro['responsavel_id']));

                if($key !== false){
                    $registro['responsavel'] = $usuarios[$key]['nome'];
                }

                if(!empty($registro['item_interno_id'])){
                    $registro['item_id'] = 'I-'.$registro['item_interno_id'];
                }else{
                    $registro['item_id'] = 'E-'.$registro['item_id'];
                }

                $registro['permite_excluir'] = true;
                $registro['data'] = implode('/',(explode('-', $registro['criado_em'])));
            }
        }

        $this->data['agrupadores_item'] = $atendimentoModel->getAgrupadorListaItens('result');
        $this->data['resultado'] = $registros;
        $this->data['usuarios'] = $usuarios;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/assistencia-tecnica/consulta/pagina',
            $_SERVER['QUERY_STRING']
        );

        $this->data['query_string'] = $_SERVER['QUERY_STRING'];

        return $this->data;
    }


    public function listDados()
    {
        $atendimentoModel = new atendimentoModel();
        $auxiliaresModel  = new Auxiliares();
        $motivosModel     = new motivosModel();
        $defeitosModel    = new defeitosModel();

        
        $this->filtros = array();

        if (!empty($this->input['protocolo'])) {
            $this->filtros['v.protocolo'] = " = '".$this->input['protocolo']."'";
        }

        if (!empty($this->input['cliente_origem_id'])) {
            $this->filtros['v.cliente_origem_id'] = ' = '."'".$this->input['cliente_origem_id']."'";
            $cliente = $atendimentoModel->retornaClientes(false,$this->input['cliente_origem_id']);
            if(!empty($cliente)){
                $this->data['filtros']['cod_cli_origem']       = $cliente['codigo'];
                $this->data['filtros']['descricao_cli_origem'] = $cliente['descricao'];
            }
        }

        if (!empty($this->input['cliente_id'])) {
            $cli = explode('-', $this->input['cliente_id']);
            if($cli['0'] == 'I'){
                $this->filtros['v.cliente_assistencia_id'] = ' = '."'".$cli['1']."'";
                $cliente_ass = $atendimentoModel->retornaClienteAssistenciaSelect2(false,$cli['1']);
                if(!empty($cliente_ass)){
                    $this->data['filtros']['cod_cli_assistencia']       = $cliente_ass['codigo'];
                    $this->data['filtros']['descricao_cli_assistencia'] = $cliente_ass['descricao'];
                }
            }else{
                $this->filtros['v.cliente_assistencia_erp_id'] = ' = '."'".$cli['1']."'";
                $cliente_e = $atendimentoModel->retornaClientes(false,$cli['1']);
                if(!empty($cliente_e)){
                    $this->data['filtros']['cod_cli_assistencia']       = $cliente_e['codigo'];
                    $this->data['filtros']['descricao_cli_assistencia'] = $cliente_e['descricao'];
                }
            }
        }
       
        if (!empty($this->input['item_id'])) {

            $item = explode('-', $this->input['item_id']);
            if($item['0'] == 'E'){
                $this->filtros['v.item_id'] = " = '".$item['1']."'";
                $item_erp = $atendimentoModel->retornaItemErp($item['1']);
                if(!empty($item_erp)){
                    $this->data['filtros']['cod_item']     = $item_erp['cod_item'];
                    $this->data['filtros']['desc_tecnica'] = $item_erp['desc_tecnica'];
                }
            }else{
                $this->filtros['item_interno_id']  = " = '".$item['1']."'";
                $item_ass = $atendimentoModel->retornaItemAss($item['1']);
                if(!empty($item_ass)){
                    $this->data['filtros']['cod_item']     = $item_ass['cod_item'];
                    $this->data['filtros']['desc_tecnica'] = $item_ass['desc_tecnica'];
                }
            }
           
        }

        if (!empty($this->input['responsavel_id'])) {
            if($this->input['responsavel_id'] != 'TODOS') {
                $this->filtros['v.responsavel_id'] = " = '".$this->input['responsavel_id']."'";
            }
        }else{
            if($_SESSION['usuario']['id'] == '8' ){
                $this->filtros['v.responsavel_id'] = " = '".$_SESSION['usuario']['id']."'";
            }
            
        }

        if (!empty($this->input['serie'])) {
            $this->filtros['v.serie'] = " = '".$this->input['serie']."'";
        }
        if (!empty($this->input['material_recebido'])) {
            if($this->input['material_recebido'] == 'N'){
                $this->filtros['v.material_recebido'] = " IS NULL OR  v.material_recebido = '0'";
            }else{
                $this->filtros['v.material_recebido'] = " = '1'";
            }
        }

        if (!empty($this->input['nota_fiscal'])) {
            $this->filtros['v.nota_fiscal'] = " = '".$this->input['nota_fiscal']."'";
        }

        if (!empty($this->input['sequencial'])) {
            $this->filtros['v.sequencial'] = " = '".$this->input['sequencial']."'";
        }

        if (!empty($this->input['agrupador_item'])) {
            $this->filtros['v.agrupador_item'] = " = '".$this->input['agrupador_item']."'";
        }


        if (!empty($this->input['defeito_principal_id'])) {
            $this->filtros['v.defeito_principal_id'] = " = '".$this->input['defeito_principal_id']."'";
            $defeito = $defeitosModel->getDefeitos('row', array('id = '=> $this->input['defeito_principal_id'] ));
            if(!empty($defeito)){
                $this->data['filtros']['cod_defeito']       = $defeito['cod_defeito'];
                $this->data['filtros']['descricao_defeito'] = $defeito['descricao'];
            }
        }

        if (!empty($this->input['motivo_id'])) {
            $this->filtros['v.motivo_id'] = " = '".$this->input['motivo_id']."'";   
            $motivo = $motivosModel->getMotivos('row', array('id = '=> $this->input['motivo_id'] ));
            if(!empty($motivo)){
                $this->data['filtros']['cod_motivo']       = $motivo['cod_motivo'];
                $this->data['filtros']['descricao_motivo'] = $motivo['descricao'];
            }
        }

        if (!empty($this->input['data']) && $this->input['data'] != null) {
            $this->input['data'] = trim($this->input['data']);
            if (strpos($this->input['data'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['data']);
                $this->filtros['TRUNC(v.criado_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $this->filtros['TRUNC(v.criado_em)'] = " = '" . $this->input['data'] . "'";
            }
        }
       /* }else{
            $this->filtros['TRUNC(v.criado_em)'] = " BETWEEN TRUNC(SYSDATE)-30 AND TRUNC(SYSDATE)";
            $date = new \DateTime('-30 day');
            $date = $date->format('d/m/Y');
            $this->data['data_default_criado'] = $date."|".date('d/m/Y');
        }*/

        if (!empty($this->input['data_recebimento']) && $this->input['data_recebimento'] != null) {
            $this->input['data_recebimento'] = trim($this->input['data_recebimento']);
            if (strpos($this->input['data_recebimento'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['data_recebimento']);
                $this->filtros['TRUNC(v.recebido_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $this->filtros['TRUNC(v.recebido_em)'] = " = '" . $this->input['data_recebimento'] . "'";
            }
        }

        if (!empty($this->input['saida_produto'])) {
            $this->filtros['v.saida_produto'] = " = '".$this->input['saida_produto']."'";
        }
       
        $usuarios = $auxiliaresModel->usuarios('result');

        $registros = $atendimentoModel->getAssistenciaRegistro('result', $this->filtros);

        if (!empty($registros)) {
            foreach ($registros as &$registro) {
                $key = false;
                $key = Core::multidimensionalSearchArray($usuarios,array('id' => $registro['responsavel_id']));

                if($key !== false){
                    $registro['responsavel'] = $usuarios[$key]['nome'];
                }

                if(!empty($registro['item_interno_id'])){
                    $registro['item_id'] = 'I-'.$registro['item_interno_id'];
                }else{
                    $registro['item_id'] = 'E-'.$registro['item_id'];
                }

                $registro['permite_excluir'] = true;
                $registro['data'] = implode('/',(explode('-', $registro['criado_em'])));
            }
        }

        $this->data['resultado'] = $registros;
        
        return $this->data;
    }

}