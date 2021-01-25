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
use Lidere\Modules\Assistencia\Models\RastreabilidadeGarantia as modelRastreabilidadeGarantia;
use Lidere\Modules\Assistencia\Models\VRastreabilidadeGarantia as modelVRastreabilidadeGarantia;

/**
 * RastreamentoGarantia
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Services\RastreamentoGarantia
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class RastreamentoGarantia extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $filtros = array();

      

        if (!empty($this->input['razao_social'])) {
            $filtros['razao_social'] = " LIKE '%".strtoupper($this->input['razao_social'])."%'";
        }

        if (!empty($this->input['transportadora'])) {
            $filtros['transportadora'] = " LIKE '%".strtoupper($this->input['transportadora'])."%'";
        }

        if (!empty($this->input['num_nf'])) {
            $filtros['num_nf'] = " = '".$this->input['num_nf']."'";
        }

        if (!empty($this->input['status'])) {
            $filtros['status'] = " = '".$this->input['status']."'";
        }

        if (!empty($this->input['atrasado'])) {
            if($this->input['atrasado'] == 'S'){
                $filtros['cor'] = " IN ('V','A')";
            }else{
                $filtros['cor'] = " = 'N'"; 
            }

        }
       
        if (!empty($this->input['dt_solicitado']) && $this->input['dt_solicitado'] != null) {
            $this->input['dt_solicitado'] = trim($this->input['dt_solicitado']);
            if (strpos($this->input['dt_solicitado'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['dt_solicitado']);
                 $filtros['TRUNC(dt_solicitado)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                 $filtros['TRUNC(dt_solicitado)'] = " = '" . $this->input['dt_solicitado'] . "'";
            }
        }

        if (!empty($this->input['coletado_em']) && $this->input['coletado_em'] != null) {
            $this->input['coletado_em'] = trim($this->input['coletado_em']);
            if (strpos($this->input['coletado_em'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['coletado_em']);
                 $filtros['TRUNC(coletado_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                 $filtros['TRUNC(coletado_em)'] = " = '" . $this->input['coletado_em'] . "'";
            }
        }

        if (!empty($this->input['recebido_em']) && $this->input['recebido_em'] != null) {
            $this->input['recebido_em'] = trim($this->input['recebido_em']);
            if (strpos($this->input['recebido_em'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['recebido_em']);
                 $filtros['TRUNC(recebido_em)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                 $filtros['TRUNC(recebido_em)'] = " = '" . $this->input['recebido_em'] . "'";
            }
        }

        if (!empty($this->input['dt_emissao']) && $this->input['dt_emissao'] != null) {
            $this->input['dt_emissao'] = trim($this->input['dt_emissao']);
            if (strpos($this->input['dt_emissao'], '|') !== false) {
                list($inicio, $fim) = explode('|', $this->input['dt_emissao']);
                 $filtros['TRUNC(dt_emissao)'] = " BETWEEN '" . $inicio . "' AND '" . $fim . "'";
            } else {
                 $filtros['TRUNC(dt_emissao)'] = " = '" . $this->input['dt_emissao'] . "'";
            }
        }


        $filtros = function($query) use ($filtros) {
             if (!empty($filtros)) {
                foreach ($filtros as $coluna => $valor) {
                    $query->whereRaw($coluna." ".$valor);
                }
            }
        };



        try {

            if (!empty($pagina)) {
                /* Total sem paginação  */
                $total = modelVRastreabilidadeGarantia::where($filtros)->count();
                $num_paginas = ceil($total / Config::read('APP_PERPAGE'));

                /**
                 * records = qtd de registros
                 * offset = inicia no registro n
                */
                $records = ($pagina * Config::read('APP_PERPAGE')) - Config::read('APP_PERPAGE');
                $offset = Config::read('APP_PERPAGE');
            }

            $rows = modelVRastreabilidadeGarantia::where($filtros);

            if (!empty($pagina)) {
                $rows->skip($records)
                    ->take($offset);
            }

            $rows = $rows->orderBy('ordem')->get();

            $total_tela = count($rows);

            if (!empty($rows)) {
                foreach ($rows as &$row) {
                    $row['permite_excluir'] = true;
                }
            }


        } catch (\Illuminate\Database\QueryException $e) {
            $rows = false;
            $total_tela = 0;
            $total = 0;
            $num_paginas = 1;
        }

        $this->data['filtros'] = $this->input;
        $this->data['resultado'] = $rows;
        if (!empty($pagina)) {
            $this->data['paginacao'] = Core::montaPaginacao(
                true,
                $total_tela,
                $total,
                $num_paginas,
                $pagina,
                '/assistencia/rastreamento-garantias/pagina',
                $_SERVER['QUERY_STRING']
            );
        }

        return $this->data;
    }


    public function anexarNfe($files = false)
    {
        $anexou =  false;
        $data = false;
        $cnpj_resfriar = '01986608000108';
        if (!empty($files)) {

            $file_ary = array();
            $file_count = count($files['files']['name']);
            $file_keys = array_keys($files['files']);

            for ($i=0; $i<$file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $files['files'][$key][$i];
                }
            }

            foreach ($file_ary as $v => $file) {

                $xml_file = file_get_contents($file['tmp_name']);  
                $xml= simplexml_load_file($file['tmp_name']);   
                if (!$xml) {
                    continue;
                    $anexou = false;
                } 

                // TESTA PARA VER SE A RESFRIAR É A DESTINATÁRIA 01986608000108

                if($cnpj_resfriar == strval($xml->NFe->infNFe->dest->CNPJ)){

                    $data['cnpj'] = (strval($xml->NFe->infNFe->emit->CNPJ));
                    $data['razao_social'] = (strval($xml->NFe->infNFe->emit->xNome));
                    $data['nome_fantasia'] = (strval($xml->NFe->infNFe->emit->xFant));
                    $data['cidade'] = (strval($xml->NFe->infNFe->emit->enderEmit->xMun));
                    $data['uf'] = (strval($xml->NFe->infNFe->emit->enderEmit->UF));

                    $data['num_nf'] = (strval($xml->NFe->infNFe->ide->nNF));
                    $data_emissao = substr(strval($xml->NFe->infNFe->ide->dhEmi), 0, 10);
                    $data['dt_emissao'] = date("d/m/Y", strtotime($data_emissao));

                    $data['transportadora'] = (strval($xml->NFe->infNFe->transp->transporta->xNome));
                    $data['cnpj_transportadora'] = (strval($xml->NFe->infNFe->transp->transporta->CNPJ));


                    $data['chave_acesso'] = (strval($xml->protNFe->infProt->chNFe));
                    $data['xml'] = $xml_file;
                     
                   // var_dump($data);die;
                    $anexou = modelRastreabilidadeGarantia::criar($data);
                }else{
                    continue;
                    $anexou = false;
                }
                
            }
        }

       
        return $anexou;
    }

    public function operacoes()
    {
        $atualizou = false;

        if(!empty($this->input['id']) && !empty($this->input['operacao'])){

            if($this->input['operacao'] == 'S'){
                $upd['dt_solicitado'] = $this->input['data'];
            }elseif ($this->input['operacao'] == 'C') {
                $upd['coletado_em'] = $this->input['data'];
            }elseif ($this->input['operacao'] == 'R') {
                $upd['recebido_em'] = date('d/m/Y');
            }  

            $row = modelRastreabilidadeGarantia::find($this->input['id']);
            $atualizou = $row->update($upd);
        }

        return $atualizou;
    }
}
