<?php

namespace Lidere\Modules\Api\Models;

use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * InsereTeste
 *
 * @package Lidere\Modules
 * @subpackage Erp\Models
 * @author Sergio Sirtoli Júnior
 * @copyright 2019 Lidere Sistemas
 */
class InsereTeste extends OracleEloquent
{

    public $table = 'tsdi_teste_jiga_geladeiras';

    public $timestamps = false;

    protected $fillable = [
         'dt_teste' 
        ,'operador' 
        ,'serie_id '
        ,'obs_iniciais' 
        ,'dt_fim'
        ,'serie_compressor'
    ];

    protected $cast = [
        'dt_teste' => 'date',
        'operador' => 'string',
        'serie_id' => 'int',
        'obs_iniciais' => 'string',
        'dt_fim' => 'date',
        'serie_compressor' => 'string'
    ];

    public function __construct(array $attributes = [])
    {
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
        parent::__construct($attributes);
    }

   /* public function Marca()
    {
        return $this->belongsTo(Marca::class, 'tsdi_mar_veic_id');
    }*/

    public function total($restricao = []) {
        $total = self::where(function ($query) use ($restricao) {
           if (!empty($restricao)) {
               foreach ($restricao as $column => $value) {
                   $query->whereRaw($column . " " . $value);
               }
           }
       })
       ->count();

       return $total;
    }

    public function buscar($restricao = [], $paginacao = [])
    {
        $registros = self::select(
             'id'
            ,'dt_teste' 
            ,'operador' 
            ,'serie_id '
            ,'obs_iniciais' 
            ,'dt_fim'
            ,'serie_compressor'
        )
        ->where(function ($query) use ($restricao) {
            if (!empty($restricao)) {
                foreach ($restricao as $column => $value) {
                    $query->whereRaw($column . " " . $value);
                }
            }
        })
        //->with(['Marca'])
        ->skip($paginacao[0])
        ->take($paginacao[1])
        ->get();

        return $registros;
    }
}
