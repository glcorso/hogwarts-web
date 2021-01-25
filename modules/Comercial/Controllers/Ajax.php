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
namespace Lidere\Modules\Comercial\Controllers;

use Lidere\Config;
use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Models\Auxiliares;
use Lidere\Models\Aplicacao;
use Lidere\Modules\Comercial\Models\Concorrente as modelConcorrente;
use Lidere\Modules\Comercial\Models\TelefoneEstabelecimento;
use Lidere\Modules\Comercial\Models\Estabelecimento;
use Lidere\Modules\Comercial\Models\Prospect;
use Lidere\Modules\Comercial\Models\Participante;
use Lidere\Modules\Comercial\Models\ClienteEstabelecimentoProspect;
use Lidere\Modules\Comercial\Models\ClienteErp;
use Lidere\Modules\Comercial\Models\RelatorioVisitaStatus as modelRelatorioVisitaStatus;

/**
 * Ajax
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Comercial\Controllers\Ajax
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Ajax extends Controller
{

	public $url = false;

    public function retornaConcorrentesPorCategoriaSelect2()
    {
        $return = new \stdClass();
        $modelConcorrente = new modelConcorrente();
        $get = $this->app->request()->get();
        $return->error = false;

        $filtros = array();

       	if($get['categoriaItem'] == 'clima'){
       		$filtros['categoria_id'] = Core::parametro('comercial_id_cat_clima');
       	}
       	elseif($get['categoriaItem'] == 'rodoar'){
       		$filtros['categoria_id'] = Core::parametro('comercial_id_cat_rodoar');
       	} 
       	else{
       		$filtros['categoria_id'] = Core::parametro('comercial_id_cat_geladeira');
       	}

        $return->items = modelConcorrente::with('Categoria')->where($filtros)->get();

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaTelefoneEstabelecimento()
    {
        $return = new \stdClass();
        $modelConcorrente = new modelConcorrente();
        $get = $this->app->request()->get();
        $return->error = false;
        $telefone_formatado = false;
        $filtros = array();

        $dados = TelefoneEstabelecimento::where('est_id', $get['est_id'])
                               ->where('ranking','1')->first();

        if(!empty($dados)){
            $telefone_formatado = '('.$dados->ddd.') '.$dados->telefone;
        }
        
        $return->telefone_formatado = $telefone_formatado;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaEstabelecimentosProspectsConsultaSelect2()
    {
        $return = new \stdClass();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        
        if(!empty($string)){
            $content = ClienteEstabelecimentoProspect::whereRaw("(UPPER(codigo) LIKE '%".$string."%' OR UPPER(descricao) LIKE '%".$string."%') ")->get();
            
            if(!empty($content)){
                $content = $content->toArray();
            }


            $return->items = $content;
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaParticipantesSelect2() {


        $return = new \stdClass();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        
        if(!empty($string)){
            $content = Participante::with('Cliente')->whereRaw("( UPPER(nome) LIKE '%".strtoupper($string)."%') ")->get();
            
            if(!empty($content)){
                $content = $content->toArray();
            }


            $return->items = $content;
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));

    }


    public function retornaClientesErpSelect2()
    {
        $return = new \stdClass();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        
        if(!empty($string)){
            $content = ClienteErp::whereRaw("(UPPER(cod_cli) LIKE '%".strtoupper($string)."%' OR UPPER(descricao) LIKE '%".strtoupper($string)."%') ")->get();
            
            if(!empty($content)){
                $content = $content->toArray();
            }


            $return->items = $content;
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }


    public function naoFoiPossivelContato()
    {
        $return = new \stdClass();
        $post = $this->app->request()->post();

        if(!empty($post['id'])){
            $st['status_id'] = 5; //Sem Contato
            $st['relatorio_id'] = $post['id'];
            $status = modelRelatorioVisitaStatus::criar($st);
        }

        $return->error = !empty($status) ? false : true;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }
}