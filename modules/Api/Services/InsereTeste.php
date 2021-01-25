<?php

namespace Lidere\Modules\Api\Services;

use stdClass;
use Lidere\Core;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Api\Models\InsereTeste as insereTesteModel;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Insere Teste
 *
 * @package Lidere\Modules
 * @subpackage Api\Services
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class InsereTeste extends Services
{
    public function create()
    {       
        $input = $this->input;

        if(!empty($input)){
        
            $created = false;
            try {
                $model = insereTesteModel::create([
                    ,'dt_teste' => $this->input['dt_teste']
                    ,'operador' => $this->input['operador']
                    ,'serie_id ' => $this->input['serie_id']
                    ,'obs_iniciais' => substr($this->input['obs_iniciais'],1,200);
                    ,'dt_fim' => $this->input['dt_fim']
                    ,'serie_compressor' => trim($this->input['serie_compressor'])
                ]);
                $created = true;
            } catch(\Exception | \Yajra\Pdo\Oci8\Exceptions\Oci8Exception | \Illuminate\Database\QueryException $e) {
                $created = false;
            }
            return $created;
        }

        return false;
    }
}
