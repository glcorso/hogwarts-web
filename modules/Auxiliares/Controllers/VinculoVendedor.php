<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Auxiliares\Controllers;

use Lidere\Config;
use Lidere\Core;
use Lidere\Assets;
use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Modules\Erp\Models\Erp;

/**
 * VinculoVendedor
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Controllers\VinculoVendedor
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class VinculoVendedor extends Controller
{
    protected $url = 'auxiliares/vinculo-vendedor';

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
        //Assets::add('/assets/js/setores.js', 'Auxiliares');

        $this->app->render(
            'form.html.twig',
            array(
                'data' => $this->app->service->form($id)
            )
        );
    }

    public function add()
    {
        $rules = array(
            'int_usuario_id' => 'required',
            'ext_usuario_id' => 'required'
        );
        $messages = array(
            'int_usuario_id.required' => 'Vendedor Interno obrigatório',
            'ext_usuario_id.required' => 'Vendedor Externo obrigatório'
        );

        $vinculo_id = null;
        if ($this->validate($rules, $this->app->service->input, $messages)) {
            $vinculo_id = $this->app->service->add();

            if (!empty($vinculo_id)) {
                Core::insereLog(
                    $this->modulo['url'],
                    'Vínculo criado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );

                $this->app->flash('success', 'Vínculo incluído com sucesso!');
                $this->app->redirect('/'.$this->modulo['url'].'/pagina/1?');
            }else{
                Core::insereLog(
                    $this->modulo['url'],
                    'Não foi possível criar o Vínculo pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                    $this->usuario['id'],
                    $this->empresa['id']
                );
                $this->app->flash('error', 'Não foi possível criar o vínculo! ');
                $this->app->redirect('/auxiliares/vinculo-vendedor/pagina/1?');
            }

        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível criar o Vínculo pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
            $this->app->flash('error', 'Não foi possível criar o vínculo! ');
            $this->app->redirect($this->data['voltar']);
        }
    }

    public function edit()
    {
        if (!empty($this->app->service->edit())) {
            Core::insereLog(
                $this->modulo['url'],
                'Vínculo alterado com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Vínculo alterado com sucesso!');
        } else {
            Core::insereLog(
                $this->modulo['url'],
                'Não foi possível alterar o Vínculo pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('error', 'Não foi possível alterar o vínculo!');
        }

        $this->app->redirect('/auxiliares/vinculo-vendedor/pagina/1?');
    }

    public function delete()
    {
        $aplicacaoObj = new Aplicacao();

        $id = $this->app->request()->post('id');
        if (!empty($id)) {
            $aplicacaoObj->delete('tvinculo_vendedores', $id);
            $this->app->flash('success', 'Vínculo excluído com sucesso!');
            Core::insereLog(
                $this->modulo['url'],
                'Vínculo '.$id.' excluído com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );
        } else {
            $this->app->flash('error', 'Problema ao excluir o vínculo!');
        }
        $this->app->redirect($this->app->request->getReferer());
    }
}
