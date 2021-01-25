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
 * @subpackage Prospect
 *
 * @author    Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright 2019 Lidere Sistemas
 * @license   GPL-3 https://www.lideresistemas.com.br/licence.txt
 * @link      https://www.lideresistemas.com.br/
 */
class Prospect extends OracleEloquent
{
    
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_prospects';
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
        , 'nome_fantasia'
        , 'razao_social'
        , 'cnpj_cpf'
        , 'uf'
        , 'cidade'
        , 'contato'
        , 'tel_celular'
        , 'telefone'
        , 'e_mail'
        , 'pais'
    ];


    public static function criar($input = null)
    {
        $prospect = null;
        try {
            $prospect = new Prospect();
            $prospect->nome_fantasia = $input['nome_fantasia'];
            $prospect->razao_social  = $input['razao_social'];
            $prospect->cnpj_cpf      = $input['cnpj_cpf'];
            $prospect->uf            = $input['uf'];
            $prospect->cidade        = $input['cidade'];
            $prospect->tel_celular   = $input['tel_celular'];
            $prospect->telefone      = $input['telefone'];
            $prospect->e_mail        = $input['e_mail'];
            if(!empty($input['pais'])){
                $prospect->pais          = $input['pais'];
            }
            if(!empty($input['contato'])){
                $prospect->contato          = $input['contato'];
            }
            $prospect->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $prospect;
    }
}
