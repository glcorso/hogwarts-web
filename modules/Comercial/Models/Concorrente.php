<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Comercial\Models;

// use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * Classe de CRUD das categorias de concorrente no schema Lidere do Oracle
 *
 * @author Sergio Sirtoli
 * @package Models
 * @category Model
 */
class Concorrente extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_concorrentes';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'descricao'
        , 'categoria_id'
        , 'status'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public static function criar($input = null)
    {
        $concorrente = null;
        try {
            $concorrente = new Concorrente();
            $concorrente->descricao = $input['descricao'];
            $concorrente->status = $input['status'];
            $concorrente->categoria_id = $input['categoria_id'];
            $concorrente->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $concorrente;
    }

    public static function atualizar(Fase $find = null, $input = null)
    {
        $updated = false;

        try {
            $find->update([
                  'descricao'  => $input['descricao']
                , 'status' => $input['status']
                , 'categoria_id' => $input['categoria_id']
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

    public function Categoria()
    {
        return $this->belongsTo(CategoriaConcorrente::class);
    }

}
