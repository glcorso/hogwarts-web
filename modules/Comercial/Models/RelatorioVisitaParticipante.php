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
class relatorioVisitaParticipante extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_rel_particip';

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
        , 'participante_id'
    ];

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public static function criar($input = null)
    {
        $relatorioVisitaParticipante = null;

        try {
            
            $relatorioVisitaParticipante = new relatorioVisitaParticipante();
            $relatorioVisitaParticipante->relatorio_id     = $input['relatorio_id'];
            $relatorioVisitaParticipante->participante_id  = $input['participante_id'];

            $relatorioVisitaParticipante->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $relatorioVisitaParticipante;
    }
}

