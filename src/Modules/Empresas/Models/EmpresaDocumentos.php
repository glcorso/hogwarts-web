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
 * EmpresaDocumentos
 *
 * @package Lidere\Modules
 * @subpackage Empresas\Models
 * @author Ramon Barros
 */
class EmpresaDocumentos extends Model
{
    public $table = 'tempresa_documentos';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'id'
        ,'arquivo'
        ,'tipo'
        ,'empresa_id'
    ];
}
