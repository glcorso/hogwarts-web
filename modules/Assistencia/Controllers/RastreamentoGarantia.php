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
 * @license  Copyright (c) 2020
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Assistencia\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Assets;
use Lidere\Core;

/**
 * RastreamentoGarantia
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Controllers\RastreamentoGarantia
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class RastreamentoGarantia extends Controller {

    public $url = 'assistencia-tecnica/rastreamento-garantias';

    public function index()
    {
        Assets::add('/assets/js/rastreamento-garantia.js', 'Assistencia');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list()
            )
        );
    }

    public function form($id = null)
    {
        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    public function operacoes()
    {

        $id = $this->app->service->operacoes();
        if (!empty($id)) {
            
            $this->app->flash('success', 'Operação Realizada com Sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível realizar a operação! Verifique os dados informados!');
            $this->app->redirect($this->data['voltar']);
        }

    }


    public function anexarNfe()
    {

        $anexou = $this->app->service->anexarNfe($_FILES);
        if (!empty($anexou)) {
    

            $this->app->flash('success', 'Notas incluídas com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível incluir as notas selecionadas, verifique se esta nota já foi importada! ');
            $this->app->redirect($this->data['voltar']);
        }

    }
}
