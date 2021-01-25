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
namespace Lidere\Modules\Api\Models;

use PDO;
use Lidere\Core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Erp
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Erp\Models\Erp
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class ValidaSerie extends Model
{
    protected $core;

    protected $connection;

    public function __construct()
    {
        $this->core = Core::getInstance();
        $this->connection = 'oracle_'.$_SESSION['empresa']['id'];
    }

    public function retornaSerie($serie)
    {
        $serie = self::select('tl.id, tl.cod_lote serie, it.cod_item, it.desc_tecnica')
                           ->from('tlotes tl')
                           ->join('titens_lote ti', 'ti.lot_id', '=','tl.id')
                           ->join('titens_empr te', 'te.id', '=','ti.itempr_id')
                           ->join('titens it', 'it.id', '=','te.item_id')
                           ->where('tl.cod_lote', '=', $serie)
                           ->first();

        return !empty($serie) ? $serie->toArray() : null;
    }
}