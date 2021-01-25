<?php

namespace Lidere\Modules\Api\Controllers;

use Lidere\Modules\Api\Controllers\Core\Api;

/**
 * Welcome
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers
 * @author Ramon Barros
 * @copyright 2017 Lidere Sistemas
 */
class Welcome extends Api
{
    public $url = false;

    /**
     * Página de boas vindas
     *
     * @access public
     *
     * @apiVersion 1.0.0
     * @apiName Lidere
     * @apiGroup Api
     * @api {get} /
     * @apiDescription Página contendo informações sobre a API
     * @apiUse Erro404
     * @apiErrorExample {json} Error-Response 404:
     * HTTP/1.1 404 Not Found
     * {
     *   "error": "Not Found"
     * }
     */
    public function index()
    {
        $this->app->render('index.twig');
    }

    public function ok()
    {
        $data = [
            'ok' => true
        ];
        $this->setData($data)
             ->response();
    }
}
