<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Upload\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Classe para consulta, inclusão, edição e exclusão do usuários
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Auxiliares\Models\Usuario
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Upload  extends Model
{
    public $table = 'tuploads';

    public $timestamps = true;

    protected $fillable = [
          'file_name'
        , 'file_type'
        , 'file_path'
        , 'full_path'
        , 'raw_name'
        , 'orig_name'
        , 'client_name'
        , 'file_ext'
        , 'file_size'
        , 'is_image'
        , 'image_width'
        , 'image_height'
        , 'image_type'
        , 'image_size_str'
    ];
}
