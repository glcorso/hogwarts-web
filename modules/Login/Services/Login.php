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
namespace Lidere\Modules\Login\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Auxiliares\Models\Usuario as UsuarioModel;

/**
 * Login
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Login\Services\Login
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Login extends Services
{

    public function auth()
    {
        return !empty($this->usuario['id']);
    }

    public function login()
    {
        $usuario = Core::efetuaLogin(
            $this->input['usuario'],
            $this->input['senha']
        );
        return $usuario;
    }

    public function forgot()
    {
        return !empty($this->usuario['id']);
    }

    public function recover()
    {
        $usuario = Core::resetaSenha(
            !empty($this->input['email']) ? $this->input['email'] : null
        );

        $this->data['flash'] = 'Você receberá um email com a nova senha caso o email '.$this->input['email'].' esteja cadastrado no sistema ';

        return $this->data;
    }

    public function newPassword()
    {
        return !empty($this->usuario['id']);
    }

    public function resetaSenha()
    {
        if (!empty($this->input['password']) && !empty($this->input['confirm'])) {
            $this->input['id_user'] = !empty($this->usuario['id'])
                            ? $this->usuario['id']
                            : null;
            $usuario = Core::redefineSenha($this->input);

            if (!empty($usuario)) {
                $this->data['success'] = '<b>'.$usuario['nome'] .'</b> sua senha foi redefinida com Sucesso!';
            } else {
                $this->data['error']  = 'Ocorreu um erro ao redefinir sua senha!';
            }
        } else {
            $this->data['error'] = 'Ocorreu um erro ao redefinir sua senha!';
        }
        return $this->data;
    }
}
