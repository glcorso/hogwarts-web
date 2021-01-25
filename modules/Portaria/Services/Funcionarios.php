<?php

namespace Lidere\Modules\Portaria\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Portaria\Models\Funcionario;
use Lidere\Modules\Services\Services;
use Illuminate\Database\QueryException;

/**
 * Funcionarios
 *
 * @package Lidere\Modules
 * @subpackage Funcionarios\Services
 * @author William Mascarello
 * @copyright 2020 Lidere Sistemas
 */
class Funcionarios extends Services
{
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
            $total = Funcionario::where($filtros)->count();
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

            /**
             * records = qtd de registros
             * offset = inicia no registro n
            */
            $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
            $offset = Config::read('APP_PERPAGE');



            $rows = Funcionario::where($filtros)
                            ->skip($records)
                            ->take($offset)
                            ->orderBy('descricao')
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
            '/portaria/funcionarios/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    public function form($id = null)
    {
        $row = Funcionario::find($id);

        $this->data['registro'] = $row;

        return $this->data;
    }

    public function add()
    {
        $funcionario = Funcionario::criar($this->input);

        return $funcionario;
    }

    public function edit()
    {
        unset($this->input['_METHOD']);

        $row = Funcionario::find($this->input['id']);
        $updated = $row->update($this->input);
        return $updated;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = Funcionario::whereId($this->input['id'])->delete();
        return $deleted;
    }

}
