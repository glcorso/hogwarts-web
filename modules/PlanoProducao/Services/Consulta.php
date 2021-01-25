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
namespace Lidere\Modules\PlanoProducao\Services;

use Lidere\Core;
use Lidere\Config;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Models\EmpresaParametros;
use Lidere\Modules\Services\Services;
use Lidere\Modules\PlanoProducao\Models\Consulta as consultaModel;
use Lidere\Modules\PlanoProducao\Models\Vinculo as vinculoModel;


/**
 * Consulta
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Consulta
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Consulta extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function index()
    {
        $consultaModel = new consultaModel();
        $vinculoModel = new vinculoModel();
        
        $this->filtros = array();
        $array_vinc = array();
        $total_geral = 0;

        /// P310 -climatizadores
        /// P330 -geladeiras
        
        $vinculos = $vinculoModel->getVinculos('result', array('i.usuario_id = '=> $_SESSION['usuario']['id']));

        if(!empty($vinculos)){
            foreach ($vinculos as $k => $vinc) {
                $array_vinc[$k] = $vinc['func_id'];
            }
        }

        $vinculo_usuario_logado = implode(',', $array_vinc);


        $filtro = !empty($vinculo_usuario_logado) ? array('v.func_id IN ' =>  '('.$vinculo_usuario_logado.')') : false;

        $registros = $consultaModel->getPlanoProducao('result',$filtro);
        $total_tela = count($registros);

        if(!empty($registros)){
            foreach ($registros as $reg) {
                $total_geral = $reg['total_geral'];
            }
        }


        $this->data['total_geral'] = $total_geral;
        $this->data['resultado'] = $registros;

        return $this->data;
    }

}