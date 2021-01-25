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
namespace Lidere\Modules\Assistencia\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Assistencia\Models\Itens as itemModel;

/**
 * Itens
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Itens
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Itens extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $itemModel = new itemModel();
        $this->filtros = array();

        if (!empty($this->input['string'])) {
            $this->filtros = array('( UPPER(i.descricao)' => ' like UPPER(\'%'.$this->input['string'].'%\'))');
        }

        /* Total sem paginação */
        $total = count($itemModel->getItens('result', $this->filtros));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $itens = $itemModel->getItens('result', $this->filtros, array($inicio, $fim));
        $total_tela = count($itens);

        if (!empty($itens)) {
            foreach ($itens as &$item) {
                $item['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $itens;
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
        $itemModel = new itemModel();
        $itens = array();
        if ( !empty($id) ) {
            $itens = $itemModel->getItens(
                'row',
                array(
                    'i.id' => ' = '.$id
                )
            );
        }

        $this->data['registro'] = $itens;

        return $this->data;
    }

    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $item_id = false;
        $item = array();
        $itemModel = new itemModel(); 

        if(!empty($this->input)){
            $item['descricao']  = $this->input['descricao'];
            $item['situacao']   = $this->input['situacao']; 
            $item_id = $itemModel->cadastrarItem($item);
        } 
       
        return $item_id;
    }

    public function edit()
    {
        $itemModel = new itemModel(); 

        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $updated = false;

        if(!empty($this->input)){
            $item['descricao']  = $this->input['descricao'];
            $item['situacao']   = $this->input['situacao']; 
            $item['id']         = $id;
            $updated = $itemModel->alterarItem($item);
        } 
       
        return $updated;
    }

    public function delete()
    {
        $itemModel = new itemModel(); 

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $deleted = false;

        if(!empty($id)){
            $deleted = $itemModel->deletarItem($id);
        } 
       
        return $deleted;
    }
}
