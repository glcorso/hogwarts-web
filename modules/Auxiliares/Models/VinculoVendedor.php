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
 * Classe para consulta VinculoVendedor 
 *
 * @package Models
 * @category Core
 * @author Ramon Barros
 * @copyright 2019 Lidere Sistemas
 */
class VinculoVendedor extends Model
{

    protected $core;

    function __construct()
    {
        $this->core = Core::getInstance();
    }

    public function vinculos($retorno = 'result', $restricao = false, $paginacao = false)
    {
        $vinculos = self::select(
            array(
                'v.*','uext.nome as nome_externo', 'uint.nome as nome_interno', 'uint.email as email_interno'
            )
        )
        ->from('tvinculo_vendedores AS v')
        ->join('tusuarios AS uint', 'uint.id', '=', 'v.int_usuario_id')
        ->join('tusuarios AS uext', 'uext.id', '=', 'v.ext_usuario_id')
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
            $vinculos->skip($records)->take($offset)->get();
        }

        $result = $retorno == 'result' ? $vinculos->get() : $vinculos->first();

        return !empty($result) ? $result->toArray() : null;
    }


    public function retornaEmailsInterno($retorno = 'row', $restricao = false, $paginacao = false)
    {
        $vinculos = self::select(DB::raw('GROUP_CONCAT(uint.email) email_interno')
        )
        ->from('tvinculo_vendedores AS v')
        ->join('tusuarios AS uint', 'uint.id', '=', 'v.int_usuario_id')
        ->join('tusuarios AS uext', 'uext.id', '=', 'v.ext_usuario_id')
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
            $vinculos->skip($records)->take($offset)->get();
        }

        $result = $retorno == 'result' ? $vinculos->get() : $vinculos->first();

        return !empty($result) ? $result->toArray() : null;
    }

}