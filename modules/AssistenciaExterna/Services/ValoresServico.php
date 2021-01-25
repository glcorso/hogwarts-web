<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2019
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\AssistenciaExterna\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\AssistenciaExterna\Models\Servicos;
use Lidere\Modules\AssistenciaExterna\Models\ValorServico;
use Lidere\Modules\AssistenciaExterna\Models\Agrupador;
use Lidere\Modules\Services\Services;
use Illuminate\Database\QueryException;

/**
 * Service Valor por Servico
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Services\ValoresCategoria
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class ValoresServico extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     *
     * @return array
     */
    public function list($pagina = 1)
    {
        $filtros = array();

        $filtros = function ($query) use ($filtros) {
            if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        };

        try {
            /* Total sem paginação  */
            $total = ValorServico::where($filtros)->count();
            $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

            /**
             * Paginação
             *
             * @var records = qtd de registros
             * @var offset = inicia no registro n
            */
            $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
            $offset = Config::read('APP_PERPAGE');



            $rows = ValorServico::where($filtros)
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

        $this->data['filtros'] = $this->input;
        $this->data['resultado'] = $rows;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/comercial/valores-servicos/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    /**
     * Recupera os dados para o formulário
     *
     * @param integer $id Id do registro
     *
     * @return void
     */
    public function form($id = null)
    {
        $this->data['registro'] = ValorServico::with(array('ValorServicoAgrup.ValorServicoPrecos' => function($query) {
            $query->orderBy('servico_id');
        }))->find($id);

        //echo "<pre>";
        //var_dump($this->data['registro']->toArray());die;


       // with(['ValorServicoPrecos'])->orderBy('servico_id')
            

        $this->data['servicos'] = Servicos::whereSit(1)->orderBy('cod_serv')
            ->get();

        $this->data['agrupadores'] = Agrupador::whereSituacao(1)->orderBy('id')
            ->get();

        return $this->data;
    }

    /**
     * Cadastra um novo registro valor por categoria
     *
     * @return void
     */
    public function add()
    {   

        $valorServico = ValorServico::criar($this->input);
        return $valorServico;
    }

    /**
     * Edita um registro valor por categoria
     *
     * @return void
     */
    public function edit()
    {
        unset($this->input['_METHOD']);
        return ValorServico::atualizar($this->input);
    }

    /**
     * Remove um registro pelo id do valor por categoria
     *
     * @return void
     */
    public function delete()
    {
        return ValorServico::find($this->input['id'])
            ->delete();
    }
}
