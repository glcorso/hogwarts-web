<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\AssistenciaExterna\Models;

// use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * Classe de CRUD das categorias no schema Lidere do Oracle
 *
 * @author Sergio Sirtoli
 * @package Models
 * @category Model
 */
class OrdemServicoStatus extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assist_ext_status';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'      
        , 'status_id'
        , 'responsavel_id'  
        , 'dt_status'       
        , 'ordem_id' 
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public static function criar($input = null)
    {
        $ordemStatus = null;
        try {
            $ordemStatus = new OrdemServicoStatus();
            $ordemStatus->status_id = $input['status_id'];
            $ordemStatus->responsavel_id = $_SESSION['usuario']['id'];
            $ordemStatus->dt_status  = date('d/m/Y H:i:s');
            $ordemStatus->ordem_id   = $input['ordem_id'];
            $ordemStatus->save();
        } catch(\Exception $e) {
          //  var_dump($e->getMessage());die;
            //throw new \Exception($e->getMessage());
             return false;
        }
        return $ordemStatus;
    }

    public static function excluir($id = null)
    {
        $deleted = false;
        try {
            $deleted = self::where('id', $id)->delete();
        } catch (\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $deleted;
    }

}
