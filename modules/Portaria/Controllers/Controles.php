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
use Lidere\Modules\Portaria\Models\Funcionario;
use Lidere\Assets;
use Lidere\Modules\Portaria\Models\VControle;

/**
 * Controller Controles
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Portaria\Controllers\Controles
 * @author     William Mascarello <willim.mascarello@lideresistemas.com.br>
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Controles extends Controller
{
    /**
     * Rota do modulo
     *
     * @var string
     */
    public $url = 'portaria/controles';

    /**
     * Listagem dos registros
     *
     * @param integer $pagina Número da página
     *
     * @return void
     */
    public function pagina($pagina = 1)
    {

        Assets::add('/assets/js/controles.js', 'Portaria');
        Assets::add('/assets/css/controles.css', 'Portaria');

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

        Assets::add('/assets/js/controles.js', 'Portaria');
        Assets::add('/assets/css/controles.css', 'Portaria');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }


    public function addAjax()
    {
        $this->withJson(
            $this->app->service->add()
        );
    }


    public function editAjax()
    {
        $this->withJson(
            $this->app->service->edit()
        );
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
                'Controle removido com sucesso pelo usuário '.$this->usuario['id'].
                ' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Controle removido com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        } else {
            $this->app->flash('error', 'Não foi possível remover o Controle! ');
            $this->app->redirect($this->data['voltar']);
        }
    }


    public function retornaPlacaAnterior()
    {
        $return = new \stdClass();

        $return->retorno = false;

        $get = $this->app->request->get();

        $retorno = false;

        if(!empty($get['placa'])){
            $controle = VControle::where('placa',$get['placa'])->orderBy('id','desc')->first();
        }

        $return->controle = $controle;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }
}
