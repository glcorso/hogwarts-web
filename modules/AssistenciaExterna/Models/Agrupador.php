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
 * Model Valor por servico
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models\Agrupador
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Agrupador extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assist_ext_agrupador';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'descricao' // 70
        , 'situacao'
        , 'criado_por'
        , 'criado_em'
        , 'alterado_por'
        , 'alterado_em'
    ];

    /**
     * Construtor que define a conexão com o banco
     */
    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    /**
     * Retorna os valores dos serviços vinculados ao valor servico
     *
     * @return void
     */
    public function AgrupadorItem()
    {
        return $this->hasMany(
            '\Lidere\Modules\AssistenciaExterna\Models\AgrupadorItem',
            'agrupador_id'
        );
    }

    /**
     * Criar o registro valor por servico
     *
     * @param array $input Dados do formulário
     *
     * @return void
     */
    public static function criar($input = null)
    {
        
        //var_dump($input);die;
        $agrupador = null;
        try {
            $agrupador = new Agrupador();
            $agrupador->descricao  = $input['descricao'];
            $agrupador->situacao   = $input['sit'];
            $agrupador->criado_por = $_SESSION['usuario']['id'];
            $agrupador->criado_em  = date('d/m/Y H:i:s');
            if ($agrupador->save() && !empty($input['itens'])) {
                foreach ($input['itens'] as $item) {
                    $agrupadorItem = new AgrupadorItem();
                    $agrupadorItem->agrupador_id = $agrupador->id;
                    $agrupadorItem->item_id = $item['item_id'];
                    $agrupadorItem->save();
                }
            }
        } catch(\Exception $e) {
            var_dump($e->getMessage());die;
            dlog('error', $e->getMessage());
            $agrupador = false;
        }
        return $agrupador;
    }

    /**
     * Atualizar o registro
     *
     * @param array $input Dados do formulário
     *
     * @return void
     */
    public static function atualizar($input = null)
    {
        

        $agrupador = null;
        try {
            $agrupador = Agrupador::find($input['id']);
            $agrupador->descricao  = $input['descricao'];
            $agrupador->situacao   = $input['sit'];
            $agrupador->alterado_por = $_SESSION['usuario']['id'];
            $agrupador->alterado_em  = date('d/m/Y H:i:s');
            if ($agrupador->save() && !empty($input['itens'])) {
                $agrupador->AgrupadorItem()
                    ->delete();
                foreach ($input['itens'] as $item) {
                    $agrupadorItem = new AgrupadorItem();
                    $agrupadorItem->agrupador_id = $agrupador->id;
                    $agrupadorItem->item_id = $item['item_id'];
                    $agrupadorItem->save();
                }
            }
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $agrupador;
    }

    /**
     * Remove o registro
     *
     * @param integer $id Id do registro
     *
     * @return void
     */
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
