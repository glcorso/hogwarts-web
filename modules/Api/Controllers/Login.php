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
use Lidere\Modules\Api\Services\Login as LoginService;

/**
 * Login
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Login extends Api
{
    public $url = false;

    public function index()
    {
        $post = (array)$this->getRequestBodyData();
        if ($this->validate($this->constroiRegrasLogin(), $post, $this->constroiMensagensErroLogin())) {
            $service = new LoginService(
                $this->usuario,
                $this->empresa,
                $this->modulo,
                $this->data,
                $post
            );

            $data = $service->login();

            if (!empty($data['usuario'])) {
                $this->setData([
                    'usuario' => $data['usuario']
                ]);
                Core::insereLog(
                    'login',
                    'Acesso ao sistema realizado com sucesso via app.',
                    $data['usuario']->id,
                    1
                );
                $this->response();
            } else {
                $this->setError('LOGIN', 'Usuário ou senha inválidos')
                     ->response(403);
            }
        } else {
            $this->setError('LOGIN', 'Não foi possível efetuar o login', $this->errors)
                 ->response(403);
        }
    }

    private function constroiRegrasLogin() {
        return array(
            'usuario' => 'required',
            'senha'  => 'required'
        );
    }

    private function constroiMensagensErroLogin() {
        return array(
            'usuario.required' => 'O campo usuario é obrigatório.',
            'senha.required' => 'O campo senha é obrigatório.'
        );
    }
}
