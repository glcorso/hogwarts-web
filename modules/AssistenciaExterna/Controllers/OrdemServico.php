<?php

namespace Lidere\Modules\AssistenciaExterna\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\AssistenciaExterna\Services\OrdemServico as OrdemServicoService;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoArquivos as modelOrdemServicoArquivos;

/**
 * OrdemServico
 *
 * @package Lidere\Modules
 * @subpackage OrdemServico\Controllers
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class OrdemServico extends Controller
{
    public $url = 'assistencia-externa/ordem-servico';

    public function pagina($pagina = 1)
    {

        Assets::add('/assets/js/ordemServicoIndex.js', 'AssistenciaExterna');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }


    public function form($id = null)
    {
       // Assets::add('/javascripts/base/jsQR/dist/jsQR.js');

        Assets::add('/javascripts/base/instascan.min.2.js');
        Assets::add('/assets/js/validaCPF_CNPJ.js', 'Assistencia');
        Assets::add('/assets/js/ordemServicoForm.js', 'AssistenciaExterna');

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

        $ordem_servico = $this->app->service->add($_FILES);
        if (!empty($ordem_servico)) {
            Core::insereLog(
                $this->modulo['url'],
                'Ordem de serviço '.$this->app->filtros['descricao'].' criada com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Ordem de serviço <strong>'.$this->app->filtros['descricao'].'</strong> incluída com sucesso!');
            $this->app->redirect('/'."assistencia-externa/ordem-servico/imprimir-atendimento/{$ordem_servico->id}");
        }else{
            $this->app->flash('error', 'Não foi possível criar a categoria <strong>'.$this->app->filtros['descricao'].'</strong>! ');
            $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
        }

    }

    public function edit()
    {

        $editou = $this->app->service->edit($_FILES);
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Ordem de serviço '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Ordem de serviço <strong>'.$this->app->filtros['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{
            $this->app->flash('error', 'Não foi possível alterar a categoria <strong>'.$this->app->filtros['descricao'].'</strong>! ');
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function editServicos()
    {

        $editou = $this->app->service->editServicos($_FILES);
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Ordem de serviço '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $ordem_servico = $this->app->service->select();
            
            if($ordem_servico['ordem_servico']['garantia'] == 'S'){
                $this->enviaEmailServico('aprovacao', $ordem_servico);
            }

            $this->app->flash('success', 'Ordem de serviço <strong>'.$this->app->filtros['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{
            $this->app->flash('error', 'Não foi possível alterar a categoria <strong>'.$this->app->filtros['descricao'].'</strong>! ');
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function aprovarServicos()
    {

        $editou = $this->app->service->aprovarServicos();
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Ordem de serviço '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $ordem_servico = $this->app->service->select();

            $this->enviaEmailServico('aprovado', $ordem_servico);

            $this->app->flash('success', 'Ordem de serviço <strong>'.$this->app->filtros['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{
            $this->app->flash('error', 'Não foi possível aprovar o serviço <strong>'.$this->app->filtros['descricao'].'</strong>! ');
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function reprovarServicos()
    {
        $post = $this->app->request->post();
        $editou = $this->app->service->reprovarServicos($post['id']);
        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Ordem de serviço '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $ordem_servico = $this->app->service->select();

            $this->enviaEmailServico('rejeitado', $ordem_servico);

        }

        $response->body(json_encode($editou));

    }

    public function concluirServicos()
    {

       // echo "<pre>";
       // var_dump($_POST);die;

        $editou = $this->app->service->concluirServicos($_FILES);
        if ($editou) {
            Core::insereLog(
                $this->modulo['url'],
                'Ordem de serviço '.$this->app->filtros['descricao'].' alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Ordem de serviço <strong>'.$this->app->filtros['descricao'].'</strong> alterado com sucesso!');
            $this->app->redirect('/'.'assistencia-externa/ordem-servico/imprimir-conclusao/'.$_POST['id']);
        }else{
            $this->app->flash('error', 'Não foi possível alterar a categoria <strong>'.$this->app->filtros['descricao'].'</strong>! ');
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function delete()
    {

        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Ordem de serviço removida com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Ordem de serviço removida com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{
            $this->app->flash('error', 'Não foi possível remover a categoria! ');
            $this->app->redirect($this->data['voltar']);
        }

    }

    public function download($link) {


        $link = base64_decode($link);



        try {
            list($time, $id, $ordem_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }


        $file = modelOrdemServicoArquivos::find($id)->toArray();



        if ( $file['ordem_id'] != $ordem_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica_ex'.DS.$file['arquivo']));
        }

    }


    public function imprimirAtendimento($id)
    {
        Assets::add('/javascripts/base/signaturePad/css/ie9.css');
        Assets::add('/javascripts/base/html2canvas.js');
        Assets::add('/assets/js/domToImage.js', 'AssistenciaExterna');
        Assets::add('/assets/js/ordemServicoImprimir.js', 'AssistenciaExterna');

        $this->app->render(
            'imprimir-atendimento.html.twig',
            array(
                'data' => $this->app->service->imprimirAtendimento($id)
            )
        );
    }

    public function imprimirConclusao($id)
    {

        Assets::add('/javascripts/base/signaturePad/css/ie9.css');
        Assets::add('/javascripts/base/html2canvas.js');
        Assets::add('/assets/js/ordemServicoImprimir.js', 'AssistenciaExterna');

        $this->app->render(
            'imprimir-conclusao.html.twig',
            array(
                'data' => $this->app->service->imprimirAtendimento($id)
            )
        );
    }

    private function enviaEmailServico($status, $ordem_servico)
    {

        if ($status == 'aprovacao') {
            $titulo = 'Portal Resfri Ar | Assistência Técnica - Ordem de Serviço Aguardando Aprovação';
            $conteudo =  "Nova Ordem de Serviço {$ordem_servico['ordem_servico']['num_ordem']} para ser analisada.";
            $header = 'Assistência Técnica';
            $email_to = Core::retornaEmails(Core::parametro('assistencia_tecnica_email_aprovacao'));
        } elseif ($status == 'aprovado') {
            $titulo = 'Portal Resfri Ar | Assistência Técnica - Ordem de Serviço Aprovada';
            $conteudo =  "A Ordem de Serviço {$ordem_servico['ordem_servico']['num_ordem']} foi aprovada.";
            $header = $ordem_servico['usuario']['cliente_erp_descricao'];
            $email_to = Core::retornaEmails($ordem_servico['usuario']['email']);
        } elseif ($status == 'rejeitado') {
            $titulo = 'Portal Resfri Ar | Assistência Técnica - Ordem de Serviço Rejeitada';
            $conteudo =  "A Ordem de Serviço {$ordem_servico['ordem_servico']['num_ordem']} foi rejeitada.";
            $header = $ordem_servico['usuario']['cliente_erp_descricao'];
            $email_to = Core::retornaEmails($ordem_servico['usuario']['email']);
        }

        $mensagem = file_get_contents(APP_ROOT . 'src' . DS . 'Resources' . DS . 'views' . DS . 'emails' . DS . 'ordemServico.html');
        $mensagem = str_replace('%mensagem%', $conteudo, $mensagem);
        $mensagem = str_replace('%nome_revendedor%', $header, $mensagem);


        Core::insereFilaEnvioEmail($titulo, $mensagem, $email_to, '', 'portal');
    }

}
