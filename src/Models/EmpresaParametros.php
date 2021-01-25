<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta dos logs do sistema
 *
 * @package Models
 * @category Model
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class EmpresaParametros extends Model
{

    public $table = 'tempresa_parametros';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parametro_id',
        'empresa_id',
        'valor',
        'data_edicao'
    ];
}
