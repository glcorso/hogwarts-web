<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão das empresas do sistema
 *
 * @package Models
 * @category Model
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Empresa extends Model
{

    public $table = 'tempresas';

    /* result retorna vários registros, row retorna apenas um */
    public function buscaEmpresas($tipo = 'result', $restricao = false)
    {
        if (!$restricao) {
            return false;
        }

        $empresa = self::where(function ($query) use ($restricao) {
            if (!empty($restricao)) {
                foreach ($restricao as $coluna => $valor) {
                    $query->where($coluna, '=', $valor);
                }
            }
        });

        $result = $tipo == 'result' ? $empresa->get() : $empresa->first();

        return !empty($result) ? $result->toArray() : null;
    }


    public function buscaEmpresasEsp($usu_id = false)
    {
        if (!$usu_id) {
            return false;
        }

        $empresa = self::whereExists(function ($query) use ($usu_id) {
            if ($usu_id != 1) {
                $query->select(DB::raw(1))
                      ->from('tmodulos_usuarios')
                      ->whereRaw('tmodulos_usuarios.empresa_empr_id = tempresas.id')
                      ->where('usuario_id', '=', $usu_id);
            }
        })
        ->get();

        return !empty($empresa) ? $empresa->toArray() : null;
    }
}
