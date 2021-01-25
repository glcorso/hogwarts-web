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

/**
 * Perfis
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Controllers\Perfis
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Perfis extends Controller
{
    protected $url = 'auxiliares/perfis';

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
        Assets::add('/assets/js/perfis.js', 'Auxiliares');

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
            'perfil' => 'required',
            'situacao' => 'required'
        );
        $messages = array(
            'perfil.required' => 'Perfil obrigatório',
            'situacao.required' => 'Situação obrigatório'
        );

        $perfil_id = null;
        if ($this->validate($rules, $this->app->service->input, $messages)) {
            $perfil_id = $this->app->service->add();

            if (!empty($perfil_id)) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Perfil '.$this->app->service->input['perfil'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash('success', 'Perfil <strong>'.$this->app->service->input['perfil'].'</strong> incluído com sucesso!');
                $this->app->redirect('/'.$this->modulo['url'].'/editar/'.$perfil_id);
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível criar o perfil '.$this->app->service->input['perfil'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash('error', 'Não foi possível criar o perfil <strong>'.$this->app->service->input['perfil'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }
    }

    public function edit()
    {
        if (!empty($this->app->service->edit())) {
            Core::insereLog(
                $this->modulo['url'],
                'Perfil '.$this->app->service->input['perfil'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Perfil <strong>'.$this->app->service->input['usuario'].'</strong> alterado com sucesso!');
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível alterar o perfil '.$this->app->service->input['perfil'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('error', 'Não foi possível alterar o perfil <strong>'.$this->app->service->input['perfil'].'</strong>!');
        }

        $this->app->redirect('/auxiliares/perfis');
    }

    public function delete()
    {
        $aplicacaoObj = new Aplicacao();

        $id = $this->app->request()->post('id');
        if (!empty($id)) {
            $aplicacaoObj->delete('tperfis', $id);
            $this->app->flash('success', 'Perfil excluído com sucesso!');
            Core::insereLog(
                $this->modulo['url'],
                'Perfil '.$id.' excluído com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
        } else {
            $this->app->flash('error', 'Problema ao excluir o perfil!');
        }
        $this->app->redirect($this->app->request->getReferer());
    }
}
