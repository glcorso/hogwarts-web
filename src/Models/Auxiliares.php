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
 * Classe para consulta, inclusão, edição e exclusão de tabelas auxiliares do sistema
 *
 * @package Models
 * @category Core
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Auxiliares extends Model
{

    protected $core;

    function __construct()
    {
        $this->core = Core::getInstance();
    }

    public function empresa($empresa_id = null)
    {
        $id = $empresa_id ? $empresa_id : $_SESSION['empresa']['id'];
        $empresa = self::from('tempresas')
                       ->where('id', '=', $id)
                       ->first();

        return !empty($empresa) ? $empresa->toArray() : null;
    }

    public function usuarios($retorno = 'result', $restricao = false, $paginacao = false)
    {
        $usuarios = self::select(
            array(
                'u.*',
                'uc.cliente_erp_id',
                'uc.cliente_erp_cod_cli',
                'uc.cliente_erp_descricao',
                'us.setor_id',
                'ucom.company_id'
            )
        )
        ->from('tusuarios AS u')
        ->leftJoin('tusuarios_clientes AS uc', 'uc.usuario_id', '=', 'u.id')
        ->leftJoin('tusuarios_setor AS us', 'us.usuario_id', '=', 'u.id')
        ->leftJoin('tusuarios_company AS ucom', 'ucom.usuario_id', '=', 'u.id')
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
            $usuarios->skip($records)->take($offset)->get();
        }

        $result = $retorno == 'result' ? $usuarios->get() : $usuarios->first();

        return !empty($result) ? $result->toArray() : null;
    }

    public function parametros($retorno = 'result', $restricao = false)
    {
        $parametros = self::select('p.*')
                          ->from('tparametros AS p')
                          ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $coluna => $valor) {
                                    $query->whereRaw($coluna." ".$valor);
                                }
                            }
                          })
                          ->orderBy('p.grupo');

        $result = $retorno == 'result' ? $parametros->get() : $parametros->first();

        return !empty($result) ? $result->toArray() : null;
    }


    public function grupoParametros($retorno = 'result', $restricao = false)
    {

        $parametros = self::distinct()
                          ->select('grupo')
                          ->from('tparametros')
                          ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $coluna => $valor) {
                                    $query->where($coluna, '=', $valor);
                                }
                            }
                          })
                          ->orderBy('grupo');

        $result = $retorno == 'result' ? $parametros->get() : $parametros->first();

        return !empty($result) ? $result->toArray() : null;
    }

    public function parametroValor($restricao = false)
    {

        if (empty($restricao)) {
            return false;
        }

        $parametro = self::select('valor')
                          ->from('tempresa_parametros')
                          ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $coluna => $valor) {
                                    $query->whereRaw($coluna." ".$valor);
                                }
                            }
                          })
                          ->first();

        $result = !empty($parametro) ? $parametro->toArray() : array();

        return !empty($result['valor']) ? $result['valor'] : null;
    }

    public function parametroSetado($restricao = false)
    {

        if (empty($restricao)) {
            return false;
        }

        $parametro = self::from('tempresa_parametros')
                         ->where(function ($query) use ($restricao) {
                            if (!empty($restricao)) {
                                foreach ($restricao as $coluna => $valor) {
                                    $query->whereRaw($coluna." ".$valor);
                                }
                            }
                         })
                         ->first();

        return !empty($parametro) ? $parametro->toArray() : null;
    }
}
