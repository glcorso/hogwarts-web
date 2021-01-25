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
namespace Lidere\Modules\Worker\Controllers;

use Lidere\Core;
use Lidere\Models\Aplicacao;
use Lidere\Controllers\Cli;

/**
 * Env
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Worker\Controllers\Env
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Env extends Cli
{
    public function index()
    {
        $this->log(null, "Controller/Env::index()");
        $last_line = system('env > /tmp/env.output', $retval);
        $this->log(null, "Última linha da saída: ".$last_line);
        $this->log(null, "Valor de Retorno: ".$retval);
        $this->log(null, "Controller/Env::index()");
    }
}
