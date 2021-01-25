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
class CategoriaConcorrente extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_comercial_cat_conc';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'descricao'
        , 'status'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public static function criar($input = null)
    {
        $categoria = null;
        try {
            $categoria = new CategoriaConcorrente();
            $categoria->descricao = $input['descricao'];
            $categoria->status = $input['status'];
            $categoria->save();
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $categoria;
    }

    public static function atualizar(Fase $find = null, $input = null)
    {
        $updated = false;

        try {
            $find->update([
                  'descricao'  => $input['descricao']
                , 'status' => $input['status']
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
