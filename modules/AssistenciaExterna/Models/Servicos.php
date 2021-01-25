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
 * Classe de CRUD das servicos no schema Lidere do Oracle
 *
 * @author Sergio Sirtoli
 * @package Models
 * @category Model
 */
class Servicos extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assistencia_servicos';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'cod_serv'
        , 'descricao'
        , 'empr_id'
        , 'sit'
        , 'categoria_id'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public static function criar($input = null)
    {
        $servico = null;
        try {
            $servico = new Servicos();
            $servico->cod_serv = $input['cod_serv'];
            $servico->descricao = $input['descricao'];
            $servico->sit = $input['sit'];
            $servico->categoria_id = $input['categoria_id'];
            $servico->empr_id = $_SESSION['empresa']['id'];
            $servico->save();
        } catch(\Exception $e) {

             //var_dump( $e->getMessage());die;
            //$e->get
           // throw new \Exception($e->getMessage());
             return false;
        }
        return $servico;
    }

    public static function atualizar(Fase $find = null, $input = null)
    {
        $updated = false;

        try {
            $find->update([
                  'cod_serv'  => $input['cod_serv']
                , 'descricao'  => $input['descricao']
                , 'categoria_id'  => $input['categoria_id']
                , 'sit' => $input['sit']
            ]);
            $updated = true;
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $updated;
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
