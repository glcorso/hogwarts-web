<?php

namespace Lidere\Modules\AssistenciaExterna\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Usuario;
use Lidere\Modules\AssistenciaExterna\Models\TPagamento;
use Lidere\Modules\AssistenciaExterna\Models\TPagamentoOrdem;
use Lidere\Modules\AssistenciaExterna\Models\VOrdemServicoPagamento;
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
class PagamentoOrdem implements ServicesInterface
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
    public function list($pagina = 1)
    {

        $filtros = array();

        $auxiliaresModel = new Auxiliares();

        if (!empty($this->input['num_ordem'])) {
            $this->filtros['ordem'] = ' = ' . $this->input['num_ordem'];
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

        if (!empty($this->input['criado_por']) || ($_SESSION['usuario']['tipo'] == 'ate')) {

            if (!empty($this->input['criado_por'])) {
                $filtro_criado_por = $this->input['criado_por'];
            } elseif ($_SESSION['usuario']['tipo'] == 'ate') {
                $filtro_criado_por = $_SESSION['usuario']['id'];
            }

            $this->filtros['criado_por'] = ' = ' . $filtro_criado_por;
            $assistencia = Usuario::where('id', '=', $filtro_criado_por)->first();
            if (!empty($assistencia)) {
                $this->data['filtros']['assistencia']       = $assistencia['usuario'];
                $this->data['filtros']['assistencia_nome'] = $assistencia['nome'];
            }
        }

        // So irá mostrar dados quando o usuário informar a Assitencia e um período.
        if (empty($this->input['criado_por']) || empty($this->input['criado_em'])) {
            $this->filtros['1'] = " =  2";
        }

        // Somnente irá mostrar as Ordens de Serviço que estão com o Status Concluído.
        $this->filtros['status_id'] = " =  8";

        $filtros = $this->filtros;
        $filtros = function ($query) use ($filtros) {
            if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna . " " . $valor);
                }
            }
        };

        try {

            /* Total sem paginação  */
            $total = VOrdemServicoPagamento::where($filtros)->count();
            $num_paginas = ceil($total / 200);

            /**
             * records = qtd de registros
             * offset = inicia no registro n
             */
            $records = ($pagina * 200) - 200;
            $offset = 200;



            $pagamentos = VOrdemServicoPagamento::where($filtros)
                ->skip($records)
                ->take($offset)
                ->get();
            $total_tela = count($pagamentos);

            if (!empty($pagamentos)) {
                foreach ($pagamentos as &$row) {
                    $row['permite_excluir'] = true;
                    $row['assistencia'] = $auxiliaresModel->usuarios('row', array('u.id' => ' = ' . $row['criado_por']));
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            $pagamentos = false;
            $total_tela = 0;
            $total = 0;
            $num_paginas = 1;
        }

        $this->data['resultado'] = $pagamentos;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/assistencia-externa/pagamento-ordem/pagina/',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    /**
     * Gera um pagamento
     *
     * @return $ordem
     */

    public function geraPagamento()
    {

        $ids = $this->data['filtros']['ids'];

        if (!empty($ids)) {

            $pagamento = TPagamento::criar($this->input, $this->usuario['id']);

            if (!empty($pagamento)) {
                foreach ($ids as $id) {

                    $ordem = VOrdemServicoPagamento::find($id);

                    if (!empty($ordem)) {
                        $pagamentoOrdem = TPagamentoOrdem::criar($id, $pagamento->id, $ordem->valor);
                    }
                }
            } else {
                return false;
            }
        }

        return true;
    }
}
