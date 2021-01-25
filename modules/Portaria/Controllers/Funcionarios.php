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
namespace Lidere\Modules\Portaria\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\Portaria\Models\Funcionario as FuncionarioModel;

/**
 * Controller Veículos
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Portaria\Controllers\Funcionarios
 * @author     William Mascarello <willim.mascarello@lideresistemas.com.br>
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Funcionarios extends Controller
{
    /**
     * Rota do modulo
     *
     * @var string
     */
    public $url = 'portaria/funcionarios';

    /**
     * Listagem dos registros
     *
     * @param integer $pagina Número da página
     *
     * @return void
     */
    public function pagina($pagina = 1)
    {
        $id = 1;
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
    public function form($id = null)
    {
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
        $nome = $this->app->service->input['descricao'];
        $rules = array(
            'codigo' => 'required|max:40',
            'descricao' => 'required|max:80',          
            'situacao' => 'required'
        );
        $messages = array(
            'codigo.required' => 'Código é obrigatório',
            'codigo.max' => 'O campo Código deve conter no máximo :max caracteres.',
            'descricao.required' => 'Nome é obrigatório',
            'descricao.max' => 'O campo Nome deve conter no máximo :max caracteres.',
            'situacao.required' => 'Situação obrigatório',
        );

        if ($this->validate($rules, $this->app->service->input, $messages)) {
            if ($this->app->service->add()) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Funcionário '.$nome.
                    ' criado com sucesso pelo usuário '.$this->usuario['id'].
                    ' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash(
                    'success',
                    'Funcionário <strong>'.$nome.
                    '</strong> incluído com sucesso!'
                );
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            } else {
                $this->app->flash(
                    'error',
                    'Não foi possível criar o Funcionário <strong>'.
                    $nome.'</strong>!'
                );
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível criar o Funcionário '.$nome.
                ' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash(
                'error',
                'Não foi possível criar o Funcionário <strong>'.
                $nome.'</strong>! '.implode('</br>', $this->errors)
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
        $nome = $this->app->service->input['descricao'];
        $rules = array(
            'descricao' => 'required|max:80',
            'situacao' => 'required'
        );
        $messages = array(
            'descricao.required' => 'Nome é obrigatório',
            'descricao.max' => 'O campo Nome deve conter :max caracteres.',
            'situacao.required' => 'Situação obrigatório',
        );

        if ($this->validate($rules, $this->app->service->input, $messages)) {
            if ($this->app->service->edit()) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Funcionário '.$nome.
                    ' alterado com sucesso pelo usuário '.$this->usuario['id'].
                    ' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash(
                    'success',
                    'Funcionário <strong>'.$nome.
                    '</strong> alterado com sucesso!'
                );
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            } else {
                $this->app->flash(
                    'error',
                    'Não foi possível alterar o Funcionário <strong>'.
                    $placa.'</strong>!'
                );
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível alterar o Funcionário '.$placa.
                ' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash(
                'error',
                'Não foi possível alterar o Funcionário <strong>'.
                $placa.'</strong>! '.implode('</br>', $this->errors)
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
        $deletou = $this->app->service->delete();
        if ($deletou) {
            Core::insereLog(
                $this->modulo['url'],
                'Funcionário removido com sucesso pelo usuário '.$this->usuario['id'].
                ' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Funcionário removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        } else {
            $this->app->flash('error', 'Não foi possível remover o Funcionário! ');
            $this->app->redirect($this->data['voltar']);
        }
    }


}
