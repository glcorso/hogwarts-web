<?php

namespace Lidere\Modules\Avisos\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Avisos\Models\TAvisos as TAvisos;
use Lidere\Modules\Avisos\Models\TAvisosArquivos as TAvisosArquivos;
use Lidere\Modules\Avisos\Models\TAvisosSetores as TAvisosSetores;
use Lidere\Modules\Services\ServicesInterface;
use Illuminate\Database\QueryException;
use Lidere\Modules\Auxiliares\Models\Setores as setorModel;

/**
 * Cadastro
 *
 * @package Lidere\Modules
 * @subpackage Cadastro\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Cadastro implements ServicesInterface
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
               

        $filtros = function($query) use ($filtros) {
             if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        };

        try{

            /* Total sem paginação  */
            $total = TAvisos::where($filtros)->count();
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

            /**
             * records = qtd de registros
             * offset = inicia no registro n
            */
            $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
            $offset = Config::read('APP_PERPAGE');

        

            $rows = TAvisos::where($filtros)->orderBy('codigo','desc')
                            ->skip($records)
                            ->take($offset)
                            ->get();
            $total_tela = count($rows);

            if (!empty($rows)) {
                foreach ($rows as &$row) {
                    $row['permite_excluir'] = true;
                }
            }


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
            '/avisos/cadastro/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    public function form($id = null)
    {
        $setorModel = new setorModel();

        $row = TAvisos::find($id);
        if(!empty($row)){
            $row['arquivos'] = TAvisosArquivos::where('aviso_id', $row['id'])->get();
            $row['setores'] = TAvisosSetores::where('aviso_id', $row['id'])->get();
            if(!empty($row['arquivos'])){
                foreach ($row['arquivos'] as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['aviso_id'].'!'.$arq['arquivo']);
                }
            }
        }



        $this->data['setores'] = $setorModel->setores('result', array('s.situacao = ' => "'ativo'"));


        if(!empty($row['setores'])){
            $row['setores'] = $row['setores']->toArray();
            foreach ($this->data['setores'] as &$setor) {
                foreach ($row['setores'] as $setor_aviso) {

                    if( $setor['id'] == $setor_aviso['setor_id']){
                        $setor['selecionado'] = true;
                    }else{
                        $setor['selecionado'] = false;
                    }
                }
            }
        }

        

        $this->data['registro'] = $row;

        return $this->data;
    }

    public function add($files = false)
    {       
        $setores = !empty($this->input['setor_ids']) ? $this->input['setor_ids'] : false;
        unset($this->input['setor_ids']);


        $aviso = TAvisos::criar($this->input);
        if(!empty($aviso->id)){
            if(!empty($setores)){
                foreach ($setores as $setor_id) {
                    $inputSetores['aviso_id'] = $aviso->id;
                    $inputSetores['setor_id'] = $setor_id;
                    TAvisosSetores::criar($inputSetores);
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
                        $ins_file['aviso_id']  = $aviso->id;
                        $ins_file['tipo']          = $file['type'];
                        $ins_file['arquivo']       = $k.$aviso->id."-".$file['name'];
                        move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'avisos_arquivos'.DS.$k.$aviso->id."-".$file['name']);
                        TAvisosArquivos::criar($ins_file);
                        $k++;
                    }

                }
            }
        }



        return $aviso;
    }

    public function edit($files = false)
    {
        unset($this->input['_METHOD']);

        $setores = !empty($this->input['setor_ids']) ? $this->input['setor_ids'] : false;
        unset($this->input['setor_ids']);

        $row = TAvisos::find($this->input['id']);
        $updated = $row->update($this->input);
        if($updated){
            $delete = TAvisosSetores::where('aviso_id',$row->id)->delete();
  
            if(!empty($setores)){
                foreach ($setores as $setor_id) {
                    $inputSetores['aviso_id'] = $row->id;
                    $inputSetores['setor_id'] = $setor_id;
                    TAvisosSetores::criar($inputSetores);
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
                        $ins_file['aviso_id']  = $row->id;
                        $ins_file['tipo']          = $file['type'];
                        $ins_file['arquivo']       = $k.$row->id."-".$file['name'];
                        move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'avisos_arquivos'.DS.$k.$row->id."-".$file['name']);
                        TAvisosArquivos::criar($ins_file);
                        $k++;
                    }

                }
            }
        }

        return $updated;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = TAvisos::whereId($this->input['id'])->delete();
        return $deleted;
    }

}
