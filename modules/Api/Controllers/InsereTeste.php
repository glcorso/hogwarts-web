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
use Lidere\Modules\Api\Services\InsereTeste as insereTesteService;

/**
 * ValidaSerie
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class InsereTeste extends Api
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
        $this->schema = APP_ROOT.'modules'.DS.'Api'.DS.'Json'.DS.'Schema'.DS.'insereTeste.json';
    }

    /**
     * Insere
     *
     * @return void
     * @throws \ReflectionException
     */
    public function create()
    {
	    dlog('debug', 'Create');
	    dlog('debug', 'Headers');
	    dlog('debug', print_r($this->app->request->headers, true));
	    dlog('debug', 'Body');
	    $json = $this->app->request()->getBody();
        $teste = !empty($json) ? json_decode($json) : [];


        $status = 201;
        $data = [];
        
        if ($this->validateSchema($teste, $this->schema)) {
            $token = Core::parametro('portal_token_jiga');
            if($token == $teste->token){
                $service = new insereTesteService(
                    $this->usuario,
                    $this->empresa,
                    $this->modulo,
                    $this->object,
                    $teste
                );
                $data = $service->create();
    	        if ($data['error']) {
                    $status = 200;
                }
                $this->setData($data);
            }else{
                $this->setError(
                    'INSERETESTE',
                    'Token Incorreto! Verifique com a Lidere Sistemas o Token Correto!',
                    array('Token Inválido!')
                );
                $status = 401;
            }
        } else {
            $this->setError(
                'INSERETESTE',
                'Erro ao Validar Schema Json!',
                $this->errors
            );
            $status = 400;
        }
	    dlog('debug', print_r($this->object, true));
        $this->response($status);
    }
}

