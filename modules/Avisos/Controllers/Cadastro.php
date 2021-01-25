<?php

namespace Lidere\Modules\Avisos\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\Avisos\Services\Cadastro as CadastroService;
use Lidere\Modules\Avisos\Models\TAvisosArquivos as TAvisosArquivos;

/**
 * Cadastro
 *
 * @package Lidere\Modules
 * @subpackage Cadastro\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Cadastro extends Controller
{
    public $url = 'avisos/cadastro';

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

        Assets::add('/javascripts/base/redactor/redactor.css');
        Assets::add('/javascripts/base/redactor/redactor.js');
        Assets::add('/assets/js/cadastro.js', 'Avisos');

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    public function add()
    {

        $concorrente_id = $this->app->service->add($_FILES);
        if (!empty($concorrente_id)) {
            Core::insereLog(
                $this->modulo['url'],
                'Aviso '.$this->app->filtros['descricao'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Aviso <strong>'.$this->app->filtros['descricao'].'</strong> incluído com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível criar o aviso <strong>'.$this->app->filtros['descricao'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function edit()
    {   

        $editou = $this->app->service->edit($_FILES);
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Aviso '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Aviso <strong>'.$this->app->filtros['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível alterar o aviso <strong>'.$this->app->filtros['descricao'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function delete()
    {

        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Aviso removido com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Aviso removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível remover o aviso! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function download($link) {


        $link = base64_decode($link);

        try {
            list($time, $id, $aviso_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }


        $file = TAvisosArquivos::find($id)->toArray();

        if ( $file['aviso_id'] != $aviso_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'avisos_arquivos'.DS.$file['arquivo']));
        }

    }


    public function deleteArquivo()
    {

        $id = $this->app->request()->post('id');
        if (!empty($id)) {
            TAvisosArquivos::whereId($id)->delete();
            $this->app->flash('success', 'Arquivo excluído com sucesso!');
        } else {
            $this->app->flash('error', 'Problema ao excluir o arquivo!');
        }
        $this->app->redirect($this->app->request->getReferer());
    }
}