<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Api\Controllers;

use Lidere\Core;
use Lidere\Modules\Api\Controllers\Core\Api;
use Lidere\Modules\Assistencia\Models\Atendimento as modelAssistencia;

/**
 * Concorrentes
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class ClientesEstabelecimento extends Api
{
    public $url = false;

    public function index()
    {

        $modelAssistencia = new modelAssistencia();

        $clientes = $modelAssistencia->retornaClientesCnpjAPI(false,false);

        if (!empty($clientes)) {
            $this->setData([
                'clientes' => $clientes
            ]);
            $this->response();
        } else {
            $this->setError('CLIENTES', 'Nenhum Cliente Encontrado.')
                 ->response(403);
        }
       
    }
}
