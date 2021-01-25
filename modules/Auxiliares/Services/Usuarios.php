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
use Lidere\Modules\Auxiliares\Models\Usuario as UsuarioModel;
use Lidere\Modules\Auxiliares\Models\UsuarioContrato as UsuarioContratoModel;
use Lidere\Modules\Auxiliares\Models\Cliente  as ClienteModel;
use Lidere\Modules\Auxiliares\Models\Setores as setorModel;
use Lidere\Modules\Auxiliares\Models\Perfil as perfilModel;

/**
 * Usuarios
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Services\Usuarios
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Usuarios extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $auxiliaresModel = new Auxiliares();
        $this->filtros = array('u.sistema' => ' = 0');

        if (!empty($this->input['string'])) {
            $this->filtros = array('(' => 'u.nome like \'%'.$this->input['string'].'%\' OR u.usuario like \'%'.$this->input['string'].'%\')');
        }

        /* Total sem paginação */
        $total = count($auxiliaresModel->usuarios('result', $this->filtros));
        $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

        $inicio = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
        $fim = Config::read('APP_PERPAGE');

        $usuarios = $auxiliaresModel->usuarios('result', $this->filtros, array($inicio, $fim));
        $total_tela = count($usuarios);

        if (!empty($usuarios)) {
            foreach ($usuarios as &$usuario) {
                $usuario['permite_excluir'] = true;
            }
        }

        $this->data['resultado'] = $usuarios;
        $this->data['paginacao'] = Core::montaPaginacao(
            true,
            $total_tela,
            $total,
            $num_paginas,
            $pagina,
            '/auxiliares/usuarios/pagina',
            $_SERVER['QUERY_STRING']
        );

        return $this->data;
    }

    public function form($id = null)
    {
        $auxiliaresModel = new Auxiliares();
        $setorModel = new setorModel();
        $perfilModel = new perfilModel();
        $usuarios = array();

        $companyObj = new \Lidere\Modules\TI\Models\Company();
        
        if ( !empty($id) ) {
            $usuarios = $auxiliaresModel->usuarios(
                'row',
                array(
                    'u.id' => ' = '.$id
                )
            );

            if(!empty($usuarios)){
                $usuarios['arquivos'] = UsuarioContratoModel::where('usuario_id', $usuarios['id'])->get();
                if(!empty($usuarios['arquivos'])){
                    foreach ($usuarios['arquivos'] as &$arq) {
                        $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['usuario_id'].'!'.$arq['arquivo']);
                    }
                }
            }
        }

        $empresas = Empresa::whereSituacao('ativo')
                           ->get();
        $empresas = !empty($empresas) ? $empresas->toArray() : array();
        if (!empty($empresas)) {
            foreach ($empresas as &$empresa) {
                $empresa['modulos'] = Core::modulos(null, $id, $empresa['id'], true, 'A');
            }
        }

        $this->data['perfis'] = $perfilModel->perfis('result');
        $this->data['companies'] = $companyObj->getCompanies();
        $this->data['setores'] = $setorModel->setores('result', array('s.situacao = ' => "'ativo'"));
        $this->data['exibe_usuario_erp'] = Core::parametro('portal_exibe_usuario_erp', 'Não');
        $this->data['exibe_select_cliente_erp'] = Core::parametro('portal_exibe_select_cliente_erp', 'Não');
        $this->data['empresas']  = $empresas;
        $this->data['registro'] = $usuarios;
        $this->data['permissions'] = Core::permissions($empresas);

        return $this->data;
    }

    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;

        $aplicacaoObj = new Aplicacao();
        if (!empty($this->input['senha'])) {
            $this->input['senha'] = Core::geraSenha($this->input['senha']);
        }
        $this->input['data_criacao'] = Core::now();

        if(!empty($this->input['setor_id'])){
            $setor_id = $this->input['setor_id'];
        }
        unset($this->input['setor_id']);

        $cliente_erp_id = null;
        $cliente_erp_cod_cli = null;
        $cliente_erp_descricao = null;

        //var_dump($this->input['cliente_erp']);die;
        if (isset($this->input['cliente_erp'])) {
            if (!empty($this->input['cliente_erp'])) {
                list($cliente_erp_id, $cliente_erp_cod_cli, $cliente_erp_descricao) = explode('#', $this->input['cliente_erp']);
            }
            unset($this->input['cliente_erp']);
        }

        $company_id = null;
        if (!empty($this->input['company_id'])) {
            $company_id = $this->input['company_id'];
        }
        unset($this->input['company_id']);


        if (empty($this->input['perfil_id'])) {
           $this->input['perfil_id'] = null;
        }
        
        $usuario_id = $aplicacaoObj->insert('tusuarios', $this->input);
        if (!empty($usuario_id) && !empty($cliente_erp_id)) {
            $aplicacaoObj->insert(
                'tusuarios_clientes',
                array(
                    'usuario_id' => $usuario_id,
                    'cliente_erp_id' => $cliente_erp_id,
                    'cliente_erp_cod_cli' => $cliente_erp_cod_cli,
                    'cliente_erp_descricao' => $cliente_erp_descricao
                )
            );
        }

        if(!empty($setor_id)){
            $vinculo['setor_id'] = $setor_id;
            $vinculo['usuario_id'] = $usuario_id;
            $vinc = $aplicacaoObj->insert('tusuarios_setor', $vinculo);
        }

        if (!empty($usuario_id) && !empty($company_id)) {
            $aplicacaoObj->insert(
                'tusuarios_company',
                array(
                    'usuario_id' => $usuario_id,
                    'company_id' => $company_id
                )
            );
        }

        return $usuario_id;
    }

    public function edit($files = false)
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

        if (empty($this->input['senha'])) {
            unset($this->input['senha']);
        } else {
            $this->input['senha'] = Core::geraSenha($this->input['senha']);
        }

        if (empty($this->input['ad'])) {
            $this->input['ad'] = '0';
        }

        if (empty($this->input['perfil_id'])) {
           $this->input['perfil_id'] = null;
        }

        if(!empty($this->input['setor_id'])){
            $setor_id = $this->input['setor_id'];
        }
        unset($this->input['setor_id']);

        $cliente_erp_id = null;
        $cliente_erp_cod_cli = null;
        $cliente_erp_descricao = null;
        if (isset($this->input['cliente_erp'])) {
            if (!empty($this->input['cliente_erp'])) {
                list($cliente_erp_id, $cliente_erp_cod_cli, $cliente_erp_descricao) = explode('#', $this->input['cliente_erp']);
            }
            unset($this->input['cliente_erp']);
        }

        $this->input['data_edicao'] = Core::now();

        $company_id = null;
        if (!empty($this->input['company_id'])) {
            $company_id = $this->input['company_id'];
        }
        unset($this->input['company_id']);

        $updated = $aplicacaoObj->update('tusuarios', $id, $this->input);

        $aplicacaoObj->deleteByColumn('tmodulos_usuarios', array('usuario_id' => $id));
        if (!empty($modulos)) {
            foreach ($modulos as $module => $permissao) {
                $sub = null;
                list($empr, $mod, $sub) = explode('#', $module);
                $relation = array();
                $relation['usuario_id'] = $id;
                $relation['modulo_id'] = $sub != null ? $sub : $mod;
                $relation['permissao'] = $permissao;
                $relation['empresa_empr_id'] = $empr;

                $aplicacaoObj->insert('tmodulos_usuarios', $relation);
            }
        }

        if (!empty($this->input['cliente_erp'])) {
            $aplicacaoObj->deleteByColumn('tusuarios_clientes', array('usuario_id' => $id));

            $relation = array();
            $relation['usuario_id'] = $id;
            $relation['cliente_erp_id'] = $cliente_erp_id;
            $relation['cliente_erp_cod_cli'] = $cliente_erp_cod_cli;
            $relation['cliente_erp_descricao'] = $cliente_erp_descricao;

            $aplicacaoObj->insert('tusuarios_clientes', $relation);
        }


        if (!empty($setor_id)){
            $aplicacaoObj->deleteByColumn('tusuarios_setor', array('usuario_id' => $id));
            $vinculo['setor_id'] = $setor_id;
            $vinculo['usuario_id'] = $id;
            $vinc = $aplicacaoObj->insert('tusuarios_setor', $vinculo);
        }


        if(!empty($files)){

            $file_ary = array();
            $file_count = count($files['files']['name']);
            $file_keys = array_keys($files['files']);

            for ($i=0; $i<$file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $files['files'][$key][$i];
                }
            }

            foreach ($file_ary as $file) {
                $k = 0;
                if ( $file['size'] > 0 && $file['error'] === 0 ) {
                    $ins_file['usuario_id']  = $id;
                    $ins_file['tipo']          = $file['type'];
                    $ins_file['arquivo']       = $k.$id."-".$file['name'];
                    move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'usuario_contratos'.DS.$k.$id."-".$file['name']);
                    $vinc = $aplicacaoObj->insert('tusuario_contrato', $ins_file);
                    $k++;
                }

            }
        }

        if (!empty($company_id)) {
            $aplicacaoObj->deleteByColumn('tusuarios_company', array('usuario_id' => $id));

            $relation = array();
            $relation['usuario_id'] = $id;
            $relation['company_id'] = $company_id;

            $aplicacaoObj->insert('tusuarios_company', $relation);
        }

        return $updated;
    }
}
