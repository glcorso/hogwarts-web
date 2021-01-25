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
 * GeracaoProtocolo
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\GeracaoProtocolo
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class GeracaoProtocolo extends Services
{

    public function index()
    {
    	$auxiliaresModel  = new Auxiliares();
        $atendimentoModel = new atendimentoModel(); 

    	$usuarios = $auxiliaresModel->usuarios('result');
        $status = $atendimentoModel->getAssistenciaStatus();   
        $this->data['status'] = $status;

    	$this->data['usuarios'] = $usuarios;
    	return $this->data;
    }

    
    public function add()
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $retorno = false;
        $registro = array();
        $atendimentoModel = new atendimentoModel(); 

       //s echo "<pre>";
       // var_export($this->input);die;

        if(!empty($this->input)){
            foreach ($this->input['item'] as $reg) {
      

                $registro['protocolo'] =  $this->geraProtocolo();
                $registro['registro_id'] = !empty($this->input['registro_id']) ? $this->input['registro_id'] : 'NULL';
                $registro['cliente_origem_id']  = !empty($reg['cliente_origem_id']) ? $reg['cliente_origem_id'] : 'NULL';
                $registro['cliente_assistencia_id'] = !empty($this->input['cliente_assistencia_id']) ? $this->input['cliente_assistencia_id'] : 'NULL';
                $registro['serie_id']  = !empty($reg['serie_id']) ? $reg['serie_id'] : 'NULL';
                $registro['sequencial_id']  = !empty($reg['sequencial_id']) ? $reg['sequencial_id'] : 'NULL';
                
                $registro['status_id'] = $this->input['status_id'];
                $registro['responsavel_id'] = $this->input['responsavel_id'];

                $item = explode('-', $reg['item_id']);
                if($item['0'] == 'E'){
                    $registro['item_id']  = $item['1'];
                    $registro['item_interno_id']  = 'NULL';
                }else{
                    $registro['item_id']  = 'NULL';
                    $registro['item_interno_id']  = $item['1'];
                }
               
                $registro['dt_fabricacao'] = !empty($reg['dt_fabricacao']) ? "'".$reg['dt_fabricacao']."'" : 'NULL';
                $registro['dt_compra'] = !empty($reg['dt_compra']) ? "'".$reg['dt_compra']."'" : 'NULL';
                $registro['motivo_id'] = !empty($reg['motivo_id']) ? $reg['motivo_id'] : 'NULL';
                $registro['defeito_principal_id'] = !empty($reg['defeito_principal_id']) ? $reg['defeito_principal_id'] : 'NULL';
                $registro['forn_defeito_id'] = !empty($this->input['forn_defeito_id']) ? $this->input['forn_defeito_id'] : 'NULL';
                $registro['obs_interna'] = !empty($this->input['obs_interna']) ? "'".$this->input['obs_interna']."'" : "'Protocolo Gerado Automaticamente.'";
                $registro['obs_cliente'] = !empty($this->input['obs_cliente']) ? "'".$this->input['obs_cliente']."'" : "'Protocolo Gerado Automaticamente.'";
                $registro['cliente_envio_id'] = !empty($this->input['cliente_envio_id']) ? "'".$this->input['cliente_envio_id']."'" : 'NULL';
                $registro['cliente_assistencia_erp_id'] = !empty($this->input['cliente_assistencia_erp_id']) ? $this->input['cliente_assistencia_erp_id'] : 'NULL';

                $registro['nota_fiscal'] = !empty($this->input['nota_fiscal']) ? "'".$this->input['nota_fiscal']."'" : 'NULL';

                $atendimento_id = $atendimentoModel->cadastrarRegistroAtendimento($registro);

                if(!empty($this->input['cliente_assistencia_erp_id'])){
                    $registro_cliente['telefone'] = !empty($this->input['telefone']) ? "'".$this->input['telefone']."'" : 'NULL';
                    $registro_cliente['e_mail'] = !empty($this->input['e_mail']) ? "'".$this->input['e_mail']."'" : 'NULL';
                    $registro_cliente['est_id'] = $this->input['cliente_assistencia_erp_id'];
                    /**
                        Atualiza Informações Telefone Cliente ERP
                    */
                    $atendimentoModel->insereAtualizaTelefoneClienteErp($registro_cliente);
                }
            }
           
            
        }    
       
        return !empty($atendimento_id) ? true : false;
    }


    private function geraProtocolo() {

        $protocolo = '03'.substr(rand(),0,4).date('d').date('m').date('y');
        return $protocolo;

    }

}
