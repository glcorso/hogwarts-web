<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2019
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Assistencia\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Assistencia\Models\Atendimento as atendimentoModel;

/**
 * Painel
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Painel
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Painel extends Services
{

    public function index()
    {
        $atendimentoModel = new atendimentoModel(); 
        $auxiliaresModel  = new Auxiliares();

        $registros = $atendimentoModel->getAssistenciaRegistro('result',array('v.material_recebido = ' => '1', 'v.status_id' => ' <> 3'));

        //echo "<pre>";
       // var_dump($registros);die;

        $max_dias = Core::parametro('assistencia_tempo_em_dias_retorno');
        if(!empty($registros)){
            foreach ($registros as $k => &$registro) {
               // var_dump($registro);die;
                $registro['data_max_saida'] =  date('d/m/Y', strtotime(Core::data2Date($registro['recebido_em']). ' + '.$max_dias.' days'));    


                //var_dump(strtotime(Core::data2Date($registro['data_max_saida'])) );die;
                if(  strtotime(Core::data2Date($registro['data_max_saida']))  == strtotime((string)date('Y-m-d')) ){
                    $registro['cor'] = 'yellow';
                }elseif ( strtotime(Core::data2Date($registro['data_max_saida']))  < strtotime((string)date('Y-m-d'))  ){
                    $registro['cor'] = 'red';
                }

            }

        }

        $this->data['registros'] = !empty($registros) ? $registros : false;

        return $this->data;
    }

}