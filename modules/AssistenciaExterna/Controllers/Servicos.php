<?php

namespace Lidere\Modules\AssistenciaExterna\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\AssistenciaExterna\Services\Servicos as ServicosService;

/**
 * Servicos
 *
 * @package Lidere\Modules
 * @subpackage Servicos\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Servicos extends Controller
{
    public $url = 'assistencia-externa/servicos';

    public function pagina($pagina = 1)
    {

        ///Assets::add('/assets/js/categoria.js', 'Comercial');

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
                'Serviço '.$this->app->filtros['descricao'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Serviço <strong>'.$this->app->filtros['descricao'].'</strong> incluído com sucesso!');
            $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
        }else{
            $this->app->flash('error', 'Não foi possível criar o serviço <strong>'.$this->app->filtros['descricao'].'</strong>! ');
            $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
        }

    }

    public function edit()
    {

        $editou = $this->app->service->edit();
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Serviço '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Serviço <strong>'.$this->app->filtros['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{
            $this->app->flash('error', 'Não foi possível alterar o serviço <strong>'.$this->app->filtros['descricao'].'</strong>! ');
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function delete()
    {

        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Serviço removido com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Serviço removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{
            $this->app->flash('error', 'Não foi possível remover o serviço! ');
            $this->app->redirect($this->data['voltar']);
        }

    }
}
