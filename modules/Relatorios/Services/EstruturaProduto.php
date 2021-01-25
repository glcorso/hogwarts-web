<?php

namespace Lidere\Modules\Relatorios\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Services\ServicesInterface;
use Illuminate\Database\QueryException;
use Lidere\Modules\Relatorios\Models\EstruturaProduto as modelRelatorioEstruturaProduto;

/**
 * EstruturaProduto
 *
 * @package Lidere\Modules
 * @subpackage Relatorios\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class EstruturaProduto implements ServicesInterface
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
    public function index()
    {
        $modelRelatorioEstruturaProduto = new modelRelatorioEstruturaProduto();
        $filtros = array();
        $estrutura = false;
        $item_filtrado =  false;
        $custo_item_pai = false;

        if(!empty($this->input['itempr_id'])){

            $custo_item_pai =  $modelRelatorioEstruturaProduto->retornaCustoItem($this->input['itempr_id']);
            $custo = !empty($custo_item_pai['custo']) ? $custo_item_pai['custo'] : 0;
            $estrutura = $modelRelatorioEstruturaProduto->retornaEstruturaItem($this->input['itempr_id'],$this->input['mascara_id'],$custo);
            $item_filtrado =  $modelRelatorioEstruturaProduto->retornaItensPn(false,$this->input['itempr_id']);

        }

       // var_dump($estrutura);die;
        $this->data['filtros'] = $this->input;
        $this->data['estrutura'] = $estrutura;
        $this->data['item_filtrado'] = $item_filtrado;
        $this->data['custo_item_pai'] = $custo_item_pai;

        return $this->data;
    }

}