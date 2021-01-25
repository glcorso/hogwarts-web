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
use Lidere\Upload;
use Lidere\Modules\Assistencia\Models\Atendimento as atendimentoModel;

/**
 * Atendimento
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Controllers\Atendimentos
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Atendimento extends Controller {

    public $url = 'assistencia-tecnica/atendimento';

    public function index($id = false)
    {
       // $data['modulo'] = $this->modulo;
        //$data['permissao'] = $this->permissao;

        Assets::add('/assets/js/atendimento.js', 'Assistencia');
        Assets::add('/assets/js/validaCPF_CNPJ.js', 'Assistencia');

        //echo "<pre>";
        //var_dump($_SERVER);die;

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->index($id)
            )
        );
    }

    public function add()
    {

    
        $chamado_id = $this->app->service->add($_FILES);

        if (!empty($chamado_id)) {
            Core::insereLog(
                $this->modulo['url'],
                'Chamado de Assistência Técnica '.$this->app->service->input['protocolo'].' criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong> incluído com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível criar o Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function edit()
    {

        $chamado_id = $this->app->service->edit($_FILES);

        if (!empty($chamado_id)) {
            Core::insereLog(
                $this->modulo['url'],
                'Chamado de Assistência Técnica '.$this->app->service->input['protocolo'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível alterar o Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function download($link) {

        $atendimentoModel = new atendimentoModel(); 

        $link = base64_decode($link);  

        try {
            list($time, $id, $registro_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }

        $file = $atendimentoModel->getAssistenciaArquivosById($id);

        if ( $file['registro_id'] != $registro_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica'.DS.$file['arquivo']));
        }

    }

    public function downloadLaudo($link) {

        $atendimentoModel = new atendimentoModel(); 

        $link = base64_decode($link);  

        try {
            list($time, $id, $registro_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }

        $file = $atendimentoModel->getAssistenciaArquivosLaudoById($id);

        if ( $file['registro_id'] != $registro_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica'.DS.'laudos'.DS.$file['arquivo']));
        }

    }

    public function excluirArquivo()
    {   
        $post = $this->app->request()->post();
        $deletou = $this->app->service->excluirArquivo();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Arquivo removido com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Arquivo removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url'].'/'.$post['registro_id']);
        }else{ 
            $this->app->flash('error', 'Não foi possível remover o Arquivo! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function detalhes($id = false)
    {
       // $data['modulo'] = $this->modulo;
        //$data['permissao'] = $this->permissao;

        Assets::add('/assets/js/detalhes.js', 'Assistencia');
        Assets::add('/assets/js/validaCPF_CNPJ.js', 'Assistencia');

        

        $this->app->render(
            'detalhes.html.twig',
            array(
                'data' => $this->app->service->detalhes($id)
            )
        );
    }

    public function editDetalhes()
    {

        $chamado_id = $this->app->service->editDetalhes($_FILES);

        if (!empty($chamado_id)) {
            Core::insereLog(
                $this->modulo['url'],
                'Chamado de Assistência Técnica - Detalhes'.$this->app->service->input['protocolo'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong> alterado com sucesso!');
            $this->app->redirect('/assistencia-tecnica/atendimento/detalhes/'.$chamado_id);
        }else{ 
            $this->app->flash('error', 'Não foi possível alterar o Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong>! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function imprimirLaudo($id)
    {

        Assets::add('/assets/js/atendimento.js', 'Assistencia');
        Assets::add('/assets/js/validaCPF_CNPJ.js', 'Assistencia');


        $this->app->render(
            'laudo.html.twig',
            array(
                'data' => $this->app->service->imprimirLaudo($id)
            )
        );
    }

    public function excluirAtendimentoTecnico() {

        $excluiu = $this->app->service->excluirAtendimentoTecnico();

        if ($excluiu) {
            Core::insereLog(
                $this->modulo['url'],
                'Chamado de Assistência Técnica - Detalhes - Atendimento Técnico'.$this->app->service->input['protocolo'].' excluído com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong> alterado com sucesso!');
            $this->app->redirect('/assistencia-tecnica/atendimento/detalhes/'.$this->app->service->input['registro_id']);
        }else{ 
            $this->app->flash('error', 'Não foi possível excluir o atendimento do Chamado de Assistência Técnica <strong>'.$this->app->service->input['protocolo'].'</strong>! ');
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function excluirArquivoLaudo()
    {   
        $post = $this->app->request()->post();
        $deletou = $this->app->service->excluirArquivoLaudo();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Arquivo Laudo removido com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Arquivo do Laudo removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url'].'/detalhes/'.$post['registro_id']);
        }else{ 
            $this->app->flash('error', 'Não foi possível remover o Arquivo do Laudo! '.implode('</br>', $this->errors));
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function imprimirAtendimento($id = false)
    {
       // $data['modulo'] = $this->modulo;
        //$data['permissao'] = $this->permissao;

        Assets::add('/assets/js/atendimento.js', 'Assistencia');
        Assets::add('/assets/js/validaCPF_CNPJ.js', 'Assistencia');

        //echo "<pre>";
        //var_dump($_SERVER);die;

        $this->app->render(
            'imprimir.html.twig',
            array(
                'data' => $this->app->service->index($id)
            )
        );
    }


}
