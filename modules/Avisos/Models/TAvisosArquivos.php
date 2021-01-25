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
class TAvisosArquivos extends OracleEloquent
{
    
    protected $core;
    protected $connection;

    public $table = 'tsdi_avisos_arquivos';
    public $timestamps = false;
    public $sequence = false;

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
          'id'
        , 'aviso_id'
        , 'arquivo'
        , 'tipo'
    ];


    public static function criar($input = null)
    {
        $avisoArquivos = null;
        try {
            $avisoArquivos = new TAvisosArquivos();
            $avisoArquivos->aviso_id = $input['aviso_id'];
            $avisoArquivos->arquivo  = $input['arquivo'];
            $avisoArquivos->tipo     = $input['tipo'];
            $avisoArquivos->save();
        } catch(\Exception $e) {

            var_dump($e->getMessage());die;
            //$e->get
            //throw new \Exception($e->getMessage());
             return false;
        }
        return $avisoArquivos;
    }
}
