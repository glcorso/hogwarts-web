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
 * TPagamentoOrdem
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models
 * @author     Humberto Viezzer de Carvalho
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class TPagamentoOrdem extends OracleEloquent
{

    public $table = 'tsdi_pagamento_ordem';
    public $timestamps = false;

    public function __construct()
    {
        $this->connection = 'oracle_' . $_SESSION['empresa']['id'];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'pagamento_id',
        'ordem_id',
        'valor'
    ];

    public static function criar($ordem_id = 0, $pagamento_id = 0, $valor = 0)
    {
        $pagamentoOrdem = null;
        try {

            $pagamentoOrdem = new TPagamentoOrdem();

            $pagamentoOrdem->ordem_id = $ordem_id;
            $pagamentoOrdem->pagamento_id = $pagamento_id;
            $pagamentoOrdem->valor = $valor;

            $pagamentoOrdem->save();
        } catch (\Exception $e) {
            return false;
        }
        return $pagamentoOrdem;
    }
}
