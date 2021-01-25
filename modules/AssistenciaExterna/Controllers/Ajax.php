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
namespace Lidere\Modules\AssistenciaExterna\Controllers;

use Lidere\Core;
use Lidere\Controllers\Controller;
use Lidere\Modules\Comercial\Models\Concorrente as modelConcorrente;
use Lidere\Modules\AssistenciaExterna\Models\ClienteAssistencia;
use Lidere\Modules\AssistenciaExterna\Models\Item;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoPrecoItemList;
use Lidere\Modules\AssistenciaExterna\Models\OrdemServicoArquivos as modelOrdemServicoArquivos;
use Lidere\Modules\AssistenciaExterna\Models\VOrdemServicoCriadoPor;
use Lidere\Models\Usuario;


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

    public function retornaClientesAssistenciaSelect2()
    {
        $return = new \stdClass();

        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['nome']) ?  str_replace(' ', '%', $get["nome"]) : false;

        if(!empty($string)){
            $content = ClienteAssistencia::whereRaw(
                "UPPER(nome) LIKE '%".$string."%'"
            )->get();

            if(!empty($content)){
                $content = $content->toArray();
            }


            $return->items = $content;
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaItens()
    {
        $return = new \stdClass();

        $return->items = false;

        $get = $this->app->request->get();

        $filtros = !empty($get['codigoOuDescricao']) ? " cod_item LIKE ('".str_replace(' ', '%', strtoupper($get["codigoOuDescricao"]))."%') OR desc_tecnica LIKE ('%".str_replace(' ', '%', strtoupper($get["codigoOuDescricao"]))."%')" : ' 1 = 1 ';

        //var_dump($filtros);die;
        
        $return->items = Item::whereRaw($filtros)
                            ->get();

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }



    public function retornaItemListaPreco()
    {
        $return = new \stdClass();

        $return->retorno = false;

        $get = $this->app->request->get();

        $retorno = false;

        if(!empty($get['item_id'])){

            $item = explode('-',$get['item_id']);

            $codigo_lista = Core::parametro('assistencia_externa_lista_valores_servicos');

            if(!empty($codigo_lista)){
                $ret = OrdemServicoPrecoItemList::where('cod_lista',$codigo_lista)->
                where('item_id',$item['1'])->first();
                $retorno = !empty($ret) ? true : false;
            }
        }

        $return->retorno = $retorno;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }


    public function anexaImagemAssinada()
    {
        $return = new \stdClass();

        $return->retorno = false;

        $post = $this->app->request->post();

        $retorno = false;
        $ins_file = array();


        if(!empty($post['ordem_id']) && !empty($post['imagem'])){

            $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $post['imagem']));
            $path = APP_ROOT.'public'.DS.'arquivos'.DS.'assistencia_tecnica_ex'.DS;
            $name = 'assinatura_relatorio_'.$post['ordem_id'].'_'.time().'.jpg';
          //  var_dump($name);die;
            file_put_contents($path.$name, $data);
            // insere no banco
            $ins_file['ordem_id']      = $post['ordem_id'];
            $ins_file['tipo']          = 'image/jpeg';
            $ins_file['tipo_anexo']    = ($post['tipo_anexo'] == 'AT') ? 'ATA' : 'CLA';
            $ins_file['arquivo']       = $name;
            modelOrdemServicoArquivos::criar($ins_file); 
            $return->retorno = true;
        }else{
            $return->retorno = false;
        }

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }

    public function retornaAssistenciasExternasConsultaSelect2(){
        $return = new \stdClass();


        $return->items = false;

        $get = $this->app->request->get();

        $string = !empty($get['codigoOuDescricao']) ?  str_replace(' ', '%', $get["codigoOuDescricao"]) : false;
        $usuarios = Usuario::retornaUsuarioAssitencias($string,false);
        $existe_ordens_usuarios = VOrdemServicoCriadoPor::get();

        $usuarios_retorno = array();
        if(!empty($usuarios) && !empty($existe_ordens_usuarios)){
            foreach ($usuarios as $k => &$usu) {
                $key1 = Core::multidimensionalSearchArray($existe_ordens_usuarios,array('criado_por' => $usu['id']));
                if($key1 !== false){
                    $usuarios_retorno[] = $usuarios[$k];
                }
            
            }
        }

        $return->items = $usuarios_retorno;

        $response = $this->app->response();
        $response['Content-Type'] = 'application/json';
        $response->body(json_encode($return));
    }
}
