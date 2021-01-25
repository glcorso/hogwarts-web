<?php

namespace Lidere\Modules\AssistenciaExterna\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\AssistenciaExterna\Models\TPagamento;
use Lidere\Modules\AssistenciaExterna\Models\TPagamentoArquivo;
use Lidere\Modules\AssistenciaExterna\Models\VPagamentosAte;
use Lidere\Modules\AssistenciaExterna\Models\VPagamentosAteOrdem;
use Lidere\Modules\Services\ServicesInterface;
use Lidere\Models\Auxiliares;
use Lidere\Modules\Assistencia\Models\Atendimento as atendimentoModel;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoAutList as modelOrdemServicoAutList;

/**
 * PagamentoEfetuado
 *
 * @package Lidere\Modules
 * @subpackage PagamentoEfetuado\Services
 * @author Humberto Viezzer de Carvalho
 * @copyright 2020 Lidere Sistemas
 */
class PagamentoEfetuado implements ServicesInterface
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

    public $url = 'assistencia-externa/pagamento-efetuado';

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

        if (!empty($this->input['num_pagamento'])) {
            $filtros['num_pgto'] = ' = ' . $this->input['num_pagamento'];
        }

        if (!empty($this->input['criado_por'])) {
            $filtros['assistencia_id'] = ' = ' . "'" . $this->input['criado_por'] . "'";
        }

        if (!empty($this->input['criado_em']) && $this->input['criado_em'] != null) {
            $this->input['criado_em'] = trim($this->input['criado_em']);
            if (strpos($this->input['criado_em'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['criado_em']);
                $filtros['TRUNC(criado_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                $filtros['TRUNC(criado_em)'] = " = '" . $this->input['criado_em'] . "'";
            }
        }

        // Caso o usuário seja uma Assistência, sempre irá filtrar somente as dele.
        if ($this->usuario['tipo'] == 'ate') {
            $filtros['assistencia_id'] = ' = ' . $this->usuario['id'];
            $filtros['autorizado_em'] = 'IS NOT NULL';
        }

        $filtros = function ($query) use ($filtros) {
            if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna . " " . $valor . " ");
                }
            }
        };

        $total = VPagamentosAte::where($filtros)->count();

      //  if ($this->usuario['tipo'] == 'ate') {
            $total = VPagamentosAte::where('assistencia_id', $this->usuario['id'])->where($filtros)->count();
      //  } else {
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));
        //}

        /**
         * records = qtd de registros
         * offset = inicia no registro n
         */
        $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $offset = Config::read('APP_PERPAGE');

        $pagamentos = VPagamentosAte::skip($records)
            ->take($offset)
            ->where($filtros)
            ->get();

        if (!empty($pagamentos)) {
            foreach ($pagamentos as &$pagamento) {
                $pagamento['assistencia'] = $auxiliaresModel->usuarios('row', array('u.id' => ' = ' . $pagamento['assistencia_id']));
            }
        }

        // Caso seja uma assistência, não irá poder excluir.
        if ($this->usuario['tipo'] == 'ate') {
            $this->data['bloqueia_campos'] = 'S';
        } else {
            $this->data['bloqueia_campos'] = 'N';
        }

        $this->data['resultado'] = $pagamentos;
        $total_tela = $pagamentos->count();
        $this->data['usuario'] = $this->usuario;


        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/' . $this->url . '/pagina',
            !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null
        );

        return $this->data;
    }
    public function autorizar()
    {

        $auxiliaresModel = new Auxiliares();

        $id = $this->data['filtros']['pagamento_id'];

        $pagamentoQR = VPagamentosAte::find($id);

        if (!empty($pagamentoQR)) {
            $assistencia_externa = array();

            $dadosAte = $auxiliaresModel->usuarios('row', array('u.id' => ' = ' . $pagamentoQR['assistencia_id']));
            $usuarioAte = $auxiliaresModel->usuarios('row', array('u.id' => ' = ' . $pagamentoQR['assistencia_id']));

            if (!empty($dadosAte)) {
                $assistencia_externa['nome'] = $dadosAte['cliente_erp_descricao'];
                $assistencia_externa['email'] = $usuarioAte['email'];
            } else {
                $assistencia_externa['nome'] = 'Interno';
                $assistencia_externa['email'] = $usuarioAte['email'];
            }
        }

        if (!empty($id)) {

            $pagamento = TPagamento::editar($id, $this->usuario['id'], date('d/m/Y H:i:s'));

            if ($pagamento) {

                $retorno['retorno'] = true;
                $retorno['assistencia_externa'] = $assistencia_externa;
                $retorno['nroPagamento'] = $pagamentoQR->num_pgto;
                return $retorno;
            } else {
                return $retorno['retorno'] = false;
            }
        }
    }

    public function imprimirPagamento($id = null)
    {

        $auxiliaresModel = new Auxiliares();
        $atendimentoModel = new atendimentoModel();

        if (!empty($id)) {
            $pagamento = VPagamentosAte::find($id);
            if($this->usuario['tipo'] == 'ate'){
                if($pagamento['assistencia_id'] != $this->usuario['id']){
                    return false;
                }
            }

            if (!empty($pagamento)) {

                $pagamento['assistencia'] = $auxiliaresModel->usuarios('row', array('u.id' => ' = '.$pagamento['assistencia_id'] ));

                if(!empty($pagamento['assistencia'])){
                   $pagamento['dados_assistencia'] =  $atendimentoModel->getClienteERPId($pagamento['assistencia']['cliente_erp_id']);
                }

                $pagamento['texto_autorizacao'] = Core::parametro('assistencia_pagamentos_texto_impressao');
                $pagamento['ordens'] = VPagamentosAteOrdem::where('pagamento_id', $id)->orderBy('ordem_id')->get();

                if(!empty($pagamento['ordens'])){
                    foreach ($pagamento['ordens'] as &$ordem) {
                        $ordem['servicos'] =  modelOrdemServicoAutList::where('ordem_id', $ordem['ordem_id'])->get();
                    }
                }
            }

            // var_dump($pagamento);die;
        }
        $this->data['registro'] = !empty($pagamento) ? $pagamento : false;

        return $this->data;
    }

    public function anexarNFe($files = false)
    {

        $auxiliaresModel = new Auxiliares();
        $atendimentoModel = new atendimentoModel();

        $pagamento = VPagamentosAte::find($this->input['pagamento_id']);

       
        if (!empty($files)) {

            $file_ary = array();
            $file_count = count($files['files']['name']);
            $file_keys = array_keys($files['files']);

            for ($i=0; $i<$file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $files['files'][$key][$i];
                }
            }

            foreach ($file_ary as $v => $file) {

                $arquivo['pagamento_id']  = $this->input['pagamento_id'];
                $arquivo['tipo']  = $file['type'];
                $arquivo['arquivo']       = time() . "-" . $this->input['pagamento_id'] . "-" . $file['name'];
                move_uploaded_file($file['tmp_name'], APP_ROOT . 'public' . DS . 'arquivos' . DS . 'notas_ate' . DS . $arquivo['arquivo']);
                TPagamentoArquivo::criar($arquivo);

                $mensagens['retorno']['anexos'][$v]['path'] = APP_ROOT . 'public' . DS . 'arquivos' . DS . 'notas_ate' . DS . $arquivo['arquivo'];
                $mensagens['retorno']['anexos'][$v]['name'] = $arquivo['arquivo'];
                $mensagens['retorno']['anexos'][$v]['cid']  = $arquivo['arquivo'];

            }

            $assistencia = $auxiliaresModel->usuarios('row', array('u.id' => ' = '.$pagamento['assistencia_id'] ));

            if(!empty($assistencia)){
               $assistencia['dados_assistencia'] =  $atendimentoModel->getClienteERPId($assistencia['cliente_erp_id']);
               $mensagens['retorno']['assistencia_razao_social'] =  $assistencia['dados_assistencia']['nome']; 
            }

            $mensagens['success']['pagamentoEfetuado'] = 'O Anexo do pagamento ' . $pagamento->num_pgto . ' foi salvo com sucesso';
            $mensagens['retorno']['nroPagamento'] = $pagamento->num_pgto;

        } else {
            $error = 'Não foi possível salvar o Anexo.';
            $mensagens['error']['pagamentoEfetuado'] = $error;
        }

        return $mensagens;
    }


    public function retornaAnexos($pagamento_id = "")
    {

        $id = $this->data['filtros']['pagamento_id'];

        $arquivos = TPagamentoArquivo::where('pagamento_id', $id)->get()->toArray();

        if (!empty($arquivos)) {
            foreach ($arquivos as &$arq) {

                $arq['link'] = base64_encode(microtime() . '!' . $arq['id'] . '!' . $arq['pagamento_id'] . '!' . $arq['arquivo']);
            }
        }

        $this->data['arquivos'] = $arquivos;

        return $this->data;
    }

    public function form($id = null)
    {
        // $row = servicosModel::find($id);


        // $this->data['registro'] = $row;
        // $this->data['categorias'] = Categoria::where('sit','1')->get();

        // return $this->data;
    }

    public function add()
    {
        // unset($this->input['voltar']);

        // $servico = servicosModel::criar($this->input);

        // return $servico;
    }

    public function edit()
    {
        // unset($this->input['_METHOD']);

        // $row = servicosModel::find($this->input['id']);
        // $updated = $row->update($this->input);
        // return $updated;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = TPagamento::whereId($this->input['id'])->delete();
        return $deleted;
    }
}
