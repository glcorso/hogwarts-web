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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoPrecoItemList;

/**
 * OrdemServico
 *
 * @package Lidere\Modules
 * @subpackage OrdemServico\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class OrdemServico extends Services
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

        if (!empty($this->input['criado_por']) || ( $_SESSION['usuario']['tipo'] == 'ate')) {

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

        $this->data['tipo_usuario'] = $_SESSION['usuario']['tipo'];
        $this->data['setor'] = $setor_usuario;
        $this->data['resultado'] = $rows;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/assistencia-externa/ordem-servico/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    public function form($id = null)
    {

        $usuarios_permitidos = explode(',',Core::parametro('assistencia_externa_usuarios'));

        $this->data['libera_aprovacao'] = in_array($_SESSION['usuario']['id'], $usuarios_permitidos) ? true : false;

        $row = modelOrdemServicoList::find($id);

       // echo "<pre>";
       // var_dump($row);die;

        if(!empty($id)){
            
            $item_id = $row->item_id;

            $categorias = modelCategoria::where('sit','1')->whereExists(function ($query) use ( $item_id ) {
                                                                $query->select(DB::raw(1))
                                                                      ->from('vsdi_assist_itens_preco')
                                                                      ->whereRaw('vsdi_assist_itens_preco.categoria_id = tsdi_assistencia_categorias.id')
                                                                      ->whereRaw('vsdi_assist_itens_preco.item_id = '.$item_id);
                                                            })->orderBy('cod_cat')->get();

            if(!empty($categorias)){
                foreach ($categorias as &$categoria) {
                    $categoria['servicos'] = modelServicos::where('sit','1')->
                                                            where('categoria_id',$categoria->id)
                                                            ->whereExists(function ($query) use ( $item_id ) {
                                                                $query->select(DB::raw(1))
                                                                      ->from('vsdi_assist_itens_preco')
                                                                      ->whereRaw('vsdi_assist_itens_preco.servico_id = tsdi_assistencia_servicos.id')
                                                                      ->whereRaw('vsdi_assist_itens_preco.item_id = '.$item_id);
                                                            })
                                                            ->orderBy('cod_serv')
                                                            ->get();
                    if(!empty($categoria['servicos']) && !empty($id)){
                        foreach ($categoria['servicos'] as &$servico) {
                            $serv = modelOrdemServicoAutList::where('servico_id',$servico->id)
                                                                ->where('ordem_id', $id)->first();
                            $servico['checked'] = !empty($serv) ? true : false;
                        }
                    }
                }
            }

            $this->data['servicos_solicitados'] = modelOrdemServicoAutList::retornaValorLinha($id);

            $this->data['arquivos_at'] = modelOrdemServicoArquivos::where('ordem_id', $id)
                                                                    ->where('tipo_anexo', 'AT')
                                                                    ->get();

            $this->data['arquivos_cl'] = modelOrdemServicoArquivos::where('ordem_id', $id)
                                                                    ->where('tipo_anexo', 'CL')
                                                                    ->get();

            if(!empty($this->data['arquivos_at'])){
                foreach ($this->data['arquivos_at'] as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['ordem_id'].'!'.$arq['arquivo']);
                }
            }

            if(!empty($this->data['arquivos_cl'])){
                foreach ($this->data['arquivos_cl'] as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['ordem_id'].'!'.$arq['arquivo']);
                }
            }

            if(!empty($this->data['servicos_solicitados'])) {
                $this->data['total_servicos'] = modelOrdemServicoAutList::retornaValorPorOrdem($id);
            }

         //   echo "<pre>";
          //  var_dump($this->data['servicos_solicitados']);die;


            if($_SESSION['usuario']['tipo'] == 'ate' ){
                if( $row['criado_por'] != $_SESSION['usuario']['id']) {
                    return false;
                }
            }


            $this->data['categorias'] = $categorias;

        }

        $this->data['registro'] = $row;

        //echo "<pre>";
        //var_dump($row);die;

        return $this->data;
    }

    public function add($files = false)
    {

        if(empty($this->input['cliente_assistencia_id']) && empty($this->input['est_id'])){
            $this->input['telefone']   = $this->input['telefone_celular'];
            $this->input['criado_por'] = $_SESSION['usuario']['id'];
            $this->input['criado_em']  = date('d/m/Y H:i:s');
            $this->input['cpf_cnpj']   = preg_replace('/[^0-9]/', '', $this->input['cpf_cnpj']);
            unset($this->input['telefone_celular']);
            $cliente = modelClienteAssistencia::criar($this->input);
            $this->input['cliente_assistencia_id'] = $cliente->id;
        }else{
            if(!empty($this->input['cliente_assistencia_id'])){
                $rowCli = modelClienteAssistencia::find($this->input['cliente_assistencia_id']);
                $inputCli['nome']     = $this->input['nome'];
                $inputCli['telefone'] = $this->input['telefone_celular'];
                $inputCli['e_mail']   = $this->input['e_mail'];
                $inputCli['endereco'] = $this->input['endereco'];
                $inputCli['nro'] = $this->input['nro'];
                $inputCli['complemento'] = $this->input['complemento'];
                $inputCli['cidade'] = $this->input['cidade'];
                $inputCli['bairro'] = $this->input['bairro'];
                $inputCli['uf'] = $this->input['uf'];
                $inputCli['cep'] = str_replace('-', '', $this->input['cep']) ;
                $inputCli['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $this->input['cpf_cnpj']);
                $inputCli['alterado_por'] = $_SESSION['usuario']['id'];
                $inputCli['alterado_em'] = date('d/m/Y H:i:s');
                $updated = $rowCli->update($inputCli);
            }
        }

        $item = explode('-', $this->input['item_id']);

        $this->input['item_id'] = $item['1'];
        $this->input['tp_item'] = $item['0'];
        $this->input['num_ordem'] = Core::sequencia('nr_seq_ordem_serv');

        $ordem = modelOrdemServico::criar($this->input);

        if(!empty($ordem)){
            $st['status_id'] = 1; //Aguardando Análise
            $st['ordem_id'] = $ordem->id;
            $status = modelOrdemServicoStatus::criar($st);
        }

        return $ordem;
    }

    public function edit()
    {
        unset($this->input['_METHOD']);

        $row = modelOrdemServico::find($this->input['id']);
        $updated = $row->update($this->input);

        return $updated;
    }



    public function editServicos($files = false)
    {
        unset($this->input['_METHOD']);

        if(!empty($this->input['categoria'])){
            $categorias = $this->input['categoria'];
            unset($this->input['categoria']);
        }


        $row = modelOrdemServico::find($this->input['id']);
        $updated = $row->update($this->input);

        if($updated){
            if(!empty($categorias)){
                // percorre as categorias marcadas e busca o valor
                foreach ($categorias as $k => $categoria) {
                    $input_categoria['categoria_id'] = $k;
                    $input_categoria['valor'] = $row->garantia == 'S' ? $this->retornaValorCategoria($k) : 0 ;
                    $input_categoria['ordem_id'] = $this->input['id'];
                    $autCat = modelOrdemServicoCatAut::criar($input_categoria);
                    if(!empty($autCat)){
                        foreach ($categoria['servico'] as $servico) {
                            $input['servico_id'] = $servico;
                            $input['aut_cat_id'] = $autCat->id;
                            $input['valor']      = $row->garantia == 'S' ? $this->retornaValorServico($servico,$row->item_id) : 0;
                            modelOrdemServicoAut::criar($input);
                        }
                    }
                }

                if(!empty($this->input['id'])){
                    $st['status_id'] = 2; //Aguardando Aprovacao
                    $st['ordem_id'] = $this->input['id'];
                    $status = modelOrdemServicoStatus::criar($st);

                    //// SE GARANTIA = NÃO - APROVA DIRETO
                    if($row->garantia == 'N'){
                        $st2['status_id'] = 5; //Aprovado
                        $st2['ordem_id'] = $this->input['id'];
                        $status2 = modelOrdemServicoStatus::criar($st2);

                        $input2['obs_aprovacao']  = 'APROVADO AUTOMATICAMENTE - FORA DA GARANTIA RESFRI AR';
                        $input2['valor_aprovado'] = 0;
                        $row = modelOrdemServico::find($this->input['id']);
                        $updated2 = $row->update($input2);
                    }

                    if(!empty($files)){

                        $file_ary = array();
                        $file_count = count($files['files']['name']);
                        $file_keys = array_keys($files['files']);

                        for ($i=0; $i<$file_count; $i++) {
                            foreach ($file_keys as $key) {
                                $file_ary[$i][$key] = $files['files'][$key][$i];
                            }
                        }

                        foreach ($file_ary as $file) {
                            $k = 0;
                            if ( $file['size'] > 0 && $file['error'] === 0 ) {
                                $ins_file['ordem_id']  = $this->input['id'];
                                $ins_file['tipo']          = $file['type'];
                                $ins_file['tipo_anexo']    = 'AT';
                                $ins_file['arquivo']       = $k.$this->input['id']."-".$file['name'];
                                move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica_ex'.DS.$k.$this->input['id']."-".$file['name']);
                                modelOrdemServicoArquivos::criar($ins_file);
                                $k++;
                            }

                        }
                    }
                }
            }
        }
        return $updated;
    }

    public function aprovarServicos()
    {
        unset($this->input['_METHOD']);

        if(!empty($this->input['categoria'])){
            $categorias = $this->input['categoria'];
            unset($this->input['categoria']);
        }

        $input['obs_aprovacao'] = $this->input['obs_aprovacao'];
        $input['valor_aprovado'] = modelOrdemServicoAutList::retornaValorPorOrdem($this->input['id']);
        $row = modelOrdemServico::find($this->input['id']);
        $updated = $row->update($input);

        if($updated){
            if(!empty($this->input['id'])){
                $st['status_id'] = 5; //Aprovado
                $st['ordem_id'] = $this->input['id'];
                $status = modelOrdemServicoStatus::criar($st);
            }

        }
        return $updated;
    }

    public function reprovarServicos($ordem_id)
    {

        $st['status_id'] = 4; //Reprovado
        $st['ordem_id'] = $ordem_id;
        $status = modelOrdemServicoStatus::criar($st);

        return $status;
    }

    public function concluirServicos($files = false)
    {
        unset($this->input['_METHOD']);

        if(!empty($this->input['categoria'])){
            $categorias = $this->input['categoria'];
            unset($this->input['categoria']);
        }

        $input['obs_conclusao'] = $this->input['obs_conclusao'];
        $row = modelOrdemServico::find($this->input['id']);
        $updated = $row->update($input);

        if($updated){
            if(!empty($this->input['id'])){
                $st['status_id'] = 8; //Concluído
                $st['ordem_id'] = $this->input['id'];
                $status = modelOrdemServicoStatus::criar($st);

                if(!empty($files)){

                    $file_ary = array();
                    $file_count = count($files['files']['name']);
                    $file_keys = array_keys($files['files']);

                    for ($i=0; $i<$file_count; $i++) {
                        foreach ($file_keys as $key) {
                            $file_ary[$i][$key] = $files['files'][$key][$i];
                        }
                    }

                    foreach ($file_ary as $file) {
                        $k = 0;
                        if ( $file['size'] > 0 && $file['error'] === 0 ) {
                            $ins_file['ordem_id']  = $this->input['id'];
                            $ins_file['tipo']          = $file['type'];
                            $ins_file['tipo_anexo']    = 'CL';
                            $ins_file['arquivo']       = $k.$this->input['id']."-".$file['name'];
                            move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica_ex'.DS.$k.$this->input['id']."-".$file['name']);
                            modelOrdemServicoArquivos::criar($ins_file);
                            $k++;
                        }

                    }
                }

            }

        }
        return $updated;
    }



    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = modelOrdemServico::whereId($this->input['id'])->delete();
        return $deleted;
    }

    private function retornaValorCategoria($categoria_id){

        $codigo = Core::parametro('assistencia_externa_lista_valores_cat_servicos');

        //var_dump($categoria_id);die;
        $registro = modelValorCategoria::where('cod_lista',$codigo)
                                        ->with(['valorCategoriaPrecos' => function($query) use ($categoria_id) {
                                            $query->where('categoria_id', $categoria_id);
                                        }])->first();

        $registro = !empty($registro) ? $registro->valorCategoriaPrecos->toArray() : 0;
        $valor = !empty($registro['0']['preco']) ? $registro['0']['preco'] : 0;

        return $valor;
    }


    private function retornaValorServico($servico_id, $item_id){

        $codigo = Core::parametro('assistencia_externa_lista_valores_servicos');
        $registro = OrdemServicoPrecoItemList::where('cod_lista',$codigo)->where('servico_id',$servico_id)->where('item_id',$item_id)->first();

        $valor = !empty($registro->preco) ? $registro->preco : 0;

        return $valor;
    }

    public function imprimirAtendimento($id = null)
    {

        $usuarios_permitidos = explode(',',Core::parametro('assistencia_externa_usuarios'));

        $this->data['libera_aprovacao'] = in_array($_SESSION['usuario']['id'], $usuarios_permitidos) ? true : false;

        $this->data['texto_entrega_usuario'] = Core::parametro('assistencia_externa_texto_entrega_usuario');
        $this->data['texto_devolucao_usuario'] = Core::parametro('assistencia_externa_texto_devolucao');

        $auxiliaresModel = new Auxiliares();
        $atendimentoModel = new atendimentoModel();

        $row = modelOrdemServicoList::find($id);

        $categorias = modelCategoria::where('sit','1')->orderBy('cod_cat')->get();

        if(!empty($categorias)){
            foreach ($categorias as &$categoria) {
                $categoria['servicos'] = modelServicos::where('sit','1')->
                                                        where('categoria_id',$categoria->id)
                                                        ->orderBy('cod_serv')
                                                        ->get();
                if(!empty($categoria['servicos']) && !empty($id)){
                    foreach ($categoria['servicos'] as &$servico) {
                        $serv = modelOrdemServicoAutList::where('servico_id',$servico->id)
                                                            ->where('ordem_id', $id)->first();

                        $servico['checked'] = !empty($serv) ? true : false;
                    }
                }
            }
        }

        if(!empty($id)){


            $row['assistencia'] = $auxiliaresModel->usuarios('row', array('u.id' => ' = '.$row['criado_por'] ));

            if(!empty($row['assistencia'])){
               $row['dados_assistencia'] =  $atendimentoModel->getClienteERPId($row['assistencia']['cliente_erp_id']);
            }

            $this->data['servicos_solicitados'] = modelOrdemServicoAutList::retornaValorLinha($id);


            $this->data['arquivos_at'] = modelOrdemServicoArquivos::where('ordem_id', $id)
                                                                    ->where('tipo_anexo', 'AT')
                                                                    ->get();

            $this->data['arquivos_cl'] = modelOrdemServicoArquivos::where('ordem_id', $id)
                                                                    ->where('tipo_anexo', 'CL')
                                                                    ->get();

            $this->data['arquivo_ata'] = modelOrdemServicoArquivos::where('ordem_id', $id)
                                                                    ->where('tipo_anexo', 'ATA')
                                                                    ->first();
            $this->data['arquivo_cla'] = modelOrdemServicoArquivos::where('ordem_id', $id)
                                                                    ->where('tipo_anexo', 'CLA')
                                                                    ->first();

            if(!empty($this->data['arquivos_at'])){
                foreach ($this->data['arquivos_at'] as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['ordem_id'].'!'.$arq['arquivo']);
                }
            }

            if(!empty($this->data['arquivos_cl'])){
                foreach ($this->data['arquivos_cl'] as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['ordem_id'].'!'.$arq['arquivo']);
                }
            }

            if(!empty($this->data['servicos_solicitados'])) {
                $this->data['total_servicos'] = modelOrdemServicoAutList::retornaValorPorOrdem($id);
            }


            if($_SESSION['usuario']['tipo'] == 'ate' ){
                if( $row['criado_por'] != $_SESSION['usuario']['id']) {
                    return false;
                }
            }

        }

        $this->data['categorias'] = $categorias;
        $this->data['registro'] = $row;
        $this->data['usuario'] = $_SESSION['usuario'];


        //echo "<pre>";
        //var_dump($row);die;

        return $this->data;
    }

    public function select()
    {
        $auxiliaresModel  = new Auxiliares();


        $ordem_servico = modelOrdemServico::where('id', '=', $this->input['id'])->first();

        $usuario = $auxiliaresModel->usuarios('row', array('u.id' => ' = ' . $ordem_servico['criado_por']));
        $this->data['ordem_servico'] = $ordem_servico;
        $this->data['usuario'] = $usuario;

        return $this->data;
    }
}
