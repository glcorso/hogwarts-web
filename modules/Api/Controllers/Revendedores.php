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
use Lidere\Modules\Api\Services\Revendedores as RevendedoresService;

/**
 * Revendedores
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Revendedores extends Api
{
    public $url = false;

    public function index($token = false)
    {

        if(Core::parametro('site_token') == $token ){
            $service = new RevendedoresService(
                $this->usuario,
                $this->empresa,
                $this->modulo,
                $this->data,
                false
            );

            $data = $service->list();

            if (!empty($data['resultado'])) {
                $this->setData([
                    'revendedores' => $data['resultado']
                ]);
                $this->response();
            } else {
                $this->setError('REVENDEDORES', 'Nenhum Revendedor Encontrado.')
                     ->response(403);
            }
        }else{
            $this->setError('REVENDEDORES', 'Proibido')
                     ->response(401);
        }
       
    }
}
