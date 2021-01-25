<?php

namespace Lidere\Modules\Empresas\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Models\Aplicacao;
use Lidere\Modules\Empresas\Services\Empresas as EmpresasService;
use Lidere\Modules\Empresas\Models\EmpresaDocumentos as EmpresaDocumentosModel;

/**
 * TaskEmpresass
 *
 * @package Lidere\Modules
 * @subpackage Empresas\Controllers
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Empresas extends Controller
{
    public $url = 'auxiliares/empresas';

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
        Assets::add('/assets/js/empresas.js', 'Empresas');

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    public function add()
    {
        $post = $this->app->request()->post();
        $voltar = $post['voltar'];
        unset($post['voltar']);

        $rules = array(
            'razao_social' => 'required',
            'nome_fantasia' => 'required',
            'dominio' => 'required',
            'diretorio' => 'required',
            'situacao' => 'required',
            'cor_principal' => 'required'
        );
        $messages = array(
            'razao_social' => 'Razão Social obrigatória',
            'nome_fantasia' => 'Nome Fantasia obrigatória',
            'dominio' => 'Domínio do Portal obrigatório',
            'diretorio' => 'Diretório obrigatório',
            'situacao' => 'Situação obrigatória',
            'cor_principal' => 'Cor Principal obrigatória'
        );

        $empresa_id = null;
        if ($this->validate($rules, $post, $messages)) {
            $empresa_id = $this->app->service->add();
            if (!empty($empresa_id)) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Empresa '.$post['razao_social'].' criada com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash('success', 'Empresa <strong>'.$post['razao_social'].'</strong> incluída com sucesso!');
                $this->redirect();
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível criar a empresa '.$post['razao_social'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash('error', 'Não foi possível criar a empresa <strong>'.$post['razao_social'].'</strong>! '.implode('</br>', $this->errors));
            $this->redirect($voltar);
        }
    }

    public function edit()
    {
        if (!empty($this->app->service->edit($_FILES))) {
            $this->app->flash('success', 'Empresa editada com sucesso!');
        } else {
            $this->app->flash('error', 'Ocorreu um problema ao editar a empresa!');
        }
        $this->redirect($this->app->data['voltar']);
    }

    public function delete()
    {
        if (!empty($this->app->service->delete())) {
            $this->app->flash('success', 'Empresa deletada com sucesso!');
        } else {
            $this->app->flash('error', 'Ocorreu um problema ao deletar a empresa!');
        }
        $this->redirect();
    }

    public function download($link) {


        $link = base64_decode($link);



        try {
            list($time, $id, $empresa_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }


        $file = EmpresaDocumentosModel::find($id)->toArray();

        if ( $file['empresa_id'] != $empresa_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'empresa_documentos'.DS.$file['arquivo']));
        }

    }


    public function deleteArquivo()
    {
        $aplicacaoObj = new Aplicacao();

        $id = $this->app->request()->post('id');
        if (!empty($id)) {
            // if ( Core::validaExclusao('usuario', $id) ) {
            $aplicacaoObj->delete('tempresa_documentos', $id);
            $this->app->flash('success', 'Documento da empresa excluído com sucesso!');
            // }
        } else {
            $this->app->flash('error', 'Problema ao excluir o documento da empresa!');
        }
        $this->app->redirect($this->app->request->getReferer());
    }
}
