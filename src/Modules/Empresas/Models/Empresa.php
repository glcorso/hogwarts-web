<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Empresas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Empresa
 *
 * @package Lidere\Modules
 * @subpackage Empresas\Models
 * @author Ramon Barros
 */
class Empresa extends Model
{
    public $table = 'tempresas';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'id'
        ,'razao_social'
        ,'nome_fantasia'
        ,'dominio'
        ,'diretorio'
        ,'situacao'
        ,'cor_principal'
        ,'api_token'
        ,'oracle_host'
        ,'oracle_porta'
        ,'oracle_sid'
        ,'oracle_usuario'
        ,'oracle_senha'
        ,'empr_id'
        ,'empr_nfe'
    ];
}
