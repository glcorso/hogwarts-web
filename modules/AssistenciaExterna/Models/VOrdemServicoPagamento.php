<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2019
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\AssistenciaExterna\Models;

use Yajra\Oci8\Eloquent\OracleEloquent;
use Lidere\Core;

/**
 * VOrdemServicoPagamento
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models
 * @author     Humberto Viezzer de Carvalho
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class VOrdemServicoPagamento extends OracleEloquent
{

    public $table = 'vsdi_ordens_serv_pagamentos';
    public $timestamps = false;

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


}
