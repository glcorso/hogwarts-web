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
class Modulo extends Model
{
    public $table = 'tmodulos';

    //$modulo = \Lidere\Models\Modulo::find(1);
    //dd($modulo->usuario()->get()->toArray());
    public function usuario()
    {
        return $this->belongsToMany('Lidere\Models\Usuario', 'tmodulos_usuarios')
                    ->withPivot('empresa_empr_id', 'permissao');
    }

    public function perfil()
    {
        return $this->belongsToMany('Lidere\Modules\Auxiliares\Models\Perfil', 'tmodulos_perfil')
                    ->withPivot('empresa_empr_id', 'permissao');
    }
}
