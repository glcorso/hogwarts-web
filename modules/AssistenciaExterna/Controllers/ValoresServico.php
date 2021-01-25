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
namespace Lidere\Modules\AssistenciaExterna\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;

/**
 * Controller Valores por Serviço
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Controllers\ValoresServico
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class ValoresServico extends Controller
{
    /**
     * Rota do modulo
     *
     * @var string
     */
    public $url = 'assistencia-externa/valores-servicos';

    /**
     * Listagem dos registros
     *
     * @param integer $pagina Número da página
     *
     * @return void
     */
    public function pagina($pagina = 1)
    {
        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }

    /**
     * Formulário de cadastro/edição
     *
     * @param integer $id Id do registro
     *
     * @return void
     */
    public function form($id = 0)
    {
        Assets::add('/javascripts/base/jquery.maskMoney.js');
        Assets::add('/assets/js/valorServicoForm.js', 'AssistenciaExterna');

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    /**
     * Rota para cadastrar um novo registro
     *
     * @return void
     */
    public function add()
    {
        $connection = 'oracle_'.$_SESSION['empresa']['id'];
        $descricao = $this->app->service->input['descricao'];
        $rules = array(
            'cod_lista' => 'required|max:10|unique:'.$connection.'.tsdi_assistencia_lista_serv,cod_lista',
            'descricao' => 'required|max:70',
            'sit' => 'required'
        );
        $messages = array(
            'cod_lista.required' => 'Código obrigatório',
            'cod_lista.unique' => 'Código já existe',
            'cod_lista.max' => 'O campo Código deve conter no máximo :max caracteres',
            'descricao.required' => 'Descrição é obrigatória',
            'descricao.max' => 'O campo Descrição deve conter no máximo :max caracteres',
            'sit.required' => 'Situação obrigatório',
        );
        if ($this->validate($rules, $this->app->service->input, $messages)) {
            if ($this->app->service->add()) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Valor por Serviço '.$this->app->filtros['descricao'].
                    ' criado com sucesso pelo usuário '.$this->usuario['id'].
                    ' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash(
                    'success',
                    'Valor por Serviço <strong>'.$this->app->filtros['descricao'].'</strong>'.
                    'incluído com sucesso!'
                );
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            } else {
                $this->app->flash(
                    'error',
                    'Não foi possível criar o valor por serviço <strong>'.
                    $this->app->filtros['descricao'].'</strong>!'
                );
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível alterar o valor por serviço '.$descricao.
                ' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash(
                'error',
                'Não foi possível alterar o valor por serviço <strong>'.
                $descricao.'</strong>! '.implode('</br>', $this->errors)
            );
            $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
        }
    }

    /**
     * Rota para editar um registro
     *
     * @return void
     */
    public function edit()
    {
        $connection = 'oracle_'.$_SESSION['empresa']['id'];
        $id = $this->app->service->input['id'];
        $descricao = $this->app->service->input['descricao'];
        $rules = array(
            'cod_lista' => 'required|max:10|unique:'.$connection.'.tsdi_assistencia_lista_serv,cod_lista,'.$id,
            'descricao' => 'required|max:70',
            'sit' => 'required'
        );
        $messages = array(
            'cod_lista.required' => 'Código obrigatório',
            'cod_lista.unique' => 'Código já existe',
            'cod_lista.max' => 'O campo Código deve conter no máximo :max caracteres',
            'descricao.required' => 'Descrição é obrigatória',
            'descricao.max' => 'O campo Descrição deve conter no máximo :max caracteres',
            'sit.required' => 'Situação obrigatório',
        );

        if ($this->validate($rules, $this->app->service->input, $messages)) {
            if ($this->app->service->edit()) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Valor por Serviço '.$this->app->filtros['descricao'].
                    ' alterado com sucesso pelo usuário '.$this->usuario['id'].
                    ' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash(
                    'success',
                    'Valor por Serviço <strong>'.$this->app->filtros['descricao'].
                    '</strong> alterado com sucesso!'
                );
                $this->app->redirect('/'.$this->modulo['url']);
            } else {
                $this->app->flash(
                    'error',
                    'Não foi possível alterar o valor por serviço <strong>'.
                    $this->app->filtros['descricao'].'</strong>!'
                );
                $this->app->redirect($this->data['voltar']);
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível alterar a serviço '.$descricao.
                ' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash(
                'error',
                'Não foi possível alterar a serviço <strong>'.
                $descricao.'</strong>! '.implode('</br>', $this->errors)
            );
            $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
        }
    }

    /**
     * Rota para excluir um registro
     *
     * @return void
     */
    public function delete()
    {
        if ($this->app->service->delete()) {
            Core::insereLog(
                $this->modulo['url'],
                'Valor por Serviço removido com sucesso pelo usuário '.$this->usuario['id'].
                ' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash(
                'success',
                'Valor por Serviço removido com sucesso!'
            );
            $this->app->redirect('/'.$this->modulo['url']);
        } else {
            $this->app->flash(
                'error',
                'Não foi possível remover o valor por serviço!'
            );
            $this->app->redirect($this->data['voltar']);
        }
    }
}
