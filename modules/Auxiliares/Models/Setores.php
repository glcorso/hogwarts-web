<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Auxiliares\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta Setores 
 *
 * @package Models
 * @category Core
 * @author Ramon Barros
 * @copyright 2019 Lidere Sistemas
 */
class Setores extends Model
{

    protected $core;

    function __construct()
    {
        $this->core = Core::getInstance();
    }

    public function setores($retorno = 'result', $restricao = false, $paginacao = false)
    {
        $setores = self::select(
            array(
                's.*'
            )
        )
        ->from('tsetores AS s')
        ->where(function ($query) use ($restricao) {
            if (!empty($restricao)) {
                foreach ($restricao as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        });

        if (!empty($paginacao)) {
            /**
             * records = qtd de registros
             * offset = inicia no registro n
             */
            list($records, $offset) = $paginacao;
            $setores->skip($records)->take($offset)->get();
        }

        $result = $retorno == 'result' ? $setores->get() : $setores->first();

        return !empty($result) ? $result->toArray() : null;
    }

}