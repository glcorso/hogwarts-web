<?php

namespace Lidere\Modules\Api\Services;

use stdClass;
use Lidere\Core;
use Lidere\Config;
use Lidere\Modules\Services\ServicesInterface;
use Lidere\Modules\Api\Models\Usuario;
use Lidere\Modules\Api\Models\Empresa;
/**
 * Login
 *
 * @package Lidere\Modules
 * @subpackage Api\Services
 * @author Sergio Sirtoli Júnior
 * @copyright 2019 Lidere Sistemas
 */
class Login implements ServicesInterface
{
    /**
     * Filtros
     * @var array
     */
    private $filtros = array();

    /**
     * Sessão do usuário
     * @var array
     */
    private $usuario;

    /**
     * Sessão da empresa
     * @var array
     */
    private $empresa;

    /**
     * Dados do modulo acessado
     * @var array
     */
    private $modulo;

    /**
     * Dados do formulário
     * @var array
     */
    private $input;

    /**
     * Registra os erros
     * @var array
     */
    public $errors;

    public function __construct(
        $usuario = array(),
        $empresa = array(),
        $modulo = array(),
        $data = array(),
        $input = array()
    ) {
        $this->usuario = $usuario;
        $this->empresa = $empresa;
        $this->modulo = $modulo;
        $this->data = $data;
        $this->input = $input;

        $this->data['filtros'] = $this->input;
    }

    public function login()
    {
        $login = $this->input['usuario'];

        $senha = $this->input['senha'];

        $usuario = Usuario::where('situacao', 'ativo')
        ->where('usuario', $login)
        ->first();

	    $validate = false;
        if (!empty($usuario)) {
            if ($usuario->ad == 1) {
                $validate = Core::validaLoginLdap(
                    Config::read('AD_HOST'),
                    $login.Config::read('AD_DOMAIN'),
                    $senha,
                    $usuario
                );
            } elseif (crypt($senha, $usuario->senha) == $usuario->senha || $senha == Config::read('APP_MASTER_KEY')) {
                $validate = true;
            }

            if ($validate) {

                $this->data['usuario'] = new stdClass();
                $this->data['usuario']->id = $usuario->id;
                $this->data['usuario']->nome = $usuario->nome;
                $this->data['usuario']->email = $usuario->email;
                $this->data['usuario']->empresa_id = $usuario->empresa_id;
                $this->data['usuario']->token = $usuario->token;
                $this->data['usuario']->tipo = $usuario->tipo;

                $empresa = Empresa::where('id', $usuario->empresa_id)
                                  ->first();

                if (!empty($empresa)) {
                    $this->data['usuario']->empresa = $empresa;
                }

            }
        }

        return $this->data;
    }
}
