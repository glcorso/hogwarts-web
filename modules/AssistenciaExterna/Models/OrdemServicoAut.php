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
class OrdemServicoAut extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assist_ext_aut_serv';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'      
        , 'servico_id'
        , 'aut_cat_id'  
        , 'valor'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public static function criar($input = null)
    {
        $ordemAut = null;
        try {
            $ordemAut = new OrdemServicoAut();
            $ordemAut->servico_id   = $input['servico_id'];
            $ordemAut->aut_cat_id   = $input['aut_cat_id'];
            $ordemAut->valor        = $input['valor'];
            $ordemAut->save();
        } catch(\Exception $e) {
          //  var_dump($e->getMessage());die;
            //throw new \Exception($e->getMessage());
            return false;
        }
        return $ordemAut;
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
