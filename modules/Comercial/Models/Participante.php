<?php

namespace Lidere\Modules\Comercial\Models;


// use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;



/**
 * Model para retorno dos dados do banco
 *
 * @category   Controllers
 * @package    Modules
 * @subpackage Participante
 *
 * @author    Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright 2019 Lidere Sistemas
 * @license   GPL-3 https://www.lideresistemas.com.br/licence.txt
 * @link      https://www.lideresistemas.com.br/
 */
class Participante extends OracleEloquent
{
    
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_participante';
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
        , 'nome'
        , 'e_mail'
        , 'setor'
        , 'cliente_descritivo'
        , 'cliente_id'
    ];


    public static function criar($input = null)
    {
        $participante = null;
        try {
            $participante = new Participante();
            $participante->nome   = $input['nome'];
            $participante->e_mail = $input['e_mail'];
            if(!empty($input['setor'])){
                $participante->setor          = $input['setor'];
            }
            if(!empty($input['cliente_descritivo'])){
                $participante->cliente_descritivo  = $input['cliente_descritivo'];
            }
            if(!empty($input['cliente_id'])){
                $participante->cliente_id          = $input['cliente_id'];
            }
            $participante->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $participante;
    }


    public function Cliente()
    {
        return $this->belongsTo(ClienteErp::class);
    }
}
