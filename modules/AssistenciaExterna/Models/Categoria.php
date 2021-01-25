<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2019
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\AssistenciaExterna\Models;

// use PDO;
use Lidere\Core;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * Model Categoria
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models\Categoria
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Categoria extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assistencia_categorias';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'cod_cat' // 10
        , 'descricao' // 70
        , 'empr_id'
        , 'sit'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    /**
     * Retorna os valores das categorias vinculada a categoria
     *
     * @return void
     */
    public function valorCategoriaPreco()
    {
        return $this->hasMany(
            '\Lidere\Modules\AssistenciaExterna\Models\ValorCategoriaPreco',
            'categoria_id'
        );
    }

    public static function criar($input = null)
    {
        $categoria = null;
        try {
            $categoria = new Categoria();
            $categoria->cod_cat = $input['cod_cat'];
            $categoria->descricao = $input['descricao'];
            $categoria->sit = $input['sit'];
            $categoria->empr_id = $_SESSION['empresa']['id'];
            $categoria->save();
        } catch(\Exception $e) {
            //$e->get
            //throw new \Exception($e->getMessage());
             return false;
        }
        return $categoria;
    }

    public static function atualizar(Fase $find = null, $input = null)
    {
        $updated = false;

        try {
            $find->update([
                  'cod_cat'  => $input['cod_cat']
                , 'descricao'  => $input['descricao']
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
