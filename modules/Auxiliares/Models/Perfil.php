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
 * Classe para consulta Perfsil 
 *
 * @package Modelss
 * @category Core
 * @author Ramon Barros
 * @copyright 2019 Lidere Sistemas
 */
class Perfil extends Model
{

    public $table = 'tperfis';

    protected $core;

    public $timestamps = false;


    function __construct()
    {
        $this->core = Core::getInstance();
    }

    public function modulos()
    {
        return $this->belongsToMany(
            'Lidere\Models\Modulo',
            'tmodulos_perfil',
            'perfil_id',
            'modulo_id'
        )->withPivot('empresa_empr_id', 'permissao');
    }

    public function perfis($retorno = 'result', $restricao = false, $paginacao = false)
    {
        $perfis = self::select(
            array(
                'p.*'
            )
        )
        ->from('tperfis AS p')
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
            $perfis->skip($records)->take($offset)->get();
        }

        $result = $retorno == 'result' ? $perfis->get() : $perfis->first();

        return !empty($result) ? $result->toArray() : null;
    }

    public function moduloPerfil()
    {
        return $this->belongsToMany('Lidere\Models\Modulo', 'tmodulos_perfil', 'perfil_id')
                    ->withPivot('empresa_empr_id', 'permissao');
    }

}