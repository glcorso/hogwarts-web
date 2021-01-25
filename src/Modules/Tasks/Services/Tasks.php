<?php

namespace Lidere\Modules\Tasks\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Tasks\Models\Task;
use Lidere\Modules\Services\ServicesInterface;

/**
 * Tasks
 *
 * @package Lidere\Modules
 * @subpackage Tasks\Services
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Tasks implements ServicesInterface
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

    /**
     * Model Auxiliares
     * @var null
     */
    private $auxiliaresModel = null;

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
        $total = Task::where($filtros)->count();
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        /**
         * records = qtd de registros
         * offset = inicia no registro n
         */
        $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $offset = Config::read('APP_PERPAGE');

        $tasks = Task::where($filtros)
                     ->skip($records)
                     ->take($offset)
                     ->get();
        $total_tela = count($tasks);

        if (count($tasks) > 0) {
            foreach ($tasks as &$task) {
                $task['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $tasks;
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
        $tasks = Task::find($id);

        $this->data['registro'] = $tasks;

        return $this->data;
    }

    public function add()
    {
        return Task::create($this->input);
    }

    public function edit()
    {
        unset($this->input['_METHOD']);

        $task = Task::find($this->input['id']);
        $updated = $task->update($this->input);
        return $updated;
    }

    public function delete()
    {
        unset($this->input['_METHOD']);

        $deleted = Task::whereId($this->input['id'])->delete();
        return $deleted;
    }
}
