<?php

namespace Lidere\Modules\Comercial\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Comercial\Models\Concorrente;
use Lidere\Modules\Comercial\Models\CategoriaConcorrente;
use Lidere\Modules\Services\ServicesInterface;
use Lidere\Modules\Comercial\Models\Cliente;
use Lidere\Modules\Comercial\Models\Estabelecimento;
use Lidere\Modules\Comercial\Models\UF;
use Illuminate\Database\QueryException;

/**
 * Concorrentes
 *
 * @package Lidere\Modules
 * @subpackage Concorrentes\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Concorrentes implements ServicesInterface
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
            $total = Concorrente::with('Categoria')->where($filtros)->count();
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

            /**
             * records = qtd de registros
             * offset = inicia no registro n
            */
            $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
            $offset = Config::read('APP_PERPAGE');

        

            $rows = Concorrente::with('Categoria')->where($filtros)
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
            '/comercial/concorrentes/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    public function form($id = null)
    {
        $row = Concorrente::find($id);

        $this->data['categorias'] =  CategoriaConcorrente::where('status','A')
                                                          ->get();
        $this->data['registro'] = $row;

        return $this->data;
    }

    public function add()
    {
        $concorrente = Concorrente::criar($this->input);

        return $concorrente;
    }

    public function edit()
    {
        unset($this->input['_METHOD']);

        $row = Concorrente::find($this->input['id']);
        $updated = $row->update($this->input);
        return $updated;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = Concorrente::whereId($this->input['id'])->delete();
        return $deleted;
    }

}
