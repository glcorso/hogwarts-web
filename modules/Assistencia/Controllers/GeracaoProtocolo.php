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
 * GeracaoProtocolo
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Controllers\GeracaoProtocolos
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class GeracaoProtocolo extends Controller {

    public $url = 'assistencia-tecnica/geracao-protocolo-interno';

    public function index()
    {

        Assets::add('/assets/js/geracaoProtocolo.js', 'Assistencia');

        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->index()
            )
        );

    }

    public function add()
    {
        $chamado_id = $this->app->service->add();

        if (!empty($chamado_id)) {
            Core::insereLog(
                $this->modulo['url'],
                'Protocolos de Assistência Técnica criados com sucesso pelo usuário '.$this->usuario['id'].' - '.$this->usuario['nome'].'.',
                $this->usuario['id'],
                $this->empresa['id']
            );

            $this->app->flash('success', 'Protocolos de Assistência Técnica incluídos com sucesso!');
            $this->app->redirect('/'.$this->modulo['url']);
        }else{ 
            $this->app->flash('error', 'Não foi possível criar os Protocolos de Assistência Técnica');
            $this->app->redirect($this->data['voltar']);
        }

    }
}