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
 * Classe de CRUD das ClienteAssistencia no schema Lidere do Oracle
 *
 * @author Sergio Sirtoli
 * @package Models
 * @category Model
 */
class ClienteAssistencia extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assistencia_clientes';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'nome'
        , 'cpf_cnpj'
        , 'telefone'
        , 'e_mail'
        , 'criado_por'
        , 'criado_em'
        , 'alterado_por'
        , 'alterado_em'
        , 'cep'
        , 'endereco'
        , 'nro'
        , 'complemento'
        , 'bairro'
        , 'cidade'
        , 'uf'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public static function criar($input = null)
    {
        $cliente = null;
        try {
            $cliente = new ClienteAssistencia();
            $cliente->nome = $input['nome'];
            $cliente->cpf_cnpj = $input['cpf_cnpj'];
            $cliente->telefone = $input['telefone'];
            $cliente->criado_por = $input['criado_por'];
            $cliente->criado_em = $input['criado_em'];
            $cliente->cep =   str_replace('-','',$input['cep']);
            $cliente->endereco = $input['endereco'];
            $cliente->nro = $input['nro'];
            $cliente->complemento = $input['complemento'];
            $cliente->bairro = $input['bairro'];
            $cliente->cidade = $input['cidade'];
            $cliente->uf = $input['uf'];
            $cliente->save();
        } catch(\Exception $e) {
            var_dump($e->getMessage());die;
            //$e->get
           // throw new \Exception($e->getMessage());
             return false;
        }
        return $cliente;
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
