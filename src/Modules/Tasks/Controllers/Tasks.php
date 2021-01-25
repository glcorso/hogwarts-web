<?php

namespace Lidere\Modules\Tasks\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Assets;
use Lidere\Modules\Tasks\Services\Tasks as TasksService;

/**
 * Tasks
 *
 * @package Lidere\Modules
 * @subpackage Tasks\Controllers
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Tasks extends Controller
{
    public $url = 'auxiliares/tasks';

    public function pagina($pagina = 1)
    {
        $get = $this->app->request()->get();

        $service = new TasksService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            $get
        );

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $service->list($pagina)
            )
        );
    }


    public function form($id = null)
    {
        $service = new TasksService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data
        );

        Assets::add('/assets/js/tasks.js', 'Tasks');

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $service->form($id)
            )
        );
    }

    public function add()
    {
        $post = $this->app->request()->post();

        $voltar = $post['voltar'];
        unset($post['voltar']);

        $rules = array(
            'minute' => 'required',
            'hour' => 'required',
            'day' => 'required',
            'month' => 'required',
            'weekday' => 'required',
            'commonOptions' => 'required',
            'job' => 'required|unique:default.ttasks,job',
            'description' => 'required'
        );
        $messages = array(
            'minute.required' => 'Minuto obrigatório',
            'hour.required' => 'Hora obrigatório',
            'day.required' => 'Dia obrigatório',
            'month.required' => 'Mês obrigatório',
            'weekday.required' => 'Semana obrigatório',
            'commonOptions.required' => 'Comando completo obrigatório',
            'job.required' => 'O job é obrigatório',
            'job.unique' => 'O job deve ser único',
            'description.required' => 'A descrição é obrigatória'
        );

        $task_id = null;
        if ($this->validate($rules, $post, $messages)) {
            $service = new TasksService(
                $this->usuario,
                $this->empresa,
                $this->modulo,
                $this->data,
                $post
            );

            $task_id = $service->add();
            if (!empty($task_id)) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Tarefa '.$post['job'].' criada com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash('success', 'Tarefa <strong>'.$post['job'].'</strong> incluída com sucesso!');
                $this->redirect();
            }
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível criar a tarefa '.$post['job'].' pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash('error', 'Não foi possível criar a tarefa <strong>'.$post['job'].'</strong>! '.implode('</br>', $this->errors));
            $this->redirect($voltar);
        }
    }

    public function edit()
    {
        $post = $this->app->request()->post();

        $voltar = $post['voltar'];
        unset($post['voltar']);

        $service = new TasksService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            $post
        );

        if (!empty($service->edit())) {
            $this->app->flash('success', 'Tarefa editada com sucesso!');
        } else {
            $this->app->flash('error', 'Ocorreu um problema ao editar a tarefa!');
        }
        $this->redirect($voltar);
    }

    public function delete()
    {
        $post = $this->app->request()->post();

        $service = new TasksService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            $post
        );

        if (!empty($service->delete())) {
            $this->app->flash('success', 'Tarefa deletada com sucesso!');
        } else {
            $this->app->flash('error', 'Ocorreu um problema ao deletar a tarefa!');
        }
        $this->redirect();
    }
}
