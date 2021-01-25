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
namespace Lidere\Modules\Logs\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Log
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Log\Models\Log
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Log extends Model
{

    protected $table = 'tlogs';

    public function buscaLogs($ordem = 'todos', $restricao = false)
    {
        $select = array(
            'id',
            'tipo',
            'log',
            'data',
            'usuario_id',
            'lojista_id'
        );

        if ($ordem != 'todos') {
            if ($ordem == 'ultimo') {
                array_push($select, 'DATE_FORMAT(MAX(data),"%d/%m/%Y %H:%i") databr');
            } else {
                array_push($select, 'DATE_FORMAT(MIN(data),"%d/%m/%Y %H:%i") databr');
            }
        } else {
            array_push($select, 'DATE_FORMAT(DATA, "%d/%m/%Y %H:%i") databr');
        }

        $logs = self::where(function ($query) use ($restricao) {
            if (!empty($restricao)) {
                foreach ($restricao as $column => $value) {
                    $query->where($column, '=', $value);
                }
            }
        })
                    ->orderBy('data', 'desc');

        $result = $retorno == 'todos' ? $logs->get() : $logs->first();

        return !empty($result) ? $result->toArray() : null;
    }

    public function buscaTiposLogs()
    {
        $tipoLog = self::distinct()
                       ->select('tipo AS tipos')
                       ->from('tlogs')
                       ->orderBy('tipo')
                       ->get();

        return !empty($tipoLog) ? $tipoLog->toArray() : null;
    }
}
