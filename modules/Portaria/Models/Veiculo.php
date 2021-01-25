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
namespace Lidere\Modules\Portaria\Models;

// use PDO;
use Lidere\Core;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * Model Veículos
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models\Veiculo
 * @author     William Mascarello <william.mascarello@lideresistemas.com.br>
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Veiculo extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_veiculos';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'marca' // 40
        , 'modelo' // 40
        , 'placa' // 10
        , 'situacao'
        , 'controle_km'
        , 'km_atual'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public static function criar($input = null)
    {
        $veiculo = null;
        try {
            $veiculo = new Veiculo();
            $veiculo->marca = $input['marca'];
            $veiculo->modelo = $input['modelo'];
            $veiculo->placa = $input['placa'];
            $veiculo->situacao = $input['situacao'];
            $veiculo->controle_km = $input['controle_km'];
            $veiculo->km_atual = $input['km_atual'];
            $veiculo->save();
        } catch(\Exception $e) {
            //$e->get
            //throw new \Exception($e->getMessage());
             return false;
        }
        return $veiculo;
    }

    public static function atualizar(Fase $find = null, $input = null)
    {
        $updated = false;

        try {
            $find->update([
                'modelo'  => $input['modelo']
                , 'marca'  => $input['marca']
                , 'placa'  => $input['placa']
                , 'situacao' => $input['situacao']
                , 'controle_km' => $input['controle_km']
                , 'km_atual' => $input['km_atual']
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
