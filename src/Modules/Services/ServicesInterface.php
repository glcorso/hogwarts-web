<?php

namespace Lidere\Modules\Services;

/**
 * Interface para definição dos Services dos modulos
 *
 * @package Lidere\Modules
 * @subpackage Services
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
interface ServicesInterface {

    public function __construct(
        $usuario = array(),
        $empresa = array(),
        $modulo = array(),
        $data = array(),
        $input = array()
    );

}
