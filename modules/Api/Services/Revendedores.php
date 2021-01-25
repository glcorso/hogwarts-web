<?php

namespace Lidere\Modules\Api\Services;

use stdClass;
use Lidere\Core;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Api\Models\Revendedor as revendedorModel;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Revendedores
 *
 * @package Lidere\Modules
 * @subpackage Api\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class Revendedores extends Services
{
   

    public function list()
    {       
        
        $this->data['resultado'] = revendedorModel::get();
        
        return $this->data;
    }

}
