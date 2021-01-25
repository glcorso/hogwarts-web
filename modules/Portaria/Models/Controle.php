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
 * Model Controle
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models\Controle
 * @author     William Mascarello <william.mascarello@lideresistemas.com.br>
 * @copyright  2020 Lidere Sistemas
 * @license    Copyright (c) 2020
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Controle extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_controles';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'entrada' // date
        , 'saida' // date
        , 'pessoa' // 70
        , 'veiculo' // fk veiculo
        , 'destino' // 70
        , 'km_saida' // 70
        , 'km_entrada' // 70
        , 'empresa' // 70
        , 'placa' // 10
        , 'tipo' // 0 ou 1
        , 'tfuncionario_id' // fk funcionarios
        , 'veiculo_id'
        , 'assunto_id'
    ];
    
    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public static function criar($input = null)
    {
        $controle = null;
        try {

            $controle = new Controle();

            $controle->pessoa = !empty($input['pessoa']) ? $input['pessoa'] : '';

            if ($input['tipo'] == 1) {
                $controle->tfuncionario_id = $input['funcionario_id'];
                $controle->pessoa = '';
            } else {
                $controle->pessoa = $input['pessoa'];
                $controle->tfuncionario_id = '';
            }


            $controle->entrada = !empty($input['entrada']) ? $input['entrada'] : '';
            $controle->saida = !empty($input['saida']) ? $input['saida'] : '';
            $controle->veiculo = !empty($input['veiculo']) ? $input['veiculo'] : '';
            $controle->destino = !empty($input['destino']) ? $input['destino'] : '';
            $controle->km_saida = !empty($input['km_saida']) ? $input['km_saida'] : '';
            $controle->km_entrada = !empty($input['km_entrada']) ? $input['km_entrada'] : '';
            $controle->empresa = !empty($input['empresa']) ? $input['empresa'] : '';
            $controle->placa = !empty($input['placa']) ? $input['placa'] : '';
            $controle->tipo = !empty($input['tipo']) ? $input['tipo'] : '0';
            $controle->assunto_id = !empty($input['assunto_id']) ? $input['assunto_id'] : '';
            $controle->veiculo_id = !empty($input['veiculo_id']) ? $input['veiculo_id'] : '';
            $controle->save();
        } catch(\Exception $e) {

            var_export($e);die;
            //$e->get
            //throw new \Exception($e->getMessage());
             return false;
        }
        return $controle;
    }

    public static function atualizar($find = null, $input = null)
    {
        $updated = false;

        try {
            $find->update([
                  'entrada'  => !empty($input['entrada']) ? $input['entrada'] : ''
                , 'saida'  => !empty($input['saida']) ? $input['saida'] : ''
                , 'veiculo' => !empty($input['veiculo']) ? $input['veiculo'] : ''
                , 'destino' => !empty($input['destino']) ? $input['destino'] : ''
                , 'km_saida' => !empty($input['km_saida']) ? $input['km_saida'] : ''
                , 'km_entrada'  => !empty($input['km_entrada']) ? $input['km_entrada'] : ''
                , 'empresa' => !empty($input['empresa']) ? $input['empresa'] : ''
                , 'placa' => !empty($input['placa']) ? $input['placa'] : ''
                , 'tipo' => !empty($input['tipo']) ? $input['tipo'] : '0'
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


      /**
     * Retorna Funcionarios
     */
    public function funcionario()
    {
        return $this->belongsTo(
            '\Lidere\Modules\Portaria\Models\Funcionario',
            'tfuncionario_id'
        );
    }
}
