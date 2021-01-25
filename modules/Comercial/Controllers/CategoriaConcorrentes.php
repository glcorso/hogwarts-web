<?php

namespace Lidere\Modules\Comercial\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\Comercial\Services\CategoriaConcorrentes as CategoriaConcorrentesService;

/**
 * CategoriaConcorrentes
 *
 * @package Lidere\Modules
 * @subpackage CategoriaConcorrentes\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class CategoriaConcorrentes extends Controller
{
    public $url = 'comercial/cadastros/categoria-concorrentes';

    public function pagina($pagina = 1)
    {
       
        Assets::add('/assets/js/categoria-concorrentes.js', 'Comercial');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }


     public function form($id = null)
    {

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    public function add()
    {   

        $concorrente_id = $this->app->service->add();
        if (!empty($concorrente_id)) {
            Core::insereLog(
                $this->modulo['url'],
                'Categoria de Concorrentes '.$this->app->filtros['descricao'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Categoria de Concorrentes <strong>'.$this->app->filtros['descricao'].'</strong> incluído com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível criar a categoria de concorrentes <strong>'.$this->app->filtros['descricao'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function edit()
    {

        $editou = $this->app->service->edit();
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Categoria de Concorrentes '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Categoria de Concorrentes <strong>'.$this->app->filtros['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível alterar a categoria de concorrentes <strong>'.$this->app->filtros['descricao'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function delete()
    {

        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Categoria de Concorrentes removida com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Categoria de Concorrentes removida com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível remover a categoria de concorrente! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }
}