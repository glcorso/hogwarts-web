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
use Lidere\Modules\Assistencia\Models\Defeitos as defeitosModel;

/**
 * Defeitos
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Defeitos
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Defeitos extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $defeitosModel = new defeitosModel();
        $this->filtros = array();

        if (!empty($this->input['string'])) {
            $this->filtros = array('(' => 'm.cod_defeito like \'%'.$this->input['string'].'%\' OR UPPER(m.descricao) like UPPER(\'%'.$this->input['string'].'%\'))');
        }

        /* Total sem paginação */
        $total = count($defeitosModel->getDefeitos('result', $this->filtros));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $defeitos = $defeitosModel->getDefeitos('result', $this->filtros, array($inicio, $fim));
        $total_tela = count($defeitos);

        if (!empty($defeitos)) {
            foreach ($defeitos as &$defeito) {
                $defeito['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $defeitos;
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
        $defeitosModel = new defeitosModel();
        $defeitos = array();
        if ( !empty($id) ) {
            $defeitos = $defeitosModel->getDefeitos(
                'row',
                array(
                    'd.id' => ' = '.$id
                )
            );
        }

        $this->data['registro'] = $defeitos;

        return $this->data;
    }

    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $defeito_id = false;
        $defeito = array();
        $defeitosModel = new defeitosModel(); 

        if(!empty($this->input)){
            $defeito['cod_defeito'] = $this->input['cod_defeito'];
            $defeito['descricao']  = $this->input['descricao'];
            $defeito['situacao']   = $this->input['situacao']; 
            $defeito_id = $defeitosModel->cadastrarDefeito($defeito);
        } 
       
        return $defeito_id;
    }

    public function edit()
    {
        $defeitosModel = new defeitosModel(); 

        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $updated = false;

        if(!empty($this->input)){
            $defeito['cod_defeito'] = $this->input['cod_defeito'];
            $defeito['descricao']  = $this->input['descricao'];
            $defeito['situacao']   = $this->input['situacao']; 
            $defeito['id']         = $id;
            $updated = $defeitosModel->alterarDefeito($defeito);
        } 
       
        return $updated;
    }

    public function delete()
    {
        $defeitosModel = new defeitosModel(); 

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);
        $deleted = false;

        if(!empty($id)){
            $deleted = $defeitosModel->deletarDefeito($id);
        } 
       
        return $deleted;
    }
}
