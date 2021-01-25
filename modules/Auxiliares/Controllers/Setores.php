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
 * VinculoVendedor
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Controllers\VinculoVendedor
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class VinculoVendedor extends Controller
{
    protected $url = 'auxiliares/vinculo-vendedor';

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
       // Assets::add('/assets/js/setores.js', 'Auxiliares');

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
            'setor' => 'required',
            'situacao' => 'required'
        );
        $messages = array(
            'setor.required' => 'Setor obrigatório',
            'situacao.required' => 'Situação obrigatório'
        );

        $usuario_id = null;
        if ($this->validate($rules, $this->app->service->input, $messages)) {
            $usuario_id = $this->app->service->add();

            if (!empty($usuario_id)) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Setor '.$this->app->service->input['setor'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash('success', 'Setor <strong>'.$this->app->service->input['setor'].'</strong> incluído com sucesso!');
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível criar o setor '.$this->app->service->input['setor'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash('error', 'Não foi possível criar o setor <strong>'.$this->app->service->input['setor'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }
    }

    public function edit()
    {
        if (!empty($this->app->service->edit())) {
            Core::insereLog(
                $this->modulo['url'],
                'Setor '.$this->app->service->input['setor'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Setor <strong>'.$this->app->service->input['usuario'].'</strong> alterado com sucesso!');
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível alterar o setor '.$this->app->service->input['setor'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('error', 'Não foi possível alterar o setor <strong>'.$this->app->service->input['usuario'].'</strong>!');
        }

        $this->app->redirect('/auxiliares/setores');
    }

    public function delete()
    {
        $aplicacaoObj = new Aplicacao();

        $id = $this->app->request()->post('id');
        if (!empty($id)) {
            $aplicacaoObj->delete('tsetores', $id);
            $this->app->flash('success', 'Setor excluído com sucesso!');
            Core::insereLog(
                $this->modulo['url'],
                'Setor '.$id.' excluído com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
        } else {
            $this->app->flash('error', 'Problema ao excluir o setor!');
        }
        $this->app->redirect($this->app->request->getReferer());
    }
}
