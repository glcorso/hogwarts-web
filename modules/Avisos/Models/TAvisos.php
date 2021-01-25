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
namespace Lidere\Modules\Avisos\Models;

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
class TAvisos extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_avisos';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'id'
        , 'codigo'
        , 'descricao'
        , 'texto'
        , 'status'
        , 'criado_por'
        , 'criado_em'
        , 'ate'
    ];

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }


    public static function criar($input = null)
    {
        $aviso = null;
        try {
            $aviso = new TAvisos();
            $aviso->codigo = Core::sequencia('nr_seq_avisos');
            $aviso->descricao  = $input['descricao'];
            $aviso->criado_por = $_SESSION['usuario']['id'];
            $aviso->criado_em  = date('d/m/Y');
            $aviso->status     = $input['status'];
            $aviso->ate        = $input['ate'];
            $aviso->texto      = $input['texto'];

            //var_dump($aviso);die;
            $aviso->save();
        } catch(\Exception $e) {

            var_dump($e);die;
            //$e->get
            //throw new \Exception($e->getMessage());
             return false;
        }
        return $aviso;
    }

}
