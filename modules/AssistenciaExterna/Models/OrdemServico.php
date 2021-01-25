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
class OrdemServico extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assist_ext_ordens_serv';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'num_ordem'
        , 'cliente_assistencia_id'
        , 'serie_id'
        , 'garantia'
        , 'item_id'                 
        , 'obs_cond_equip'
        , 'obs_relato_cli' 
        , 'obs_relato_tec'         
        , 'criado_por'              
        , 'criado_em'              
        , 'alterado_por'           
        , 'alterado_em' 
        , 'tp_item'
        , 'est_id'   
        , 'valor_aprovado'
        , 'est_id_ass'
        , 'obs_aprovacao'
        , 'obs_conclusao'
        , 'dt_fabricacao'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public static function criar($input = null)
    {
        $ordem = null;
        try {
            $ordem = new OrdemServico();
            $ordem->num_ordem = $input['num_ordem'];

            if(!empty($input['cliente_assistencia_id'])){
                $ordem->cliente_assistencia_id = $input['cliente_assistencia_id'];
            }

            if(!empty($input['serie_id'])){
                $ordem->serie_id         = $input['serie_id'];
            }

            $ordem->garantia       = $input['garantia'];
            $ordem->item_id        = $input['item_id'];
            $ordem->obs_cond_equip = $input['obs_cond_equip'];
            $ordem->obs_relato_cli = $input['obs_relato_cli'];
            $ordem->criado_por     = $_SESSION['usuario']['id'];
            $ordem->criado_em      = date('d/m/Y H:i:s');
            $ordem->tp_item        = $input['tp_item'];
            
            if(!empty($input['dt_fabricacao'])){
                $ordem->dt_fabricacao  = $input['dt_fabricacao'];
            }

            if(!empty($input['est_id'])){
                $ordem->est_id         = $input['est_id'];
            }

            $ordem->save();
        } catch(\Exception $e) {
            //$e->get
            //throw new \Exception($e->getMessage());

            var_dump($e->getMessage());die;
             return false;
        }
        return $ordem;
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
