<?php

namespace Lidere\Modules\AssistenciaExterna\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Usuario;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoAutList as modelOrdemServicoAutList;
use Lidere\Modules\Services\ServicesInterface;
use Illuminate\Database\QueryException;
use Lidere\Models\Auxiliares;

/**
 * PagamentoOrdem
 *
 * @package Lidere\Modules
 * @subpackage PagamentoOrdem\Services
 * @author Humberto Viezzer de Carvalho
 * @copyright 2020 Lidere Sistemas
 */
class Relatorios implements ServicesInterface
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
    ) {
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


    public function ocorrenciasItem()
    {

    	$this->filtros = array();

        $modelOrdemServicoAutList = new modelOrdemServicoAutList();

	   	$ordem_id     = false;
	   	$periodo      = false;
	   	$servico_id   = false;
	   	$categoria_id = false;

    	if (!empty($this->input['ordem_id'])) {
    		$ordem_id = $this->input['ordem_id'];
    	}

    	if (!empty($this->input['periodo'])) {
    		$periodo = $this->input['periodo'];
    	}else{
            $periodo = date('01/m/Y').'|'.date('t/m/Y');
        }

    	if (!empty($this->input['servico_id'])) {
    		$servico_id = $this->input['servico_id'];
    	}

    	if (!empty($this->input['categoria_id'])) {
    		$categoria_id = $this->input['categoria_id'];
    	}

    	$registros = $modelOrdemServicoAutList->retornaOcorrenciasPorItem($ordem_id, 
    					                                                 $periodo, 
    					                                                 $servico_id, 
    					                                                 $categoria_id);


        if (!empty($registros)) {

            $defeitos = Core::unique_multidim_array($registros, 'servico_id');
            $itens = Core::unique_multidim_array($registros, 'agrupador');

            if(!empty($defeitos) && !empty($itens)){

               // echo "<pre>";
               // var_dump($defeitos);die;

                foreach ($defeitos as $k => $defeito) {
                    $estrutura['defeitos'][$k]['servico_id'] = $defeito['servico_id'];
                    $estrutura['defeitos'][$k]['cod_serv'] = $defeito['cod_serv'];
                    $estrutura['defeitos'][$k]['desc_serv'] = $defeito['desc_serv'];

                    foreach ($itens as $y => $item) {
                        $estrutura['defeitos'][$k]['itens'][$y]['item_id'] = $item['agrupador'];
                        $estrutura['defeitos'][$k]['itens'][$y]['cod_item'] = $item['agrupador'];
                  //      $estrutura['defeitos'][$k]['itens'][$y]['desc_tecnica'] = $item['desc_tecnica'];

                        $key =  Core::multidimensionalSearchArray($registros, array('agrupador' => $item['agrupador'], 'servico_id' => $defeito['servico_id']));

                        if($key !== false){
                            $estrutura['defeitos'][$k]['itens'][$y]['quantidade'] = $registros[$key]['quantidade'];
                        }else{
                            $estrutura['defeitos'][$k]['itens'][$y]['quantidade'] = 0;
                        }
                    }

                }
            }
        }

  //      echo "<pre>";
//        var_export($estrutura);die;


        
        $this->data['estrutura'] = !empty($estrutura) ? $estrutura : false;
        $this->data['itens']     = !empty($itens) ? $itens : false;
        $this->data['filtros']   = $this->input;

        return $this->data;

    }

}