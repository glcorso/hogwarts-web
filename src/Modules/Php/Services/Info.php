<?php

namespace Lidere\Modules\Php\Services;

use Lidere\Core;
use Lidere\Modules\Services\ServicesInterface;

/**
 * Info
 *
 * @package Lidere\Modules
 * @subpackage Php\Services
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Info implements ServicesInterface
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

    public function __construct(
        $usuario = array(),
        $empresa = array(),
        $modulo = array(),
        $data = array(),
        $input = array()
    )
    {
        $this->usuario = $usuario;
        $this->empresa = $empresa;
        $this->modulo = $modulo;
        $this->data = $data;
        $this->input = $input;

        $this->data['filtros'] = $this->input;
    }

    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list()
    {
        ob_start();
        phpinfo();
        $content = ob_get_contents();
        ob_end_clean();
        $content = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$'.'1',$content);

        $this->data['ini_loaded_file'] = php_ini_loaded_file();
        $this->data['resultado'] = $content;
        return $this->data;
    }
}
