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
namespace Lidere\Modules\PlanoProducao\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;
use Lidere\Modules\PlanoProducao\Models\Vinculo as vinculoModel;

/**
 * Vinculo
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage PlanoProducao\Services\Vinculo
 * @author     Sergio Sirtoli <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Vinculo extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $auxiliaresModel = new Auxiliares();
        $vinculoModel = new vinculoModel();
        $this->filtros = array();

        if (!empty($this->input['string'])) {
            $this->filtros = array('( UPPER(i.descricao)' => ' like UPPER(\'%'.$this->input['string'].'%\'))');
        }

        /* Total sem paginação */
        $total = count($vinculoModel->getVinculos('result', $this->filtros));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $vinculos = $vinculoModel->getVinculos('result', $this->filtros, array($inicio, $fim));
        $total_tela = count($vinculos);

        if (!empty($vinculos)) {
            foreach ($vinculos as &$vinculo) {
                $usuario = false;
                $usuario = $auxiliaresModel->usuarios('row',array('u.id' => " = ".$vinculo['usuario_id']));
                $vinculo['usuario'] = $usuario['nome'];
                $vinculo['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $vinculos;
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
        $vinculoModel = new vinculoModel();
        $auxiliaresModel = new Auxiliares();
        $vinculos = array();

        $usuarios = $auxiliaresModel->usuarios('result',array('situacao' => " = 'ativo'"));
        $planejadores = $vinculoModel->retornaPlanejadores();
        if ( !empty($id) ) {
            $vinculos = $vinculoModel->getVinculos(
                'row',
                array(
                    'i.id' => ' = '.$id
                )
            );
        }
        $this->data['usuarios'] = $usuarios;
        $this->data['registro'] = $vinculos;
        $this->data['planejadores'] = $planejadores;

        return $this->data;
    }

    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $item_id = false;
        $item = array();
        $vinculoModel = new vinculoModel(); 

        if(!empty($this->input)){
            $item['usuario_id']  = $this->input['usuario_id'];
            $item['func_id']   = $this->input['func_id']; 
            $item_id = $vinculoModel->cadastrarVinculo($item);
        } 
       
        return $item_id;
    }

    public function edit()
    {
        $vinculoModel = new vinculoModel(); 

        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $updated = false;

        if(!empty($this->input)){
            $item['usuario_id']  = $this->input['usuario_id'];
            $item['func_id']     = $this->input['func_id']; 
            $item['id']          = $id;
            $updated = $vinculoModel->alterarVinculo($item);
        } 
       
        return $updated;
    }

    public function delete()
    {
        $vinculoModel = new vinculoModel(); 

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $deleted = false;

        if(!empty($id)){
            $deleted = $vinculoModel->deletarVinculo($id);
        } 
       
        return $deleted;
    }
}
