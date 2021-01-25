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
namespace Lidere\Modules\Login\Controllers;

use Lidere\Core;
use Lidere\Config;
use Lidere\Controllers\Controller;
use Lidere\Assets;

/**
 * Login
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Login\Controllers\Login
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Login extends Controller {

    public $url = 'login';

    public function index()
    {
        if ($this->app->service->auth()) {
            $this->app->redirect('home');
        } else {
            $cookieValue = $this->app->getEncryptedCookie(Config::read('COOKIE_NAME'));
            if ( $cookieValue != null ) {
                Core::validaCookie($this->app, $cookieValue);
            }
        }

        $this->app->render(
            'index.html.twig'
        );
    }

    public function login()
    {
        $usuario = $this->app->service->login();

        if (!empty($usuario)) {
            $this->app->setEncryptedCookie(
                Config::read('COOKIE_NAME'),
                base64_encode($usuario['id'].'!#'.$usuario['usuario'])
            );
            if ($usuario['tipo'] == 'user') {
                $this->app->redirect(Core::parametro('redirecionamento', 'home'));
            } else {
                $this->app->redirect('home');
            }
        } else {
            $this->app->flash('error', 'Usuário e/ou senha inválidos!');
            $this->app->redirect('login');
        }
    }

    public function forgot()
    {
        if ($this->app->service->forgot()) {
            $this->app->redirect('home');
        }

        $this->app->render(
            'login.html.twig'
        );
    }

    public function recover()
    {
        $data = $this->app->service->recover();

        $this->app->flash('success', $data['flash']);
        $this->app->redirect('login');
    }

    public function newPassword()
    {
        if (!$this->app->service->newPassword()) {
            $this->app->redirect('login');
        }

        Assets::add('assets/js/new-password.js', 'Login');
        $this->app->render(
            'new-password.html.twig'
        );
    }

    public function resetaSenha()
    {
        $data = $this->app->service->resetaSenha();

        $this->app->flash('success', !empty($data['success']) ? $data['success'] : null);
        $this->app->flash('error', !empty($data['error']) ? $data['error'] : null);

        $this->app->redirect('/new-password');
    }

    public function logout()
    {
        session_destroy();
        $this->app->deleteCookie(Config::read('COOKIE_NAME'));
        $this->app->redirect('login');
    }
}
