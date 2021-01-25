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
 * Model Valor por Categoria
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Models\ValorCategoria
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class ValorCategoria extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assistencia_lista_cat';
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
     * Retorna os valores dos serviços vinculados ao valor categoria
     *
     * @return void
     */
    public function valorCategoriaPrecos()
    {
        return $this->hasMany(
            '\Lidere\Modules\AssistenciaExterna\Models\ValorCategoriaPreco',
            'lista_id'
        );
    }

    /**
     * Criar o registro valor por categoria
     *
     * @param array $input Dados do formulário
     *
     * @return void
     */
    public static function criar($input = null)
    {
        $valorCategoria = null;
        try {
            $valorCategoria = new ValorCategoria();
            $valorCategoria->cod_lista = $input['cod_lista'];
            $valorCategoria->descricao = $input['descricao'];
            $valorCategoria->sit = $input['sit'];
            $valorCategoria->empr_id = $_SESSION['empresa']['empr_id'];
            if ($valorCategoria->save() && !empty($input['precos'])) {
                foreach ($input['precos'] as $servico) {
                    $valorCategoriaPreco = new ValorCategoriaPreco();
                    $valorCategoriaPreco->lista_id = $valorCategoria->id;
                    $valorCategoriaPreco->categoria_id = $servico['categoria_id'];
                    $valorCategoriaPreco->preco = $servico['preco'];
                    $valorCategoriaPreco->save();
                }
            }
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            $valorCategoria = false;
        }
        return $valorCategoria;
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
        $valorCategoria = null;
        try {
            $valorCategoria = ValorCategoria::find($input['id']);
            $valorCategoria->cod_lista = $input['cod_lista'];
            $valorCategoria->descricao = $input['descricao'];
            $valorCategoria->sit = $input['sit'];
            $valorCategoria->empr_id = $_SESSION['empresa']['empr_id'];
            if ($valorCategoria->save() && !empty($input['precos'])) {
                $valorCategoria->valorCategoriaPrecos()
                    ->delete();
                foreach ($input['precos'] as $servico) {
                    $valorCategoriaPreco = new ValorCategoriaPreco();
                    $valorCategoriaPreco->lista_id = $valorCategoria->id;
                    $valorCategoriaPreco->categoria_id = $servico['categoria_id'];
                    $valorCategoriaPreco->preco = $servico['preco'];
                    $valorCategoriaPreco->save();
                }
            }
        } catch(\Exception $e) {
            dlog('error', $e->getMessage());
            throw new \Exception($e->getMessage());
        }
        return $valorCategoria;
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
