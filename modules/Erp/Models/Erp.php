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
namespace Lidere\Modules\Erp\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Erp
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Erp\Models\Erp
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Erp extends Model
{
    protected $core;

    protected $connection;

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public function buscaClienteErp($restricao = false, $string = null)
    {
        $clientes = self::select('ID, COD_CLI, DESCRICAO')
                           ->from('tclientes')
                           ->where('ATIVO', '=', 1)
                           ->where(function ($query) use ($restricao, $string) {
                                if (!empty($restricao)) {
                                    foreach ($restricao as $column => $value) {
                                        $query->whereRaw($column . " " . $value);
                                    }
                                }
                                if (!empty($string)) {
                                    $query->whereRaw(
                                        "(UPPER(COD_CLI) LIKE '%".strtoupper($string)."%' OR UPPER(DESCRICAO) LIKE '%".strtoupper($string)."%')"
                                    );
                                }
                           })
                           ->get();

        return !empty($clientes) ? $clientes->toArray() : null;
    }

    public function buscaCondPagtos($restricao = false)
    {
        $condPagatos = self::select('ID, COD_CDPG CODIGO, DESCRICAO')
                           ->from('TCOND_PAGTOS')
                           ->where('ATIVO', '=', 1)
                           ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $column => $value) {
                                    $query->whereRaw($column . " " . $value);
                                }
                            }
                           })
                           ->get();

        return !empty($condPagatos) ? $condPagatos->toArray() : null;
    }

    public function buscaPortadores($restricao = false)
    {
        $portadores = self::select('ID, COD_POR CODIGO, DESCRICAO')
                           ->from('TPORTADORES')
                           ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $column => $value) {
                                    $query->whereRaw($column . " " . $value);
                                }
                            }
                           })
                           ->get();

        return !empty($portadores) ? $portadores->toArray() : null;
    }

    public function buscaFornecedores($empr_id = null, $restricao = false, $forn_id = false)
    {
        $fornecedores = self::select('f.id , f.cod_for codigo, f.descricao , f.cnpj')
                           ->from('tfornecedores AS f')
                           ->where(function ($query) use ($empr_id, $restricao, $forn_id) {
                            if (!empty($forn_id)) {
                                $query->where('f.id', '=', $forn_id);
                            }
                            if (!empty($restricao)) {
                                foreach ($restricao as $column => $value) {
                                    $query->whereRaw($column . " " . $value);
                                }
                            }
                           })
                           ->orderBy('f.descricao');
        if (!empty($empr_id)) {
            $fornecedores->join('temp_for AS ef', function ($join) use ($empr_id) {
                $join->on('ef.forn_id', '=', 'f.id')
                     ->where('ef.empr_id', '=', $empr_id);
            });
        }

        $result = $fornecedores->get();

        return !empty($result) ? $result->toArray() : null;
    }
}
