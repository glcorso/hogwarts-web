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
namespace Lidere\Modules\Assistencia\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Assets;
use Lidere\Core;

/**
 * Motivos
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Controllers\Motivos
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Motivos extends Controller {

    public $url = 'assistencia-tecnica/motivos';

    public function index()
    {
  

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list()
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

        $motivo_id = $this->app->service->add();
        if (!empty($motivo_id)) {
            Core::insereLog(
                $this->modulo['url'],
                'Motivo '.$this->app->service->input['descricao'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Motivo <strong>'.$this->app->service->input['descricao'].'</strong> incluído com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível criar o motivo <strong>'.$this->app->service->input['descricao'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function edit()
    {

        $editou = $this->app->service->edit();
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Motivo '.$this->app->service->input['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Motivo <strong>'.$this->app->service->input['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível alterar o motivo <strong>'.$this->app->service->input['descricao'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function delete()
    {

        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Motivo removido com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Motivo removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível remover o motivo! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }
}
