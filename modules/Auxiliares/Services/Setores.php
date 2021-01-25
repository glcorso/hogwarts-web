<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Auxiliares\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Auxiliares\Models\Setores as setorModel;

/**
 * Setores
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Services\Setores
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Setores extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $setorModel = new setorModel();

        /* Total sem paginação */
        $total = count($setorModel->setores('result'));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $setores = $setorModel->setores('result',false ,array($inicio, $fim));
        $total_tela = count($setores);

        if (!empty($setores)) {
            foreach ($setores as &$usuario) {
                $usuario['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $setores;
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
        $setorModel = new setorModel();
        $setores = array();
        if ( !empty($id) ) {
            $setores = $setorModel->setores(
                'row',
                array(
                    's.id' => ' = '.$id
                )
            );
        }

        $this->data['registro'] = $setores;

        return $this->data;
    }

    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $aplicacaoObj = new Aplicacao();

        $setor_id = $aplicacaoObj->insert('tsetores', $this->input);

        return $setor_id;
    }

    public function edit()
    {
        $aplicacaoObj = new Aplicacao();

        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $id = $this->input['id'];
        unset($this->input['id']);
        unset($this->input['_METHOD']);

        $updated = $aplicacaoObj->update('tsetores', $id, $this->input);

        return $updated;
    }
}
