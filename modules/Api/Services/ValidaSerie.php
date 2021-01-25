<?php

namespace Lidere\Modules\Api\Services;

use stdClass;
use Lidere\Core;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Api\Models\ValidaSerie as validaSerieModel;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Ambientes
 *
 * @package Lidere\Modules
 * @subpackage Api\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class ValidaSerie extends Services
{
   

    public function read()
    {       
        $validaSerieModel = new validaSerieModel();
        $input = $this->input;

        if(!empty($input->serie)){
            $retorno = $validaSerieModel->retornaSerie($input->serie);


            $this->data = [
                'serieId' => $serie = !empty($retorno['id']) ? $retorno['id'] : false,
                'error'   => $error = !empty($retorno['id']) ? false : true,
                'item'    => $item = !empty($retorno['id']) ? $retorno['cod_item'].' - '.$retorno['desc_tecnica'] : false
            ];
        }

        

        return $this->data;
    }

}
