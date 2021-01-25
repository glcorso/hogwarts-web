<?php

namespace Lidere\Modules\AssistenciaExterna\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServico as modelOrdemServico;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoStatus as modelOrdemServicoStatus;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoList as modelOrdemServicoList;
use Lidere\Modules\AssistenciaExterna\Models\Categoria as modelCategoria;
use Lidere\Modules\AssistenciaExterna\Models\Servicos as modelServicos;
use Lidere\Modules\AssistenciaExterna\Models\ClienteAssistencia as modelClienteAssistencia;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoAut as modelOrdemServicoAut;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoCatAut as modelOrdemServicoCatAut;
use Lidere\Modules\AssistenciaExterna\Models\ValorCategoria as modelValorCategoria;
use Lidere\Modules\AssistenciaExterna\Models\ValorServico as modelValorServico;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoAutList as modelOrdemServicoAutList;
use Lidere\Modules\Assistencia\Models\Atendimento as atendimentoModel;
use Lidere\Models\Usuario;
use Lidere\Modules\Services\Services;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoArquivos as modelOrdemServicoArquivos;
use Lidere\Models\Auxiliares;
use Illuminate\Database\QueryException;


/**
 * EncerramentoOrdens
 *
 * @package Lidere\Modules
 * @subpackage EncerramentoOrdens\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class EncerramentoOrdens extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $atendimentoModel = new atendimentoModel();
        $auxiliaresModel = new Auxiliares();

        $this->filtros = array();

        $usuarios = $auxiliaresModel->usuarios('result');

        if($_SESSION['usuario']['id'] == 1 or $_SESSION['usuario']['tipo'] == 'admin'){
            $setor_usuario = 'admin';
        }else{
            $usuario = Usuario::with('SetorUsuario')->whereId($_SESSION['usuario']['id'])->first();

            if($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_pos_vendas')){
                $setor_usuario = 'pos_vendas';
            }elseif($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_coordenador')){
                $setor_usuario = 'coordenador';
            }else{
                $setor_usuario = 'vendedor';
            }
        }

        if (!empty($this->input['num_ordem'])) {
            $this->filtros['num_ordem'] = ' = '.$this->input['num_ordem'];
        }

        if (!empty($this->input['criado_em']) && $this->input['criado_em'] != null) {
            $this->input['criado_em'] = trim($this->input['criado_em']);
            if (strpos($this->input['criado_em'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['criado_em']);
                $this->filtros['TRUNC(criado_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $this->filtros['TRUNC(criado_em)'] = " = '" . $this->input['criado_em'] . "'";
            }
        }

        if (!empty($this->input['cliente_id'])) {
            $cli = explode('-', $this->input['cliente_id']);
            if($cli['0'] == 'I'){
                $this->filtros['cliente_assistencia_id'] = ' = '."'".$cli['1']."'";
                $cliente_ass = $atendimentoModel->retornaClienteAssistenciaSelect2(false,$cli['1']);
                if(!empty($cliente_ass)){
                    $this->data['filtros']['cod_cli_assistencia']       = $cliente_ass['codigo'];
                    $this->data['filtros']['descricao_cli_assistencia'] = $cliente_ass['descricao'];
                }
            }else{
                $this->filtros['cliente_assistencia_erp_id'] = ' = '."'".$cli['1']."'";
                $cliente_e = $atendimentoModel->retornaClientes(false,$cli['1']);
                if(!empty($cliente_e)){
                    $this->data['filtros']['cod_cli_assistencia']       = $cliente_e['codigo'];
                    $this->data['filtros']['descricao_cli_assistencia'] = $cliente_e['descricao'];
                }
            }
        }

        if (!empty($this->input['criado_por'])) {

            $this->filtros['criado_por'] = ' = ' . $this->input['criado_por'];
            $assistencia = Usuario::where('id', '=', $this->input['criado_por'])->first();
            if (!empty($assistencia)) {
                $this->data['filtros']['assistencia']       = $assistencia['usuario'];
                $this->data['filtros']['assistencia_nome'] = $assistencia['nome'];
            }
        }

        $this->filtros['status_id'] = " = 8";

        $filtros = $this->filtros;
        $filtros = function($query) use ($filtros) {
             if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        };

       // var_dump(Core::sequencia('nr_seq_ordem_serv'));die;

        try{

            /* Total sem paginação  */
            $total = modelOrdemServicoList::where($filtros)->count();
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

            /**
             * records = qtd de registros
             * offset = inicia no registro n
            */
            $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
            $offset = Config::read('APP_PERPAGE');



            $rows = modelOrdemServicoList::where($filtros)
                            ->skip($records)
                            ->take($offset)
                            ->get();
            $total_tela = count($rows);

            if (!empty($rows)) {
                foreach ($rows as &$row) {
                    $row['permite_excluir'] = true;
                    $row['assistencia'] = $auxiliaresModel->usuarios('row', array('u.id' => ' = '.$row['criado_por'] ));
                    $row['valor'] = modelOrdemServicoAutList::retornaValorPorOrdem($row['id']);
                    $row['valor'] = Core::BRL($row['valor']);
                }
            }


        } catch (\Illuminate\Database\QueryException $e) {

          //  var_dump($e->getMessage());die;
            $rows = false;
            $total_tela = 0;
            $total = 0;
            $num_paginas = 1;
        }

        //echo "<pre>";
        //var_dump($rows);die;

        $this->data['setor'] = $setor_usuario;
        $this->data['resultado'] = $rows;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/comercial/categorias-servico/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }


    public function add($ids)
    {
        try {
            // Percorre todos os ids informados na tela
            foreach ($ids as $id => $value) {
                $st['status_id'] = 10; // Finalizado
                $st['ordem_id'] = $value;
                $status = modelOrdemServicoStatus::criar($st);
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }


    }

}
