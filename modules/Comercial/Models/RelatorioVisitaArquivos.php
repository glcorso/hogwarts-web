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
 * @subpackage RelatorioVisitaArquivos
 *
 * @author    Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright 2019 Lidere Sistemas
 * @license   GPL-3 https://www.lideresistemas.com.br/licence.txt
 * @link      https://www.lideresistemas.com.br/
 */
class RelatorioVisitaArquivos extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_rel_arq';

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
        , 'relatorio_id'
        , 'criado_por'
        , 'criado_em'
        , 'arquivo'
        , 'tipo'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public static function criar($input = null)
    {
        $relatorioVisitaArquivos = null;

        try {

            $relatorioVisitaArquivos = new RelatorioVisitaArquivos();
            $relatorioVisitaArquivos->arquivo          = $input['arquivo'];
            $relatorioVisitaArquivos->tipo             = $input['tipo'];
            $relatorioVisitaArquivos->criado_em        = date('d/m/Y H:i:s');
            $relatorioVisitaArquivos->criado_por       = $_SESSION['usuario']['id'];
            $relatorioVisitaArquivos->relatorio_id     = $input['relatorio_id'];

            $relatorioVisitaArquivos = $relatorioVisitaArquivos->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $relatorioVisitaArquivos;
    }
}

