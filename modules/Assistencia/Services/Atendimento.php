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
 * Atendimento
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\Atendimento
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Atendimento extends Services
{

    public function index($id = false)
    {
        $atendimentoModel = new atendimentoModel(); 

        if(!empty($id)){
            $registro = $atendimentoModel->getAssistenciaRegistro('row', array('v.id = ' => $id ));  
            $arquivos = $atendimentoModel->getAssistenciaArquivos($id);  

            if(!empty($registro)){
                if(!empty($registro['item_interno_id'])){
                    $registro['item_id'] = 'I-'.$registro['item_interno_id'];
                }else{
                    $registro['item_id'] = 'E-'.$registro['item_id'];
                }
            }
            if(!empty($arquivos)){
                foreach ($arquivos as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['registro_id'].'!'.$arq['arquivo']);
                }
            }

        }

        $auxiliaresModel  = new Auxiliares();

        $usuarios = $auxiliaresModel->usuarios('result');
        $status = $atendimentoModel->getAssistenciaStatus();   
        $status_novo_chamado = Core::parametro('assistencia_status_novo_chamado');

        $this->data['status_novo_chamado']   = $status_novo_chamado; 
        $this->data['status']   = $status;
        $this->data['usuarios'] = $usuarios;
        $this->data['arquivos'] = !empty($arquivos) ? $arquivos : false;
        $this->data['registro'] = !empty($registro) ? $registro : false;

        return $this->data;
    }

    public function add($files = false)
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $retorno = false;
        $registro = array();
        $atendimentoModel = new atendimentoModel(); 

        if(!empty($this->input)){
           
            $registro['protocolo'] =  $this->input['protocolo'];
            $registro['cliente_assistencia_id'] = !empty($this->input['cliente_assistencia_id']) ? $this->input['cliente_assistencia_id'] : 'NULL';
            $registro['registro_id'] = !empty($this->input['registro_id']) ? $this->input['registro_id'] : 'NULL';
            $registro['cliente_origem_id']  = !empty($this->input['cliente_origem_id']) ? $this->input['cliente_origem_id'] : 'NULL';
            $registro['serie_id']  = !empty($this->input['serie_id']) ? $this->input['serie_id'] : 'NULL';
            $registro['sequencial_id']  = !empty($this->input['sequencial_id']) ? $this->input['sequencial_id'] : 'NULL';
            $registro['status_id'] = $this->input['status_id'];
            $registro['responsavel_id'] = $this->input['responsavel_id'];

            $item = explode('-', $this->input['item_id']);
            if($item['0'] == 'E'){
                $registro['item_id']  = $item['1'];
                $registro['item_interno_id']  = 'NULL';
            }else{
                $registro['item_id']  = 'NULL';
                $registro['item_interno_id']  = $item['1'];
            }
           
            $registro['dt_fabricacao'] = !empty($this->input['dt_fabricacao']) ? "'".$this->input['dt_fabricacao']."'" : 'NULL';
            $registro['dt_compra'] = !empty($this->input['dt_compra']) ? "'".$this->input['dt_compra']."'" : 'NULL';
            $registro['motivo_id'] = !empty($this->input['motivo_id']) ? $this->input['motivo_id'] : 'NULL';
            $registro['defeito_principal_id'] = !empty($this->input['defeito_principal_id']) ? $this->input['defeito_principal_id'] : 'NULL';
            $registro['forn_defeito_id'] = !empty($this->input['forn_defeito_id']) ? $this->input['forn_defeito_id'] : 'NULL';
            $registro['obs_interna'] = !empty($this->input['obs_interna']) ? "'".$this->input['obs_interna']."'" : 'NULL';
            $registro['obs_cliente'] = !empty($this->input['obs_cliente']) ? "'".$this->input['obs_cliente']."'" : 'NULL';
            $registro['cliente_assistencia_erp_id'] = !empty($this->input['cliente_assistencia_erp_id']) ? $this->input['cliente_assistencia_erp_id'] : 'NULL';

            $registro['cliente_envio_id'] = !empty($this->input['cliente_envio_id']) ? "'".$this->input['cliente_envio_id']."'" : 'NULL';

            $registro['nota_fiscal'] = !empty($this->input['nota_fiscal']) ? "'".$this->input['nota_fiscal']."'" : 'NULL';

            $atendimento_id = $atendimentoModel->cadastrarRegistroAtendimento($registro);

            if(!empty($this->input['cliente_assistencia_erp_id'])){
                $registro_cliente['telefone'] = !empty($this->input['telefone']) ? "'".$this->input['telefone']."'" : 'NULL';
                $registro_cliente['e_mail'] = !empty($this->input['e_mail']) ? "'".$this->input['e_mail']."'" : 'NULL';
                $registro_cliente['est_id'] = $registro['cliente_assistencia_erp_id'];
                /**
                    Atualiza Informações Telefone Cliente ERP
                */
                $atendimentoModel->insereAtualizaTelefoneClienteErp($registro_cliente);
            
            }else{

                /**
                    Atualiza Informações Telefone Cliente Assistência
                */

                $registro_cliente['telefone'] = !empty($this->input['telefone']) ? "'".$this->input['telefone']."'" : 'NULL';
                 $registro_cliente['nome'] = !empty($this->input['nome']) ? "'".$this->input['nome']."'" : 'NULL';
                $registro_cliente['e_mail'] = !empty($this->input['e_mail']) ? "'".$this->input['e_mail']."'" : 'NULL';
                $registro_cliente['cliente_id'] = $this->input['cliente_assistencia_id'];

                $atendimentoModel->insereAtualizaClienteAssistencia($registro_cliente);
            }


            if(!empty($files)){


                $file_ary = array();
                $file_count = count($files['files']['name']);
                $file_keys = array_keys($files['files']);

                for ($i=0; $i<$file_count; $i++) {
                    foreach ($file_keys as $key) {
                        $file_ary[$i][$key] = $files['files'][$key][$i];
                    }
                }          



                foreach ($file_ary as $file) {
                    $k = 0; 
                    if ( $file['size'] > 0 && $file['error'] === 0 ) {
                        $ins_file['registro_id']  = $atendimento_id;
                        $ins_file['tipo']    = "'".$file['type']."'";
                        $ins_file['arquivo'] = "'".$k.$atendimento_id."-".$file['name']."'";
                        move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica'.DS.$k.$atendimento_id."-".$file['name']);     
                        $atendimentoModel->insereArquivo($ins_file);
                        $k++;
                    }    

                }     
            }   

        }    
       
        return $atendimento_id;
    }


    public function edit($files = false)
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $retorno = false;
        $registro = array();
        $atendimentoModel = new atendimentoModel(); 

        if(!empty($this->input)){
            
            $registro['id'] = $this->input['id'];
            $registro['protocolo'] =  $this->input['protocolo'];
            $registro['cliente_assistencia_id'] = !empty($this->input['cliente_assistencia_id']) ? $this->input['cliente_assistencia_id'] : 'NULL';
            $registro['registro_id'] = !empty($this->input['registro_id']) ? $this->input['registro_id'] : 'NULL';
            $registro['cliente_origem_id']  = !empty($this->input['cliente_origem_id']) ? $this->input['cliente_origem_id'] : 'NULL';
            $registro['serie_id']  = !empty($this->input['serie_id']) ? $this->input['serie_id'] : 'NULL';
            $registro['sequencial_id']  = !empty($this->input['sequencial_id']) ? $this->input['sequencial_id'] : 'NULL';

            $registro['status_id'] = $this->input['status_id'];

            $item = explode('-', $this->input['item_id']);

            if($item['0'] == 'E'){
                $registro['item_id']  = $item['1'];
                $registro['item_interno_id']  = 'NULL';
            }else{
                $registro['item_id']  = 'NULL';
                $registro['item_interno_id']  = $item['1'];
            }

            $registro['dt_fabricacao'] = !empty($this->input['item_id']) ? "'".$this->input['dt_fabricacao']."'" : 'NULL';
            $registro['dt_compra'] = !empty($this->input['dt_compra']) ? "'".$this->input['dt_compra']."'" : 'NULL';
            $registro['motivo_id'] = !empty($this->input['motivo_id']) ? $this->input['motivo_id'] : 'NULL';
            $registro['defeito_principal_id'] = !empty($this->input['defeito_principal_id']) ? $this->input['defeito_principal_id'] : 'NULL';
            $registro['obs_interna'] = !empty($this->input['obs_interna']) ? "'".$this->input['obs_interna']."'" : 'NULL';
            $registro['obs_cliente'] = !empty($this->input['obs_cliente']) ? "'".$this->input['obs_cliente']."'" : 'NULL';
            $registro['cliente_assistencia_erp_id'] = !empty($this->input['cliente_assistencia_erp_id']) ? $this->input['cliente_assistencia_erp_id'] : 'NULL';
            $registro['responsavel_id'] = $this->input['responsavel_id'];

            
            $registro['cliente_envio_id'] = !empty($this->input['cliente_envio_id']) ? "'".$this->input['cliente_envio_id']."'" : 'NULL';


            $registro['nota_fiscal'] = !empty($this->input['nota_fiscal']) ? "'".$this->input['nota_fiscal']."'" : 'NULL';

            $atendimento_id = $atendimentoModel->atualizarRegistroAtendimento($registro);

            if(!empty($this->input['cliente_assistencia_erp_id'])){
                $registro_cliente['telefone'] = !empty($this->input['telefone']) ? "'".$this->input['telefone']."'" : 'NULL';
                $registro_cliente['e_mail'] = !empty($this->input['e_mail']) ? "'".$this->input['e_mail']."'" : 'NULL';
                $registro_cliente['est_id'] = $registro['cliente_assistencia_erp_id'];
                /**
                    Atualiza Informações Telefone Cliente ERP
                */
                $atendimentoModel->insereAtualizaTelefoneClienteErp($registro_cliente);
            
            }else{

                /**
                    Atualiza Informações Telefone Cliente Assistência
                */

                $registro_cliente['telefone'] = !empty($this->input['telefone']) ? "'".$this->input['telefone']."'" : 'NULL';
                 $registro_cliente['nome'] = !empty($this->input['nome']) ? "'".$this->input['nome']."'" : 'NULL';
                $registro_cliente['e_mail'] = !empty($this->input['e_mail']) ? "'".$this->input['e_mail']."'" : 'NULL';
                $registro_cliente['cliente_id'] = $this->input['cliente_assistencia_id'];

                $atendimentoModel->insereAtualizaClienteAssistencia($registro_cliente);
            }


            if(!empty($files)){


                $file_ary = array();
                $file_count = count($files['files']['name']);
                $file_keys = array_keys($files['files']);

                for ($i=0; $i<$file_count; $i++) {
                    foreach ($file_keys as $key) {
                        $file_ary[$i][$key] = $files['files'][$key][$i];
                    }
                }          

                foreach ($file_ary as $file) {
                    $k = 0; 
                    if ( $file['size'] > 0 && $file['error'] === 0 ) {
                        $ins_file['registro_id']  = $atendimento_id;
                        $ins_file['tipo']    = "'".$file['type']."'";
                        $ins_file['arquivo'] = "'".$k.$atendimento_id."-".$file['name']."'";
                        move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica'.DS.$k.$atendimento_id."-".$file['name']);     
                        $atendimentoModel->insereArquivo($ins_file);
                        $k++;
                    }    

                }     
            }   

        }    
       
        return $atendimento_id;
    }


    public function excluirArquivo()
    {

        $atendimentoModel = new atendimentoModel(); 

        if(!empty($this->input['id'])){
            $excluiu = $atendimentoModel->excluirArquivo($this->input['id']);
            return $excluiu;
        }else{  
            return false;
        }
    }

    public function excluirArquivoLaudo()
    {

        $atendimentoModel = new atendimentoModel(); 

        if(!empty($this->input['id'])){
            $excluiu = $atendimentoModel->excluirArquivoLaudo($this->input['id']);
            return $excluiu;
        }else{  
            return false;
        }
    }

    public function detalhes($id = false)
    {
        $atendimentoModel = new atendimentoModel(); 
        $auxiliaresModel  = new Auxiliares();

        $usuarios = $auxiliaresModel->usuarios('result');

        if(!empty($id)){
            $registro = $atendimentoModel->getAssistenciaRegistro('row', array('v.id = ' => $id ));  
            $arquivos = $atendimentoModel->getAssistenciaArquivos($id);  
            $arquivos_laudo = $atendimentoModel->getAssistenciaArquivoLaudo($id);  

            if(!empty($registro)){
                if(!empty($registro['item_interno_id'])){
                    $registro['item_id'] = 'I-'.$registro['item_interno_id'];
                }else{
                    $registro['item_id'] = 'E-'.$registro['item_id'];
                }
                $key = false;
                $key = Core::multidimensionalSearchArray($usuarios,array('id' => $registro['responsavel_id']));

                if($key !== false){
                    $registro['responsavel'] = $usuarios[$key]['nome'];
                }

                $registro['atendimentos'] = $atendimentoModel->getAssistenciaAtendimentos($id);  
                if(!empty($registro['atendimentos'])){
                    foreach ($registro['atendimentos'] as &$atendimento) {

                        $key1 = false;
                        $key1 = Core::multidimensionalSearchArray($usuarios,array('id' => $atendimento['responsavel_id']));

                        if($key1 !== false){
                            $atendimento['responsavel'] = $usuarios[$key1]['nome'];
                        }

                    }
                }

            }
            if(!empty($arquivos)){
                foreach ($arquivos as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['registro_id'].'!'.$arq['arquivo']);
                }
            }

            if(!empty($arquivos_laudo)){
                foreach ($arquivos_laudo as &$arq_ld) {
                    $arq_ld['link'] = base64_encode(microtime().'!'.$arq_ld['id'].'!'.$arq_ld['registro_id'].'!'.$arq_ld['arquivo']);
                }
            }
        }

        $auxiliaresModel  = new Auxiliares();

        $usuarios = $auxiliaresModel->usuarios('result');
        $status = $atendimentoModel->getAssistenciaStatus();   
        $status_novo_chamado = Core::parametro('assistencia_status_novo_chamado');

        $this->data['status_novo_chamado']   = $status_novo_chamado;
        $this->data['status']   = $status;
        $this->data['usuarios'] = $usuarios;
        $this->data['arquivos'] = !empty($arquivos) ? $arquivos : false;
        $this->data['arquivos_laudo'] = !empty($arquivos_laudo) ? $arquivos_laudo : false;
        $this->data['registro'] = !empty($registro) ? $registro : false;

        return $this->data;
    }

    public function editDetalhes($files = false)
    {
        $voltar = $this->input['voltar'];
        unset($this->input['voltar']);
        $this->data['voltar'] = $voltar;
        $retorno = false;
        $registro = array();
        $atendimentoModel = new atendimentoModel(); 

        if(!empty($this->input)){

            $registro['id'] = $this->input['id'];
            $registro['protocolo'] = $this->input['protocolo'];
            $registro['forn_defeito_id'] = !empty($this->input['forn_defeito_id']) ? $this->input['forn_defeito_id'] : 'NULL';
            $registro['defeito_tecnico_id'] = !empty($this->input['defeito_tecnico_id']) ? $this->input['defeito_tecnico_id'] : 'NULL';
            $registro['obs_interna_tecnica'] = !empty($this->input['obs_interna_tecnica']) ? "'".$this->input['obs_interna_tecnica']."'" : 'NULL';
            $registro['clas_defeito'] = !empty($this->input['clas_defeito']) ? "'".$this->input['clas_defeito']."'" : 'NULL';
            $registro['responsabilidade'] = !empty($this->input['responsabilidade']) ? "'".$this->input['responsabilidade']."'" : 'NULL';
            $registro['responsavel_id'] = $this->input['responsavel_id'];
            $registro['status_id'] = $this->input['status_id'];

            $registro['chamado_erp_id'] = !empty($this->input['chamado_erp_id']) ? "'".$this->input['chamado_erp_id']."'" : 'NULL';

            $registro['saida_produto'] = !empty($this->input['saida_produto']) ? "'".$this->input['saida_produto']."'" : 'NULL';

            $atendimento_id = $atendimentoModel->atualizarRegistroAtendimentoDetalhes($registro);


            if(!empty($files)){


                if(!empty($files['files'])){
                    $file_ary = array();
                    $file_count = count($files['files']['name']);
                    $file_keys = array_keys($files['files']);

                    for ($i=0; $i<$file_count; $i++) {
                        foreach ($file_keys as $key) {
                            $file_ary[$i][$key] = $files['files'][$key][$i];
                        }
                    }          

                    foreach ($file_ary as $file) {
                        $k = 0; 
                        if ( $file['size'] > 0 && $file['error'] === 0 ) {
                            $ins_file['registro_id']  = $atendimento_id;
                            $ins_file['tipo']    = "'".$file['type']."'";
                            $ins_file['arquivo'] = "'".$k.$atendimento_id."-".$file['name']."'";
                            move_uploaded_file( $file['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica'.DS.$k.$atendimento_id."-".$file['name']);     
                            $atendimentoModel->insereArquivo($ins_file);
                            $k++;
                        }    

                    }     
                }

                if(!empty($files['files_laudo'])){
                  
                    $k = 0; 
                    if ( $files['files_laudo']['size'] > 0 && $files['files_laudo']['error'] === 0 ) {
                        $ins_file_laudo['registro_id']  = $atendimento_id;
                        $ins_file_laudo['tipo']    = "'".$files['files_laudo']['type']."'";
                        $ins_file_laudo['arquivo'] = "'".$k.$atendimento_id."-".$files['files_laudo']['name']."'";
                        move_uploaded_file( $files['files_laudo']['tmp_name'], APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica'.DS.'laudos'.DS.$k.$atendimento_id."-".$files['files_laudo']['name']);     
                        $atendimentoModel->insereArquivoLaudo($ins_file_laudo);
                        $k++;
                    }    


                }

            }   

        }    
       
        return $atendimento_id;
    }


    public function imprimirLaudo($id)
    {
        $atendimentoModel = new atendimentoModel(); 

        if(!empty($id)){
            $registro = $atendimentoModel->getAssistenciaRegistro('row', array('v.id = ' => $id ));  
            $arquivo = $atendimentoModel->getAssistenciaArquivoLaudo($id);  

            if(!empty($registro)){
                if(!empty($registro['item_interno_id'])){
                    $registro['item_id'] = 'I-'.$registro['item_interno_id'];
                }else{
                    $registro['item_id'] = 'E-'.$registro['item_id'];
                }
            }

            $registro['atendimentos'] = $atendimentoModel->getAssistenciaAtendimentos($id);  
            if(!empty($registro['atendimentos'])){
                foreach ($registro['atendimentos'] as $k => &$atendimento) {
                    if($atendimento['considerar_laudo'] == 'N'){
                        unset($registro['atendimentos'][$k]);
                    }
                }
            }
        
        }


        $this->data['arquivo_laudo'] = !empty($arquivo['0']) ? $arquivo['0'] : false;
        $this->data['registro'] = !empty($registro) ? $registro : false;

        return $this->data;
    }


    public function excluirAtendimentoTecnico() {
        $atendimentoModel = new atendimentoModel(); 
        if(!empty($this->input['atendimento_id'])){
            $excluiu = $atendimentoModel->excluirAtendimentoTecnico($this->input['atendimento_id']);
            return $excluiu;
        }else{
            return false;
        }

    }

}
