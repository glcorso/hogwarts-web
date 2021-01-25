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
class RelatorioVisitaStatus extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_rel_vis_status';

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
        , 'status_id'
        , 'dt_status'
        , 'responsavel_id'
        , 'relatorio_id'
    ];


     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public static function criar($input = null)
    {
        $relatorioVisitaStatus = null;

        try {
            
            $relatorioVisitaStatus = new RelatorioVisitaStatus();
            $relatorioVisitaStatus->status_id          = $input['status_id'];
            $relatorioVisitaStatus->dt_status          = date('d/m/Y H:i:s');
            $relatorioVisitaStatus->responsavel_id     = $_SESSION['usuario']['id'];
            $relatorioVisitaStatus->relatorio_id       = $input['relatorio_id'];

            $relatorioVisitaStatus->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $relatorioVisitaStatus;
    }
}

