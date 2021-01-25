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
use Lidere\Modules\Assistencia\Models\Motivos as motivosModel;

/**
 * Motivos
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Motivos
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Motivos extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $motivosModel = new motivosModel();
        $this->filtros = array();

        if (!empty($this->input['string'])) {
            $this->filtros = array('(' => 'm.cod_motivo like \'%'.$this->input['string'].'%\' OR UPPER(m.descricao) like UPPER(\'%'.$this->input['string'].'%\'))');
        }

        /* Total sem paginação */
        $total = count($motivosModel->getMotivos('result', $this->filtros));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $motivos = $motivosModel->getMotivos('result', $this->filtros, array($inicio, $fim));
        $total_tela = count($motivos);

        if (!empty($motivos)) {
            foreach ($motivos as &$motivo) {
                $motivo['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $motivos;
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
        $motivosModel = new motivosModel();
        $motivos = array();
        if ( !empty($id) ) {
            $motivos = $motivosModel->getMotivos(
                'row',
                array(
                    'm.id' => ' = '.$id
                )
            );
        }

        $this->data['registro'] = $motivos;

        return $this->data;
    }

    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $motivo_id = false;
        $motivo = array();
        $motivosModel = new motivosModel(); 

        if(!empty($this->input)){
            $motivo['cod_motivo'] = $this->input['cod_motivo'];
            $motivo['descricao']  = $this->input['descricao'];
            $motivo['situacao']   = $this->input['situacao']; 
            $motivo['defeito_obrigatorio']   = $this->input['defeito_obrigatorio']; 
            $motivo_id = $motivosModel->cadastrarMotivo($motivo);
        } 
       
        return $motivo_id;
    }

    public function edit()
    {
        $motivosModel = new motivosModel(); 

        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $updated = false;

        if(!empty($this->input)){
            $motivo['cod_motivo'] = $this->input['cod_motivo'];
            $motivo['descricao']  = $this->input['descricao'];
            $motivo['situacao']   = $this->input['situacao']; 
            $motivo['defeito_obrigatorio']   = $this->input['defeito_obrigatorio']; 
            $motivo['id']         = $id;
            $updated = $motivosModel->alterarMotivo($motivo);
        } 
       
        return $updated;
    }

    public function delete()
    {
        $motivosModel = new motivosModel(); 

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $deleted = false;

        if(!empty($id)){
            $deleted = $motivosModel->deletarMotivo($id);
        } 
       
        return $deleted;
    }
}
