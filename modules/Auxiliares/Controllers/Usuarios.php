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
namespace Lidere\Modules\Auxiliares\Controllers;

use Lidere\Config;
use Lidere\Core;
use Lidere\Assets;
use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Modules\Erp\Models\Erp;
use Lidere\Modules\Auxiliares\Models\UsuarioContrato as UsuarioContratoModel;

/**
 * Usuarios
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Controllers\Usuarios
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Usuarios extends Controller
{
    protected $url = 'auxiliares/usuarios';

    public function pagina($pagina = 1)
    {
        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }

    public function form($id = null)
    {
        Assets::add('/assets/js/usuarios.js', 'Auxiliares');

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    public function add()
    {
        $rules = array(
            'nome' => 'required',
            'usuario' => 'required|unique:default.tusuarios,usuario',
            'situacao' => 'required',
            'email' => 'required|email'
        );
        $messages = array(
            'nome.required' => 'Nome obrigatório',
            'usuario.required' => 'Usuário obrigatório',
            'usuario.unique' => 'Usuário já existe',
            'situacao.required' => 'Situação obrigatório',
            'email.required' => 'Email obrigatório',
            'email.email' => 'Email obrigatório'
        );

        $usuario_id = null;
        if ($this->validate($rules, $this->app->service->input, $messages)) {
            $usuario_id = $this->app->service->add();

            if (!empty($usuario_id)) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Usuário '.$this->app->service->input['nome'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash('success', 'Usuário <strong>'.$this->app->service->input['nome'].'</strong> incluído com sucesso!');
                $this->app->redirect('/'.$this->modulo['url'].'/editar/'.$usuario_id);
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível criar o usuário '.$this->app->service->input['nome'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash('error', 'Não foi possível criar o usuário <strong>'.$this->app->service->input['nome'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }
    }

    public function edit()
    {
        if (!empty($this->app->service->edit($_FILES))) {
            Core::insereLog(
                $this->modulo['url'],
                'Usuário '.$this->app->service->input['nome'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Usuário <strong>'.$this->app->service->input['usuario'].'</strong> alterado com sucesso!');
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível alterar o usuário '.$this->app->service->input['nome'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('error', 'Não foi possível alterar o usuário <strong>'.$this->app->service->input['usuario'].'</strong>!');
        }

        $this->app->redirect($this->data['voltar']);
    }

    public function delete()
    {
        $aplicacaoObj = new Aplicacao();

        $id = $this->app->request()->post('id');
        if (!empty($id)) {
            // if ( Core::validaExclusao('usuario', $id) ) {
            $aplicacaoObj->deleteByColumn('tlogs', array('usuario_id' => $id));
            $aplicacaoObj->delete('tusuarios', $id);
            $this->app->flash('success', 'Usuário excluído com sucesso!');
            Core::insereLog(
                $this->modulo['url'],
                'Usuário '.$id.' excluído com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            // }
        } else {
            $this->app->flash('error', 'Problema ao excluir o usuário!');
        }
        $this->app->redirect($this->app->request->getReferer());
    }

    public function download($link) {


        $link = base64_decode($link);



        try {
            list($time, $id, $usuario_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }


        $file = UsuarioContratoModel::find($id)->toArray();

        if ( $file['usuario_id'] != $usuario_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'usuario_contratos'.DS.$file['arquivo']));
        }

    }


    public function deleteArquivo()
    {
        $aplicacaoObj = new Aplicacao();

        $id = $this->app->request()->post('id');
        if (!empty($id)) {
            // if ( Core::validaExclusao('usuario', $id) ) {
            $aplicacaoObj->delete('tusuario_contrato', $id);
            $this->app->flash('success', 'Arquivo do usuário excluído com sucesso!');
            // }
        } else {
            $this->app->flash('error', 'Problema ao excluir o arquivo do usuário!');
        }
        $this->app->redirect($this->app->request->getReferer());
    }

}
