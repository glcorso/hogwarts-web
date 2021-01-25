<?php

namespace Lidere\Modules\Relatorios\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Services\ServicesInterface;
use Illuminate\Database\QueryException;
use Lidere\Modules\Relatorios\Models\AlteracaoEstrutura as modelRelatorioAlteracaoEstrutura;
use Lidere\Modules\Relatorios\Models\VAlteracaoEstrutura;
use Lidere\Modules\AssistenciaExterna\Models\Item;

/**
 * AlteracaoEstrutura
 *
 * @package Lidere\Modules
 * @subpackage Relatorios\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class AlteracaoEstrutura implements ServicesInterface
{
    /**
     * Filtros
     * @var array
     */
    private $filtros = array();

    /**
     * Sessão do usuário
     * @var array
     */
    private $usuario;

    /**
     * Sessão da empresa
     * @var array
     */
    private $empresa;

    /**
     * Dados do modulo acessado
     * @var array
     */
    private $modulo;

    /**
     * Dados do formulário
     * @var array
     */
    private $input;

    public function __construct(
        $usuario = array(),
        $empresa = array(),
        $modulo = array(),
        $data = array(),
        $input = array()
    )
    {
        $this->usuario = $usuario;
        $this->empresa = $empresa;
        $this->modulo = $modulo;
        $this->data = $data;
        $this->input = $input;

        $this->data['filtros'] = $this->input;
    }

    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
       
        $filtros = array();


        if (!empty($this->input['pai_id'])) {
            $filtros['pai_id'] = ' = '.$this->input['pai_id'];

            $item_pai = Item::whereId($this->input['pai_id'])->first();

            if(!empty($item_pai)){
                $item_pai = $item_pai->toArray();
                $this->data['item_filtrado_pai']['codigo'] = $item_pai['cod_item'];
                $this->data['item_filtrado_pai']['descricao'] = $item_pai['desc_tecnica'];
            }
        }

        if (!empty($this->input['filho_id'])) {
            $filtros['filho_id'] = ' = '.$this->input['filho_id'];
            $item_filho = Item::whereId($this->input['filho_id'])->first();

            if(!empty($item_filho)){
                $item_filho = $item_filho->toArray();
                $this->data['item_filtrado_filho']['codigo'] = $item_filho['cod_item'];
                $this->data['item_filtrado_filho']['descricao'] = $item_filho['desc_tecnica'];
            }
        }

        if ( !empty($this->input['acao'])) {
            $filtros['acao'] = ' = '."'".$this->input['acao']."'";
        }

        if ( isset($this->input['enviar']) &&  $this->input['enviar'] != null) {
            $filtros['enviar'] = ' = '."'".$this->input['enviar']."'";
        }

        if (!empty($this->input['data']) && $this->input['data'] != null) {
            $this->input['data'] = trim($this->input['data']);
            if (strpos($this->input['data'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['data']);
                 $filtros['TRUNC(data)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                 $filtros['TRUNC(data)'] = " = '" . $this->input['data'] . "'";
            }
        }

        if (!empty($this->input['usuario'])) {
            $filtros['UPPER(usuario)'] = " LIKE '%".strtoupper($this->input['usuario'])."%'";
        }

        if (!empty($this->input['mensagem'])) {
            $filtros['UPPER(mensagem)'] = " LIKE '%".strtoupper($this->input['mensagem'])."%'";
        }

        $filtros = function($query) use ($filtros) {
             if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        };


        //var_dump($filtros);die;

        try{

            /* Total sem paginação  */
            $total = VAlteracaoEstrutura::where($filtros)->count();
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

            /**
             * records = qtd de registros
             * offset = inicia no registro n
            */
            $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
            $offset = Config::read('APP_PERPAGE');



            $rows = VAlteracaoEstrutura::where($filtros)
                            ->skip($records)
                            ->take($offset)
                            ->orderBy('id','desc')
                            ->get();
                            
            $total_tela = count($rows);
        } catch (\Illuminate\Database\QueryException $e) {
            $rows = false;
            $total_tela = 0;
            $total = 0;
            $num_paginas = 1;
        }

        $this->data['filtros'] = $this->input;
        $this->data['resultado'] = $rows;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/relatorios/alteracao-estrutura/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

}