<?php

namespace Lidere\Modules\Comercial\Models;

use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;



/**
 * Model para retorno dos dados do banco
 *
 * @category   Controllers
 * @package    Modules
 * @subpackage RelatorioVisitaStatus
 *
 * @author    Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright 2019 Lidere Sistemas
 * @license   GPL-3 https://www.lideresistemas.com.br/licence.txt
 * @link      https://www.lideresistemas.com.br/
 */
class RelatorioVisitaConcorrentes extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_rel_conc';

    public $timestamps = false;
    public $sequence = false;

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
          'id'
        , 'concorrente_id'
        , 'relatorio_id'
        , 'tipo'
    ];


     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public static function criar($input = null)
    {
        $relatorioVisitaConcorrente = null;

        try {
            
            $relatorioVisitaConcorrente = new RelatorioVisitaConcorrentes();
            $relatorioVisitaConcorrente->concorrente_id     = $input['concorrente_id'];
            $relatorioVisitaConcorrente->relatorio_id       = $input['relatorio_id'];
            $relatorioVisitaConcorrente->tipo               = $input['tipo'];

            $relatorioVisitaConcorrente->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $relatorioVisitaConcorrente;
    }


    public function Concorrente()
    {
        return $this->belongsTo(Concorrente::class);
    }
}

