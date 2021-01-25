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
 * Relatorios
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Relatorios
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Relatorios extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function defeitosItem()
    {
        $atendimentoModel = new atendimentoModel();
        $auxiliaresModel  = new Auxiliares();
        $motivosModel     = new motivosModel();
        $defeitosModel    = new defeitosModel();

        
        $this->filtros = array();


        if (!empty($this->input['item_id'])) {

            $item = explode('-', $this->input['item_id']);
            if($item['0'] == 'E'){
                $item_erp = $atendimentoModel->retornaItemErp($item['1']);
                if(!empty($item_erp)){
                    $this->data['filtrado']['cod_item']     = $item_erp['cod_item'];
                    $this->data['filtrado']['desc_tecnica'] = $item_erp['desc_tecnica'];
                }
            }else{
                $item_ass = $atendimentoModel->retornaItemAss($item['1']);
                if(!empty($item_ass)){
                    $this->data['filtrado']['cod_item']     = $item_ass['cod_item'];
                    $this->data['filtrado']['desc_tecnica'] = $item_ass['desc_tecnica'];
                }
            }


            $this->filtros['item_id'] = " = '".$this->input['item_id']."'";
           
        }

        if (!empty($this->input['defeito_principal_id'])) {
            $this->filtros['defeito_tecnico_id'] = " = '".$this->input['defeito_principal_id']."'";
            $defeito = $defeitosModel->getDefeitos('row', array('id = '=> $this->input['defeito_principal_id'] ));
            if(!empty($defeito)){
                $this->data['filtrado']['cod_defeito']       = $defeito['cod_defeito'];
                $this->data['filtrado']['descricao_defeito'] = $defeito['descricao'];
            }
        }


        if (!empty($this->input['data_recebimento']) && $this->input['data_recebimento'] != null) {
            $this->input['data_recebimento'] = trim($this->input['data_recebimento']);
            if (strpos($this->input['data_recebimento'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['data_recebimento']);
                $this->filtros['recebido_em'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $this->filtros['recebido_em'] = " = '" . $this->input['data_recebimento'] . "'";
            }
        }else{
            $this->input['data_recebimento'] = date('01/m/Y').'|'.date('t/m/Y');
            $this->filtros['recebido_em'] = " BETWEEN '" .date('01/m/Y') . "' AND '" . date('d/m/Y'). "'";
        }


        $registros = $atendimentoModel->getRelatorioDefeitos($this->filtros);
        $estrutura = array();
        $itens = false;
        $defeitos = false;

        if (!empty($registros)) {

            $defeitos = Core::unique_multidim_array($registros, 'defeito_tecnico_id');
            $itens = Core::unique_multidim_array($registros, 'item_id');
            if(!empty($defeitos) && !empty($itens)){

                foreach ($defeitos as $k => $defeito) {
                    $estrutura['defeitos'][$k]['defeito_tecnico_id'] = $defeito['defeito_tecnico_id'];
                    $estrutura['defeitos'][$k]['cod_defeito_tecnico'] = $defeito['cod_defeito_tecnico'];
                    $estrutura['defeitos'][$k]['desc_defeito_tecnico'] = $defeito['desc_defeito_tecnico'];

                    foreach ($itens as $y => $item) {
                        $estrutura['defeitos'][$k]['itens'][$y]['item_id'] = $item['item_id'];
                        $estrutura['defeitos'][$k]['itens'][$y]['cod_item'] = $item['cod_item'];
                  //      $estrutura['defeitos'][$k]['itens'][$y]['desc_tecnica'] = $item['desc_tecnica'];

                        $key =  Core::multidimensionalSearchArray($registros, array('item_id' => $item['item_id'], 'defeito_tecnico_id' => $defeito['defeito_tecnico_id']));

                        if($key !== false){
                            $estrutura['defeitos'][$k]['itens'][$y]['quantidade'] = $registros[$key]['quantidade'];
                        }else{
                            $estrutura['defeitos'][$k]['itens'][$y]['quantidade'] = 0;
                        }
                    }

                }

            }

        }

        $this->data['estrutura'] = $estrutura;
        $this->data['itens']     = $itens;
        $this->data['filtros']   = $this->input;


        return $this->data;
    }

    public function listagem()
    {
        $atendimentoModel = new atendimentoModel();
        $auxiliaresModel  = new Auxiliares();
        $motivosModel     = new motivosModel();
        $defeitosModel    = new defeitosModel();

        
        $this->filtros = array();

        if (!empty($this->input['item_id'])) {

            $item = explode('-', $this->input['item_id']);
            if($item['0'] == 'E'){
                $item_erp = $atendimentoModel->retornaItemErp($item['1']);
                if(!empty($item_erp)){
                    $this->data['filtrado']['cod_item']     = $item_erp['cod_item'];
                    $this->data['filtrado']['desc_tecnica'] = $item_erp['desc_tecnica'];
                }
            }else{
                $item_ass = $atendimentoModel->retornaItemAss($item['1']);
                if(!empty($item_ass)){
                    $this->data['filtrado']['cod_item']     = $item_ass['cod_item'];
                    $this->data['filtrado']['desc_tecnica'] = $item_ass['desc_tecnica'];
                }
            }


            $this->filtros['item_id'] = " = '".$this->input['item_id']."'";
           
        }

        if (!empty($this->input['defeito_principal_id'])) {
            $this->filtros['defeito_tecnico_id'] = " = '".$this->input['defeito_principal_id']."'";
            $defeito = $defeitosModel->getDefeitos('row', array('id = '=> $this->input['defeito_principal_id'] ));
            if(!empty($defeito)){
                $this->data['filtrado']['cod_defeito']       = $defeito['cod_defeito'];
                $this->data['filtrado']['descricao_defeito'] = $defeito['descricao'];
            }
        }

        if (!empty($this->input['data_recebimento']) && $this->input['data_recebimento'] != null) {
            $this->input['data_recebimento'] = trim($this->input['data_recebimento']);
            if (strpos($this->input['data_recebimento'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['data_recebimento']);
                $this->filtros['recebido_em'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $this->filtros['recebido_em'] = " = '" . $this->input['data_recebimento'] . "'";
            }
        }else{
            $this->input['data_recebimento'] = date('01/m/Y').'|'.date('t/m/Y');
            $this->filtros['recebido_em'] = " BETWEEN '" .date('01/m/Y') . "' AND '" . date('d/m/Y'). "'";
        }


        if (!empty($this->input['cliente_origem_id'])) {
            $this->filtros['cliente_origem_id'] = ' = '."'".$this->input['cliente_origem_id']."'";
            $cliente = $atendimentoModel->retornaClientes(false,$this->input['cliente_origem_id']);
            if(!empty($cliente)){
                $this->data['filtrado']['cod_cli_origem']       = $cliente['codigo'];
                $this->data['filtrado']['descricao_cli_origem'] = $cliente['descricao'];
            }
        }

        if (!empty($this->input['cliente_id'])) {
            $cli = explode('-', $this->input['cliente_id']);
            if($cli['0'] == 'I'){
                $this->filtros['cliente_assistencia_id'] = ' = '."'".$cli['1']."'";
                $cliente_ass = $atendimentoModel->retornaClienteAssistenciaSelect2(false,$cli['1']);
                if(!empty($cliente_ass)){
                    $this->data['filtrado']['cod_cli_assistencia']       = $cliente_ass['codigo'];
                    $this->data['filtrado']['descricao_cli_assistencia'] = $cliente_ass['descricao'];
                }
            }else{
                $this->filtros['cliente_assistencia_erp_id'] = ' = '."'".$cli['1']."'";
                $cliente_e = $atendimentoModel->retornaClientes(false,$cli['1']);
                if(!empty($cliente_e)){
                    $this->data['filtrado']['cod_cli_assistencia']       = $cliente_e['codigo'];
                    $this->data['filtrado']['descricao_cli_assistencia'] = $cliente_e['descricao'];
                }
            }
        }

        if (!empty($this->input['saida_produto'])) {
            $this->filtros['saida_produto'] = " = '".$this->input['saida_produto']."'";
        }

        if (!empty($this->input['material_recebido'])) {
            if($this->input['material_recebido'] == 'N'){
                $this->filtros['material_recebido'] = " IS NULL OR  v.material_recebido = '0'";
            }else{
                $this->filtros['material_recebido'] = " = '1'";
            }
        }

        $registros = $atendimentoModel->getRelatorioListagem($this->filtros);

        $this->data['registros'] = $registros;
        $this->data['filtros']   = $this->input;

        return $this->data;
    }
}