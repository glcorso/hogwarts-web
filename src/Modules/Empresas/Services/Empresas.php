<?php

namespace Lidere\Modules\Empresas\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Empresas\Models\Empresa;
use Lidere\Modules\Empresas\Models\EmpresaDocumentos as EmpresaDocumentosModel;
use Lidere\Modules\Services\ServicesInterface;
use Lidere\Models\Aplicacao;

/**
 * Empresas
 *
 * @package Lidere\Modules
 * @subpackage Empresas\Services
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Empresas implements ServicesInterface
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
        $filtros = $this->filtros;
        $filtros = function($query) use ($filtros) {
             if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        };

        /* Total sem paginação */
        $total = Empresa::where($filtros)->count();
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        /**
         * records = qtd de registros
         * offset = inicia no registro n
         */
        $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $offset = Config::read('APP_PERPAGE');

        $rows = Empresa::where($filtros)
                        ->skip($records)
                        ->take($offset)
                        ->get();
        $total_tela = count($rows);

        if (!empty($rows)) {
            foreach ($rows as &$row) {
                $row['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $rows;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/'.$this->modulo['url'].'/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    public function form($id = null)
    {
        $empresa = Empresa::find($id);

        if(!empty($empresa)){
            $empresa['arquivos'] = EmpresaDocumentosModel::where('empresa_id', $empresa['id'])->get();
            if(!empty($empresa['arquivos'])){
                foreach ($empresa['arquivos'] as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['empresa_id'].'!'.$arq['arquivo']);
                }
            }
        }

        $this->data['registro'] = $empresa;

        return $this->data;
    }

    public function add()
    {
        return Empresa::create($this->input);
    }

    public function edit($files = false)
    {
        unset($this->input['_METHOD']);

        $aplicacaoObj = new Aplicacao();

        $row = Empresa::find($this->input['id']);
        $updated = $row->update($this->input);

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
                    $ins_file['empresa_id']  = $this->input['id'];
                    $ins_file['tipo']          = $file['type'];
                    $ins_file['arquivo']       = $k.$this->input['id']."-".$file['name'];
                    move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'empresa_documentos'.DS.$k.$this->input['id']."-".$file['name']);
                    $vinc = $aplicacaoObj->insert('tempresa_documentos', $ins_file);
                    $k++;
                }

            }
        }

        return $updated;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = Empresa::whereId($this->input['id'])->delete();
        return $deleted;
    }
}
