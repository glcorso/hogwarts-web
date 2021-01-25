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
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Capsule\Manager as DB;
use Yajra\Oci8\Eloquent\OracleEloquent;

/**
 * Model ValorCategoriaPreco
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage AssistenciaExterna\Controllers\ValorCategoria
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class ValorCategoriaPreco extends OracleEloquent
{
    protected $core;
    protected $connection;

    public $table = 'tsdi_assistencia_precos_cat';
    public $timestamps = false;
    public $sequence = false;

    protected $fillable = [
          'categoria_id' // 19
        , 'lista_id' // 19
        , 'preco' // 19,8
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
     * Remove mascara do preço
     *
     * @param string $value preço da categoria
     *
     * @return void
     */
    public function setPrecoAttribute($value)
    {
        $preco = str_replace('R$ ', '', $value);
        $preco = str_replace('.', '', $preco);
        $preco = (float)str_replace(',', '.', $preco);

        $this->attributes['preco'] = $preco;
    }
}
