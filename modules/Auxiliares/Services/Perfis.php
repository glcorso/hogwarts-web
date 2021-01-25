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
use Lidere\Modules\Auxiliares\Models\Perfil as perfilModel;

/**
 * Perfis
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Services\Perfils
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Perfis extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $perfilModel = new perfilModel();

        /* Total sem paginação */
        $total = count($perfilModel->perfis('result'));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $perfis = $perfilModel->perfis('result',false ,array($inicio, $fim));
        $total_tela = count($perfis);

        if (!empty($perfis)) {
            foreach ($perfis as &$usuario) {
                $usuario['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $perfis;
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
        $perfilModel = new perfilModel();
        $perfis = array();
        if ( !empty($id) ) {
            $perfis = $perfilModel->perfis(
                'row',
                array(
                    'p.id' => ' = '.$id
                )
            );
        }

        if(!empty($id)){

            $empresas = Empresa::whereSituacao('ativo')
                               ->get();
            $empresas = !empty($empresas) ? $empresas->toArray() : array();
            if (!empty($empresas)) {
                foreach ($empresas as &$empresa) {
                    $empresa['modulos'] = Core::modulosPerfis(null, $id, $empresa['id'], true, 'A');
                }
            }


            //echo "<pre>";
           // var_dump($empresas);die;

            $this->data['empresas']  = $empresas;
            $this->data['permissions'] = Core::permissions($empresas);
        }
        $this->data['registro'] = $perfis;

        return $this->data;
    }

    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $aplicacaoObj = new Aplicacao();

        $perfil_id = $aplicacaoObj->insert('tperfis', $this->input);

        return $perfil_id;
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

        $modulos = array();
        if (isset($this->input['modulos'])) {
            $modulos = $this->input['modulos'];
            unset($this->input['modulos']);
        }

        $updated = $aplicacaoObj->update('tperfis', $id, $this->input);

        $aplicacaoObj->deleteByColumn('tmodulos_perfil', array('perfil_id' => $id));
        if (!empty($modulos)) {
            foreach ($modulos as $module => $permissao) {
                $sub = null;
                list($empr, $mod, $sub) = explode('#', $module);
                $relation = array();
                $relation['perfil_id'] = $id;
                $relation['modulo_id'] = $sub != null ? $sub : $mod;
                $relation['permissao'] = $permissao;
                $relation['empresa_empr_id'] = $empr;

                $aplicacaoObj->insert('tmodulos_perfil', $relation);
            }
        }


        return $updated;
    }
}
