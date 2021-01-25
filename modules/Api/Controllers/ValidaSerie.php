<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Api\Controllers;

use stdClass;
use Lidere\Modules\Api\Controllers\Core\Api;
use Lidere\Core;
use Lidere\Modules\Api\Services\ValidaSerie as validaSerieService;

/**
 * ValidaSerie
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class ValidaSerie extends Api
{
    /**
     * Url de acesso ao modulo
     * @var bool
     */
    public $url = false;

    /**
     * Schema de validação
     * @var string
     */
    public $schema = null;

    /**
     * Schema de validação da exclusão
     * @var string
     */
    public $schemaDelete = null;

    public function __construct()
    {
        parent::__construct();
        $this->schema = APP_ROOT.'modules'.DS.'Api'.DS.'Json'.DS.'Schema'.DS.'validaSerie.json';
    }

    /**
     * Insere
     *
     * @return void
     * @throws \ReflectionException
     */
    public function read()
    {
	    dlog('debug', 'Read');
	    dlog('debug', 'Headers');
	    dlog('debug', print_r($this->app->request->headers, true));
	    dlog('debug', 'Body');
	    $json = $this->app->request()->getBody();
        $serie = !empty($json) ? json_decode($json) : [];


        $status = 201;
        $data = [];
        
        if ($this->validateSchema($serie, $this->schema)) {
            $token = Core::parametro('portal_token_jiga');
            if($token == $serie->token){
                $service = new validaSerieService(
                    $this->usuario,
                    $this->empresa,
                    $this->modulo,
                    $this->object,
                    $serie
                );
                $data = $service->read();
    	        if ($data['error']) {
                    $status = 200;
                }
                $this->setData($data);
            }else{
                $this->setError(
                    'VALIDASERIE',
                    'Token Incorreto! Verifique com a Lidere Sistemas o Token Correto!',
                    array('Token Inválido!')
                );
                $status = 401;
            }
        } else {
            $this->setError(
                'VALIDASERIE',
                'Você deve informar a série!',
                $this->errors
            );
            $status = 400;
        }
	    dlog('debug', print_r($this->object, true));
        $this->response($status);
    }
}

