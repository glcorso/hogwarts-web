<?php

namespace Lidere\Modules\Comercial\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\Comercial\Services\RelatorioVisitasMontadoras as RelatorioVisitasService;
use Lidere\Modules\Comercial\Models\RelatorioVisitaArquivos as modelRelatorioVisitaArquivos;

/**
 * RelatorioVisitasMontadoras
 *
 * @package Lidere\Modules
 * @subpackage RelatorioVisitasMontadoras\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class RelatorioVisitasMontadoras extends Controller
{
    public $url = 'comercial-montadoras/relatorio-visitas-montadoras';

    public function pagina($pagina = 1)
    {
        Assets::add('/assets/js/relatorioVisitasIndex.js', 'Comercial');
        
        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }


    public function form($id = null)
    {

        Assets::add('/assets/js/relatorioVisitasForm.js', 'Comercial');
        Assets::add('/assets/js/validaCPF_CNPJ.js', 'Assistencia');

        Assets::add('/libs/ckeditor/ckeditor.js');


        $data = $this->app->service->form($id);
        if(!empty($id) && empty($data)){
            $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
        }
        $this->app->render(
            'form.html.twig',
            array(
                'data' => $data
            )
        );
    }

    public function add()
    {

        $relatorio_id = $this->app->service->add($_FILES);

        if (!empty($relatorio_id)) {

            Core::insereLog(
                $this->modulo['url'],
                'Relatório de Visita '.$relatorio_id->id.' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Relatório de Visita <strong>'.$relatorio_id->id.'</strong> incluído com sucesso!');
            $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
        }else{ 
            $this->app->flash('error', 'Não foi possível criar o relatório de visitas <strong>'.$this->app->filtros['descricao'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function edit()
    {


        $post = $this->app->request()->post();
        $editou = $this->app->service->edit($_FILES);
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Relatório de Visitas '.$post['id'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            

            $this->app->flash('success', 'Relatório de Visitas <strong>'.$post['id'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível alterar o relatório de visita <strong>'.$post['id'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function delete()
    {

        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Relatório de Visita removido com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Relatório de visita removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível remover o relatório de visitas! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function download($link) {


        $link = base64_decode($link);  

        try {
            list($time, $id, $relatorio_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }

        $file = modelRelatorioVisitaArquivos::find($id)->toArray();

        if ( $file['relatorio_id'] != $relatorio_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'relatorio_visitas'.DS.$file['arquivo']));
        }

    }

    public function excluirArquivo()
    {   
        $post = $this->app->request()->post();
        $deletou = $this->app->service->excluirArquivo();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Arquivo do relatório de visitas removido com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Arquivo do relatório de visitas removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url'].'/editar/'.$post['relatorio_id']);
        }else{ 
            $this->app->flash('error', 'Não foi possível remover o Arquivo do relatório de Visitas! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function imprimir($ids)
    {
      
        $this->app->render(
            'imprimir.html.twig',
            array(
                'data' => $this->app->service->imprimir($ids)
            )
        );
    }

}