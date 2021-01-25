<?php

namespace Lidere\Modules\Portaria\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Portaria\Models\Controle;
use Lidere\Modules\Portaria\Models\VControle;
use Lidere\Modules\Portaria\Models\Veiculo;
use Lidere\Modules\Portaria\Models\Funcionario;
use Lidere\Modules\Services\Services;
use Illuminate\Database\QueryException;
use Lidere\Modules\Portaria\Models\TipoAssunto;

/**
 * Controles
 *
 * @package Lidere\Modules
 * @subpackage Controles\Services
 * @author William Mascarello
 * @copyright 2020 Lidere Sistemas
 */
class Controles extends Services
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
            $total = VControle::where($filtros)->count();
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

            /**
             * records = qtd de registros
             * offset = inicia no registro n
            */
            $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
            $offset = Config::read('APP_PERPAGE');



            $rows = VControle::where($filtros)
                            ->skip($records)
                            ->take($offset)
                            ->get();
            $total_tela = count($rows);

        } catch (\Illuminate\Database\QueryException $e) {
            $rows = false;
            $total_tela = 0;
            $total = 0;
            $num_paginas = 1;
        }

        $this->data['usuario'] = $_SESSION['usuario'];
        $this->data['filtros'] = $this->input;
        $this->data['resultado'] = $rows;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/portaria/controles/pagina',
            $_SERVER['QUERY_STRING']
        );



        $veiculos = Veiculo::where('situacao', '1')->get();
        $assuntos = TipoAssunto::where('situacao', '1')->get();
        $this->data['assuntos'] = $assuntos;
        $this->data['veiculos'] = $veiculos;

        $funcionarios = Funcionario::where('situacao', '1')->orderBy('descricao')->get();
        $this->data['funcionarios'] = $funcionarios;

        return $this->data;
    }

    public function form($id = null)
    {
        $row = VControle::find($id);
        $veiculos = Veiculo::where('situacao', '1')->get();

        $this->data['registro'] = $row;
        $this->data['registro']['veiculos'] = $veiculos;

        return $this->data;
    }

    public function add()
    {   

        $controle = Controle::criar($this->input);

        return !empty($controle) ? true : false;
    }

    public function edit()
    {
        unset($this->input['_METHOD']);

        $row = Controle::find($this->input['id']);
        $updated = Controle::atualizar($row, $this->input);
        if($updated && !empty($this->input['veiculo_id'])){
            $row2 = Veiculo::find($this->input['veiculo_id']);

            if($row2->controle_km == '1'){
                $upd['km_atual'] = $this->input['km_entrada'];
                $updated2 = $row2->update($upd);
            }
        }
        return $updated;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = Controle::whereId($this->input['id'])->delete();
        return $deleted;
    }

}
