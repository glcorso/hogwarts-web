<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Services;

/**
 * Services
 *
 * @package Lidere\Modules
 * @subpackage Services
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Services implements ServicesInterface
{
    /**
     * Sessão do usuário
     * @var array
     */
    public $usuario = [];

    /**
     * Sessão da empresa
     * @var array
     */
    public $empresa = [];

    /**
     * Dados do modulo acessado
     * @var array
     */
    public $modulo = [];

    /**
     * Dados para o retorno
     * @var array
     */
    public $data = [];

    /**
     * Dados do formulário
     * @var array
     */
    public $input = [];

    /**
     * Define se houve um erro no service
     * @var boolean
     */
    public $error = false;

    /**
     * Registra os erros
     * @var array
     */
    public $errors = [];

    /**
     * Registra o Exception
     * @var null
     */
    public $exception = null;

    /**
     * Constroi o Serviço para o Modulo
     * @param array $usuario Dados do usuário
     * @param array $empresa Dados da empresa
     * @param array $modulo  Dados do modulo
     * @param array $data    Dados para retorno
     * @param array $input   Dados recebidos
     */
    public function __construct(
        $usuario = [],
        $empresa = [],
        $modulo = [],
        $data = [],
        $input = []
    ) {
        $this->usuario = $usuario;
        $this->empresa = $empresa;
        $this->modulo = $modulo;
        $this->data = $data;
        $this->input = $input;

        $this->data['filtros'] = $this->input;
    }
}
