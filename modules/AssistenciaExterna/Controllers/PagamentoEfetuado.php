<?php

namespace Lidere\Modules\AssistenciaExterna\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\AssistenciaExterna\Models\TPagamentoArquivo;

/**
 * PagamentoEfetuado
 *
 * @package Lidere\Modules
 * @subpackage PagamentoEfetuado\Controllers
 * @author Humberto Viezzer de Carvalho
 * @copyright 2019 Lidere Sistemas
 */
class PagamentoEfetuado extends Controller
{
    public $url = 'assistencia-externa/pagamento-efetuado';

    public function pagina($pagina = 1)
    {
        Assets::add('/assets/js/pagamentoEfetuado.js', 'AssistenciaExterna');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }

    public function delete()
    {

        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Pagamento removido com sucesso pelo usuário ' . $this->usuario['id'] . ' - ' . $this->usuario['nome'] . '.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Pagamento removido com sucesso!');
            $this->app->redirect('/' . $this->modulo['url']);
        } else {
            $this->app->flash('error', 'Não foi possível remover o pagamento! ');
            $this->app->redirect($this->data['voltar']);
        }
    }

    public function imprimirPagamento($id = 0)
    {


        $this->app->render(
            'imprimir-pagamento.html.twig',
            array(
                'data' => $this->app->service->imprimirPagamento($id)
            )
        );
    }

    public function autorizar()
    {
        $return = $this->app->service->autorizar();

        if (!empty($return)) {

            //EMAIL
            $dados['texto'] = 'Olá ' . $return['assistencia_externa']['nome'] .'.<br>' ; 
            $dados['texto'] .= ' O pagamento ' . $return['nroPagamento'] . ' dos serviços de assistência técnica foi Liberado.<br>';
            $dados['texto'] .= ' Por favor faça o upload da sua nota fiscal e boleto de prestação de serviços, utilizando o menu "Autorização de Pagamentos".';
            $destinatarios = $return['assistencia_externa']['email'];
            $this->enviaEmailPagamento($dados, 'Pagamento Liberado - '.$return['nroPagamento'], $destinatarios);
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    private function enviaEmailPagamento($dados, $titulo, $destinatarios, $anexos = false)
    {
        $tsys_empresa_id = !empty($_SESSION['empresa']['id'])
            ? $_SESSION['empresa']['id']
            : null;

        $send = false;

        $titulo = 'Portal Resfri Ar | ' . $titulo;

        $mensagem = file_get_contents(APP_ROOT . 'src' . DS . 'Resources' . DS . 'views' . DS . 'emails' . DS . 'ordemPagLiberado.html');

        $mensagem = str_replace('%mensagem%', $dados['texto'], $mensagem);

        $usuario = Core::retornaEmails($destinatarios);
        $bcc = false;

        Core::insereFilaEnvioEmail($titulo, $mensagem, $usuario, $bcc, 'portal', $anexos );
    }

    public function anexarNFe()
    {
        $dados = array();

        $mensagens = $this->app->service->anexarNFe($_FILES);


        if (!empty($mensagens['success'])) {
            $success = [];
            if (!empty($mensagens['success']['pagamentoEfetuado'])) {
                $success[] = $mensagens['success']['pagamentoEfetuado'];
                unset($mensagens['success']['pagamentoEfetuado']);
            }

            foreach ($mensagens['success'] as $mensagem) {
                $success[] = is_array($mensagem) ? implode('<br/>', $mensagem) : $mensagem;
            }

            $dados['texto'] = 'A assistência técnica externa - '.$mensagens['retorno']['assistencia_razao_social'].' adicionou o anexo da NF-e de serviço no pagamento '.$mensagens['retorno']['nroPagamento'].'.';

            $destinatarios = Core::parametro('assistencia_destinatarios_email_nfe_pagamento');

            $this->enviaEmailPagamento($dados,'Novo Anexo NF-e Pagamento - '.$mensagens['retorno']['nroPagamento'],$destinatarios,$mensagens['retorno']['anexos']);
        }

        if (!empty($mensagens['error'])) {
            $error = [];
            if (!empty($mensagens['error']['pagamentoEfetuado'])) {
                $error[] = $mensagens['error']['pagamentoEfetuado'];
                unset($mensagens['error']['pagamentoEfetuado']);
            }
            foreach ($mensagens['error'] as $mensagem) {
                $error[] = implode('<br/>', $mensagem);
            }
        }

        return $this->redirect('/' . $this->url);
    }

    public function retornaAnexos()
    {
        $return = $this->app->service->retornaAnexos();


        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function download($linkParameter)
    {

        $link = base64_decode($linkParameter);


        try {
            list($time, $id, $pagamento_id, $name) = explode('!', $link);
        } catch (\ErrorException $e) {
            echo utf8_decode("Operação inválida - " . $e->getMessage());
            die();
        }

        $file = TPagamentoArquivo::find($id)->toArray();


        $file2 = APP_ROOT . 'public' . DS . 'arquivos' . DS. 'notas_ate' .DS. $file['arquivo'];
        $fh = fopen($file2, 'rb');

        $stream = stream_get_contents($fh);

        if ($file['pagamento_id'] != $pagamento_id) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response['Content-Type'] = 'application/force-download';
            $response['Content-Type'] = 'application/octet-stream';
            $response['Content-Type'] = 'application/download';
            $response['Content-Description'] = 'File Transfer';
            $response['Content-Transfer-Encoding'] = 'binary';
            $response['Content-Disposition'] = 'attachment; filename="' . basename($file2) . '"';
            $response['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';
            $response['Expires'] = '0';
            $response['Pragma'] = 'public';
            $response->body($stream);
        }
    }
}
