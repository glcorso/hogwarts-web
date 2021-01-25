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
 * Classe para consulta, inclusão, edição e exclusão das empresas do sistema
 *
 * @package Models
 * @category Model
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class ModuloUsuario extends Model
{
    public $table = 'tmodulos_usuarios';

    public function modulo()
    {
        return $this->belongsTo('Lidere\Models\Modulo');
    }

    public function usuario()
    {
        return $this->belongsTo('Lidere\Models\Usuario');
    }
}
