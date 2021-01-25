<?php

namespace Lidere\Modules\Assistencia\Models;

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
class RastreabilidadeGarantia extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_rast_garantia';

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
        , 'cnpj'
        , 'razao_social'
        , 'nome_fantasia'
        , 'cidade'
        , 'uf'
        , 'num_nf'
        , 'chave_acesso'
        , 'dt_emissao'
        , 'transportadora'
        , 'cnpj_transportadora'
        , 'criado_por'
        , 'criado_em'
        , 'coletado_em'
        , 'recebido_em'
        , 'recebido_por'
        , 'status'
        , 'dt_solicitado'
        , 'xml'
    ];


     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public static function criar($input = null)
    {
        $rastreabilidade = null;

        try {
            
            $rastreabilidade = new RastreabilidadeGarantia();
            $rastreabilidade->cnpj                = $input['cnpj'];
            $rastreabilidade->razao_social        = $input['razao_social'];
            $rastreabilidade->nome_fantasia       = $input['nome_fantasia'];
            $rastreabilidade->cidade              = $input['cidade'];
            $rastreabilidade->uf                  = $input['uf'];
            $rastreabilidade->num_nf              = $input['num_nf'];
            $rastreabilidade->chave_acesso        = $input['chave_acesso'];
            $rastreabilidade->dt_emissao          = $input['dt_emissao'];
            $rastreabilidade->transportadora      = $input['transportadora'];
            $rastreabilidade->cnpj_transportadora = $input['cnpj_transportadora'];
            $rastreabilidade->criado_por          = $_SESSION['usuario']['id'];
            $rastreabilidade->criado_em           = date('d/m/Y H:i:s');
            $rastreabilidade->xml                 = $input['xml'];


            $rastreabilidade->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            //throw new \Exception($e->getMessage());
            $rastreabilidade = false;
        }
        return $rastreabilidade;
    }


    public function Concorrente()
    {
        return $this->belongsTo(Concorrente::class);
    }
}

