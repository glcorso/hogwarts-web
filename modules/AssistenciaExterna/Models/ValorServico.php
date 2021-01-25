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
 * @subpackage AssistenciaExterna\Models\ValorServico
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class ValorServico extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assistencia_lista_serv';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'cod_lista' // 10
        , 'descricao' // 70
        , 'empr_id'
        , 'sit'
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

    public function valorServicoAgrup()
    {
        return $this->hasMany(
            '\Lidere\Modules\AssistenciaExterna\Models\ValorServicoAgrup',
            'lista_id'
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
        //echo "<pre>";
        //var_dump($input);die;
        $valorServico = null;
        try {
            $valorServico = new ValorServico();
            $valorServico->cod_lista = $input['cod_lista'];
            $valorServico->descricao = $input['descricao'];
            $valorServico->sit = $input['sit'];
            $valorServico->empr_id = $_SESSION['empresa']['empr_id'];
            if ($valorServico->save() && !empty($input['agrupadores'])) {
                foreach ($input['agrupadores'] as $agrupador) {
                    $valorServicoAgrup = new ValorServicoAgrup();
                    $valorServicoAgrup->lista_id = $valorServico->id;
                    $valorServicoAgrup->agrupador_id = $agrupador['agrupador_id'];
                    if ($valorServicoAgrup->save() && !empty($agrupador['precos'])) {
                        foreach ($agrupador['precos'] as $servico) {
                            $valorServicoPreco = new ValorServicoPreco();
                            $valorServicoPreco->list_agrp_id = $valorServicoAgrup->id;
                            $valorServicoPreco->servico_id   = $servico['servico_id'];
                            $valorServicoPreco->preco = $servico['preco'];
                            $valorServicoPreco->save();
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            var_dump($e->getMessage());die;
            dlog('error', $e->getMessage());
            $valorServico = false;
        }
        return $valorServico;
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
        
        $valorServico = null;
        try {
            $valorServico = ValorServico::find($input['id']);
            $valorServico->cod_lista = $input['cod_lista'];
            $valorServico->descricao = $input['descricao'];
            $valorServico->sit = $input['sit'];
            $valorServico->empr_id = $_SESSION['empresa']['empr_id'];
            if ($valorServico->save() && !empty($input['agrupadores'])) {
                $valorServico->valorServicoAgrup()
                    ->delete();
                if ($valorServico->save() && !empty($input['agrupadores'])) {
                    foreach ($input['agrupadores'] as $agrupador) {
                        $valorServicoAgrup = new ValorServicoAgrup();
                        $valorServicoAgrup->lista_id = $valorServico->id;
                        $valorServicoAgrup->agrupador_id = $agrupador['agrupador_id'];
                        if ($valorServicoAgrup->save() && !empty($agrupador['precos'])) {
                            foreach ($agrupador['precos'] as $servico) {
                                $valorServicoPreco = new ValorServicoPreco();
                                $valorServicoPreco->list_agrp_id = $valorServicoAgrup->id;
                                $valorServicoPreco->servico_id   = $servico['servico_id'];
                                $valorServicoPreco->preco = $servico['preco'];
                                $valorServicoPreco->save();
                            }
                        }
                    }
                }
            }
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $valorServico;
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
