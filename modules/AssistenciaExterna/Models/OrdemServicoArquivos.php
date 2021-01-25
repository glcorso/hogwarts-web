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
class OrdemServicoArquivos extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assist_ext_ordens_arq';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'ordem_id'
        , 'criado_por'
        , 'criado_em'
        , 'arquivo'
        , 'tipo'
        , 'tipo_anexo'
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
            $ordemAut = new OrdemServicoArquivos();
            $ordemAut->ordem_id     = $input['ordem_id'];
            $ordemAut->criado_por   = $_SESSION['usuario']['id'];
            $ordemAut->criado_em    = date('d/m/Y H:i:s');
            $ordemAut->arquivo      = $input['arquivo'];
            $ordemAut->tipo         = $input['tipo'];
            $ordemAut->tipo_anexo   = $input['tipo_anexo'];
            $ordemAut->save();
        } catch(\Exception $e) {
            var_dump($e->getMessage());die;
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
