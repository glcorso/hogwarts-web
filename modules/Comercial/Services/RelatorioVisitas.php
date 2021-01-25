<?php

namespace Lidere\Modules\Comercial\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Comercial\Models\Concorrente;
use Lidere\Modules\Comercial\Models\CategoriaConcorrente;
use Lidere\Modules\Services\ServicesInterface;
use Lidere\Modules\Comercial\Models\Estabelecimento;
use Lidere\Modules\Comercial\Models\UF;
use Illuminate\Database\QueryException;
use Lidere\Models\Auxiliares;
use Lidere\Models\Usuario;
use Lidere\Modules\Comercial\Models\RelatorioVisitas as modelRelatorioVisita;
use Lidere\Modules\Comercial\Models\RelatorioVisitaStatus as modelRelatorioVisitaStatus;
use Lidere\Modules\Comercial\Models\RelatorioVisitasList as modelRelatorioVisitaList;
use Lidere\Modules\Comercial\Models\RelatorioVisitaConcorrentes as modelRelatorioVisitaConcorrente;
use Lidere\Modules\Comercial\Models\RelatorioVisitaArquivos as modelRelatorioVisitaArquivos;
use Lidere\Modules\Comercial\Models\Prospect;
use Lidere\Modules\Comercial\Models\Status;
use Lidere\Modules\Comercial\Models\ClienteErp;

/**
 * RelatorioVisitas
 *
 * @package Lidere\Modules
 * @subpackage RelatorioVisitas\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class RelatorioVisitas implements ServicesInterface
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
        $auxiliaresModel  = new Auxiliares();
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

        $filtros['tipo_rel'] = ' = '."'R'";

        if (!empty($this->input['id'])) {
            $filtros['id'] = ' = '.$this->input['id'];
        }

        if( $setor_usuario != 'vendedor') {
            if (!empty($this->input['usuario_id'])) {
                $filtros['criado_por'] = ' = '.$this->input['usuario_id'];
            }
        }else{
            $filtros['criado_por'] = ' = '.$_SESSION['usuario']['id'];
        }

        if (!empty($this->input['status_id'])) {
            $filtros['status_id'] = ' = '.$this->input['status_id'];
        }


        if (!empty($this->input['uf'])) {
            $filtros['uf'] = ' = '."'".$this->input['uf']."'".' OR uf_cliente = '."'".$this->input['uf']."'";
        }

        if (!empty($this->input['est_id'])) {
            $est = explode('-', $this->input['est_id']);
            if($est['0'] == 'I'){
                $filtros['prospect_id'] = ' = '."'".$est['1']."'";
                $prospect = Prospect::whereId($est['1'])->first();

                if(!empty($prospect)){
                    $prospect = $prospect->toArray();
                    $this->input['cod_cli']       = $prospect['id'];
                    $this->input['descricao_cli'] = $prospect['razao_social'];
                }
            }else{
                $filtros['est_id'] = ' = '."'".$est['1']."'";
                $estab = Estabelecimento::with('ClienteErp')->whereId($est['1'])->first();

                if(!empty($estab)){
                    $estab = $estab->toArray();
                    $this->input['cod_cli']       = $estab['cliente_erp']['cod_cli'];
                    $this->input['descricao_cli'] = $estab['descricao'];
                }
            }
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


        $filtros = function($query) use ($filtros) {
             if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        };

        $horario_permitido =  Core::parametro('comercial_horario_permitido');
        $horario_permitido =  explode("|", $horario_permitido);

        $this->data['hora_ini'] = $horario_permitido['0'];
        $this->data['hora_fim'] = $horario_permitido['1'];

        try {

            if (!empty($pagina)) {
                /* Total sem paginação  */
                $total = modelRelatorioVisitaList::where($filtros)->count();
                $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

                /**
                 * records = qtd de registros
                 * offset = inicia no registro n
                */
                $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
                $offset = Config::read('APP_PERPAGE');
            }

            $rows = modelRelatorioVisitaList::where($filtros);

            if (!empty($pagina)) {
                $rows->skip($records)
                    ->take($offset);
            }

            $rows = $rows->get();

            $total_tela = count($rows);

            if (!empty($rows)) {
                foreach ($rows as &$row) {
                    $row['permite_excluir'] = true;
                        $key = false;
                    $key = Core::multidimensionalSearchArray($usuarios,array('id' => $row['usuario_id']));

                    if($key !== false){
                        $row['vendedor'] = $usuarios[$key]['nome'];
                    }

                }
            }


        } catch (\Illuminate\Database\QueryException $e) {
          //  var_dump($e);die;
            $rows = false;
            $total_tela = 0;
            $total = 0;
            $num_paginas = 1;
        }

        $this->data['status'] = Status::get();
        $this->data['usuarios'] = Usuario::where('situacao','ativo')->get();
        $this->data['horario_permitido'] = $horario_permitido;
        $this->data['filtros'] = $this->input;
        $this->data['resultado'] = $rows;
        $this->data['setor'] = $setor_usuario;
        if (!empty($pagina)) {
            $this->data['paginacao'] = Core::montaPaginacao(
                true,
                $total_tela,
                $total,
                $num_paginas,
                $pagina,
                '/comercial/relatorio-visitas/pagina',
                $_SERVER['QUERY_STRING']
            );
        }

        return $this->data;
    }

    public function form($id = null)
    {

        $auxiliaresModel  = new Auxiliares();
        $usuarios = $auxiliaresModel->usuarios('result');

        $row = modelRelatorioVisitaList::find($id);

        if($_SESSION['usuario']['id'] == 1 or $_SESSION['usuario']['tipo'] == 'admin'){
            $this->data['setor'] = 'admin';
        }else{

            $usuario = Usuario::with('SetorUsuario')->whereId($_SESSION['usuario']['id'])->first();

            if($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_pos_vendas')){
                $this->data['setor'] = 'pos_vendas';
            }elseif($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_coordenador')){
                $this->data['setor'] = 'coordenador';
            }else{
                $this->data['setor'] = 'vendedor';
            }
        }

        if(!empty($row)){

            if($this->data['setor'] == 'vendedor'){
                if ($row->criado_por != $_SESSION['usuario']['id']){
                    return false;
                }
            }

            $key = Core::multidimensionalSearchArray($usuarios,array('id' => $row->usuario_id));

            if($key !== false){
                $row->vendedor = $usuarios[$key]['nome'];
            }

            $row['clima_concorrentes'] = modelRelatorioVisitaConcorrente::with('Concorrente')->where('relatorio_id', $id)
                                                                    ->where('tipo','C')->get();

            $row['rodoar_concorrentes'] = modelRelatorioVisitaConcorrente::with('Concorrente')->where('relatorio_id', $id)
                                                                    ->where('tipo','R')->get();

            $row['geladeira_concorrentes'] = modelRelatorioVisitaConcorrente::with('Concorrente')->where('relatorio_id', $id)->where('tipo','G')->get();

            $arquivos   =  modelRelatorioVisitaArquivos::where('relatorio_id', $id)->get();

            if(!empty($arquivos)){
                foreach ($arquivos as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['relatorio_id'].'!'.$arq['arquivo']);
                }
            }

        }


        $this->data['categorias'] =  CategoriaConcorrente::where('status','A')
                                                          ->get();
        $this->data['registro'] = $row;
        $this->data['arquivos'] = !empty($arquivos) ? $arquivos : false;

        $horario_permitido =  Core::parametro('comercial_horario_permitido');
        $horario_permitido =  explode("|", $horario_permitido);

        $this->data['hora_ini'] = $horario_permitido['0'];
        $this->data['hora_fim'] = $horario_permitido['1'];


        return $this->data;
    }

    public function add($files = false)
    {

        if(empty($this->input['est_id'])){

            $prospectDados['nome_fantasia'] = $this->input['nome_fantasia'];
            $prospectDados['razao_social']  = $this->input['razao_social'];
            $prospectDados['cnpj_cpf']      = $this->input['cnpj_cpf'];
            $prospectDados['uf']            = $this->input['uf'];
            $prospectDados['cidade']        = $this->input['cidade'];
            $prospectDados['contato']       = $this->input['contato'];
            $prospectDados['tel_celular']   = $this->input['tel_celular'];
            $prospectDados['telefone']      = $this->input['telefone'];
            $prospectDados['e_mail']        = $this->input['e_mail'];
            $prospect = Prospect::criar($prospectDados);
            if(!empty($prospect->id)){
                $this->input['prospect_id'] = $prospect->id;
            }
        }


        $relatorioVisita = modelRelatorioVisita::criar($this->input);

        if(!empty($relatorioVisita->id)){
            $st['status_id'] = 1; //Aguardando Pós Vendas
            $st['relatorio_id'] = $relatorioVisita->id;
            $status = modelRelatorioVisitaStatus::criar($st);

            if(!empty($this->input['clima_concorrente_id'])){
                foreach ($this->input['clima_concorrente_id'] as $clima_concorrente) {
                    $conc['tipo'] = 'C';
                    $conc['relatorio_id'] = $relatorioVisita->id;
                    $conc['concorrente_id'] = $clima_concorrente;
                    $concorrente = modelRelatorioVisitaConcorrente::criar($conc);
                }
            }

            if(!empty($this->input['geladeira_concorrente_id'])){
                foreach ($this->input['geladeira_concorrente_id'] as $geladeira_concorrente) {
                    $conc['tipo'] = 'G';
                    $conc['relatorio_id'] = $relatorioVisita->id;
                    $conc['concorrente_id'] = $geladeira_concorrente;
                    $concorrente = modelRelatorioVisitaConcorrente::criar($conc);
                }
            }

            if(!empty($this->input['rodoar_concorrente_id'])){
                foreach ($this->input['rodoar_concorrente_id'] as $rodoar_concorrente) {
                    $conc['tipo'] = 'R';
                    $conc['relatorio_id'] = $relatorioVisita->id;
                    $conc['concorrente_id'] = $rodoar_concorrente;
                    $concorrente = modelRelatorioVisitaConcorrente::criar($conc);
                }
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
                        $ins_file['relatorio_id']  = $relatorioVisita->id;
                        $ins_file['tipo']    = $file['type'];
                        $ins_file['arquivo'] = $k.$relatorioVisita->id."-".$file['name'];
                        move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'relatorio_visitas'.DS.$k.$relatorioVisita->id."-".$file['name']);
                        modelRelatorioVisitaArquivos::criar($ins_file);
                        $k++;
                    }

                }
            }

            Core::enviaEmailPosVendas('https://portal.resfriar.com.br/comercial/relatorio-visitas/editar/'.$relatorioVisita->id);
        }

        return $relatorioVisita;
    }

    public function edit($files = false)
    {
        unset($this->input['_METHOD']);

        if($_SESSION['usuario']['id'] == 1 or $_SESSION['usuario']['tipo'] == 'admin'){
            $setor = 'admin';
        }else{

            $usuario = Usuario::with('SetorUsuario')->whereId($_SESSION['usuario']['id'])->first();

            if($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_pos_vendas')){
                $setor = 'pos_vendas';
            }elseif($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_coordenador')){
                $this->data['setor'] = 'coordenador';
            }else{
                $setor = 'vendedor';
            }
        }


        if($setor != 'pos_vendas'){


            $this->input['check_climatizador'] = !empty($this->input['check_climatizador']) ? 'S' : 'N';
            $this->input['check_clima_conc']   = !empty($this->input['check_clima_conc']) ? 'S' : 'N';
            $this->input['check_rodoar']       = !empty($this->input['check_rodoar']) ? 'S' : 'N';
            $this->input['check_rodo_conc']    = !empty($this->input['check_rodo_conc']) ? 'S' : 'N';
            $this->input['check_geladeira']    = !empty($this->input['check_geladeira']) ? 'S' : 'N';
            $this->input['check_gela_conc']    = !empty($this->input['check_gela_conc']) ? 'S' : 'N';

            $row = modelRelatorioVisita::find($this->input['id']);
            $updated = $row->update($this->input);


            if(empty($this->input['est_id'])){
                $row2 = Prospect::find($this->input['prospect_id']);
                $prospectDados['id']            = $this->input['prospect_id'];
                $prospectDados['nome_fantasia'] = $this->input['nome_fantasia'];
                $prospectDados['razao_social']  = $this->input['razao_social'];
                $prospectDados['cnpj_cpf']      = $this->input['cnpj_cpf'];
                $prospectDados['uf']            = $this->input['uf'];
                $prospectDados['cidade']        = $this->input['cidade'];
                $prospectDados['contato']       = $this->input['contato'];
                $prospectDados['tel_celular']   = $this->input['tel_celular'];
                $prospectDados['telefone']      = $this->input['telefone'];
                $prospectDados['e_mail']        = $this->input['e_mail'];
                $updatedProspect = $row2->update($prospectDados);
            }

            //*  DELETA CONCORRENTES *//

            $deletou = modelRelatorioVisitaConcorrente::where('relatorio_id',$this->input['id'])->delete();

            if(!empty($this->input['clima_concorrente_id'])){
                foreach ($this->input['clima_concorrente_id'] as $clima_concorrente) {
                    $conc['tipo'] = 'C';
                    $conc['relatorio_id'] = $this->input['id'];
                    $conc['concorrente_id'] = $clima_concorrente;
                    $concorrente = modelRelatorioVisitaConcorrente::criar($conc);
                }
            }

            if(!empty($this->input['geladeira_concorrente_id'])){
                foreach ($this->input['geladeira_concorrente_id'] as $geladeira_concorrente) {
                    $conc['tipo'] = 'G';
                    $conc['relatorio_id'] = $this->input['id'];
                    $conc['concorrente_id'] = $geladeira_concorrente;
                    $concorrente = modelRelatorioVisitaConcorrente::criar($conc);
                }
            }

            if(!empty($this->input['rodoar_concorrente_id'])){
                foreach ($this->input['rodoar_concorrente_id'] as $rodoar_concorrente) {
                    $conc['tipo'] = 'R';
                    $conc['relatorio_id'] = $this->input['id'];
                    $conc['concorrente_id'] = $rodoar_concorrente;
                    $concorrente = modelRelatorioVisitaConcorrente::criar($conc);
                }
            }

            //** ARQUIVOS **//

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
                        $ins_file['relatorio_id']  = $this->input['id'];
                        $ins_file['tipo']    = $file['type'];
                        $ins_file['arquivo'] = $k.$this->input['id']."-".$file['name'];
                        move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'relatorio_visitas'.DS.$k.$this->input['id']."-".$file['name']);
                        modelRelatorioVisitaArquivos::criar($ins_file);
                        $k++;
                    }

                }
            }
        }else{

            $row = modelRelatorioVisita::find($this->input['id']);
            $input['obs_pos_venda']  = $this->input['obs_pos_venda'];
            $updated = $row->update($input);

            if($updated){
                $st['status_id'] = 2; //Concluído
                $st['relatorio_id'] = $this->input['id'];
                $status = modelRelatorioVisitaStatus::criar($st);
            }

        }

        return $updated;
    }

    public function upload($file = null)
    {
        $arquivo = false;
        if (!empty($file)) {
            if ($file['size'] > 0 && $file['error'] === 0) {
                $ins_file['relatorio_id']  = $this->input['id'];
                $ins_file['tipo']    = $file['type'];
                $ins_file['arquivo'] = $this->input['id']."-".$file['name'];
                if (move_uploaded_file(
                    $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'relatorio_visitas'.DS.$this->input['id']."-".$file['name']
                )
                ) {
                    $arquivo = modelRelatorioVisitaArquivos::criar($ins_file);
                    // $arquivo = $ins_file;
                }
            }
        }
        return $arquivo;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = modelRelatorioVisita::whereId($this->input['id'])->delete();
        return $deleted;
    }

    public function excluirArquivo()
    {
        if (!empty($this->input['id'])) {
            $excluiu = modelRelatorioVisitaArquivos::whereId($this->input['id'])->delete();
            return $excluiu;
        } else {
            return false;
        }
    }

    public function imprimir($ids = null)
    {
        $auxiliaresModel  = new Auxiliares();
        $usuarios = $auxiliaresModel->usuarios('result');

        if($_SESSION['usuario']['id'] == 1 or $_SESSION['usuario']['tipo'] == 'admin'){
            $this->data['setor'] = 'admin';
        }else{

            $usuario = Usuario::with('SetorUsuario')->whereId($_SESSION['usuario']['id'])->first();

            if($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_pos_vendas')){
                $this->data['setor'] = 'pos_vendas';
            }elseif($usuario['SetorUsuario']['setor_id'] == Core::parametro('comercial_id_setor_coordenador')){
                $this->data['setor'] = 'coordenador';
            }else{
                $this->data['setor'] = 'vendedor';
            }
        }

        $ids = explode('-', $ids);

        if(!empty($ids)){

            foreach ($ids as $k => $id) {
                $row[$k] = modelRelatorioVisitaList::find($id);

                if(!empty($row[$k])){


                    foreach ($row as &$r) {

                        if($this->data['setor'] == 'vendedor'){
                            if ($r->criado_por != $_SESSION['usuario']['id']){
                                return false;
                            }
                        }
                        $key = Core::multidimensionalSearchArray($usuarios,array('id' => $r['usuario_id']));

                        if($key !== false){
                            $r['vendedor'] = $usuarios[$key]['nome'];
                        }

                    }

                    $row[$k]['clima_concorrentes'] = modelRelatorioVisitaConcorrente::with('Concorrente')->where('relatorio_id', $id)
                                                                            ->where('tipo','C')->get();

                    $row[$k]['rodoar_concorrentes'] = modelRelatorioVisitaConcorrente::with('Concorrente')->where('relatorio_id', $id)
                                                                            ->where('tipo','R')->get();

                    $row[$k]['geladeira_concorrentes'] = modelRelatorioVisitaConcorrente::with('Concorrente')->where('relatorio_id', $id)->where('tipo','G')->get();
                }

            }
        }



        $this->data['registro'] = !empty($row) ? $row : false;
        return $this->data;
    }

}
