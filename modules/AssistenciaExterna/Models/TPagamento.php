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
 * TPagamento
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models
 * @author     Humberto Viezzer de Carvalho
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class TPagamento extends OracleEloquent
{

    public $table = 'tsdi_pagamento';
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
        'dt_pagamento',
        'criado_por',
        'criado_em',
        'responsavel_id'
    ];

    public static function criar($input = null, $usuario_id)
    {
        $pagamento = null;
        try {

            $pagamento = new TPagamento();

            $pagamento->dt_pagamento = date('d/m/Y H:i:s');

            $pagamento->criado_por = $usuario_id;
            $pagamento->criado_em = date('d/m/Y H:i:s');


            $pagamento->responsavel_id = !empty($responsavel_id)
                ? $responsavel_id : '';

            $pagamento->autorizado_em = !empty($autorizado_em)
                ? $autorizado_em : '';

            $pagamento->save();
        } catch (\Exception $e) {
            return false;
        }

        return $pagamento;
    }

    public static function editar($id = 0, $responsavel_id = 0, $autorizado_em = null)
    {
        try {

            $pagamento = TPagamento::find($id);

            $pagamento->dt_pagamento = !empty($pagamento->dt_pagamento) ? $pagamento->dt_pagamento : date('d/m/Y H:i:s');

            $pagamento->responsavel_id = !empty($responsavel_id)
                ? $responsavel_id : '';

            $pagamento->autorizado_em = !empty($autorizado_em)
                ? $autorizado_em : '';

            $pagamento->save();


        } catch (\Exception $e) {
            return false;
        }


        return $pagamento;
    }
}
