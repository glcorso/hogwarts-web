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
namespace Lidere\Modules\Home\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Assets;
use Lidere\Modules\Auxiliares\Models\UsuarioContrato as UsuarioContratoModel;
use Lidere\Modules\Empresas\Models\EmpresaDocumentos as EmpresaDocumentosModel;
use Lidere\Modules\Avisos\Models\TAvisos as TAvisos;
use Lidere\Modules\Avisos\Models\TAvisosArquivos as TAvisosArquivos;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Home
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Home\Controllers\Home
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Home extends Controller {

    public $url = 'home';

    public function index()
    {
        $data['modulo'] = $this->modulo;

        if($this->usuario['tipo'] == 'ate'){
        	$arquivos = UsuarioContratoModel::where('usuario_id', $this->usuario['id'])->get();
            if(!empty($arquivos)){
                foreach ($arquivos as &$arq) {
                    $arq['link'] = base64_encode(microtime().'!'.$arq['id'].'!'.$arq['usuario_id'].'!'.$arq['arquivo']);
                }
            
                $data['arquivos'] = $arquivos;
            }

            $arquivos_lista = EmpresaDocumentosModel::where('empresa_id',$_SESSION['empresa']['id'])->get();
            if(!empty($arquivos_lista)){
                foreach ($arquivos_lista as &$arq2) {
                    $arq2['link'] = base64_encode(microtime().'!'.$arq2['id'].'!'.$arq2['empresa_id'].'!'.$arq2['arquivo']);
                }
            
                $data['arquivos_lista'] = $arquivos_lista;
            }

            $avisos_ate = TAvisos::where('ate','S')->where('status', 'A')->orderBy('id','desc')->get();
            if(!empty($avisos_ate)){
                foreach ($avisos_ate as &$aviso_ate) {
                    $aviso_ate['arquivos'] = TAvisosArquivos::where('aviso_id', $aviso_ate->id)->get();
                    if(!empty($aviso_ate['arquivos'])){
                        foreach ($aviso_ate['arquivos'] as &$arq3) {
                            $arq3['link'] = base64_encode(microtime().'!'.$arq3['id'].'!'.$arq3['aviso_id'].'!'.$arq3['arquivo']);
                        }
                    }
                }
            
                $data['avisos'] = $avisos_ate;
            }


        }else{  

            if(!empty($this->usuario['setor_id'])){
                $usuario_setor_id = $this->usuario['setor_id'];

                $avisos = TAvisos::where('status', 'A')
                          ->whereExists(function ($query) use ( $usuario_setor_id ) {
                                                                    $query->select(DB::raw(1))
                                                                          ->from('tsdi_avisos_setores')
                                                                          ->whereRaw('tsdi_avisos_setores.aviso_id = tsdi_avisos.id')
                                                                          ->whereRaw('tsdi_avisos_setores.setor_id = '.$usuario_setor_id);
                                                                })
                          ->orderBy('id','desc')->get();
                if(!empty($avisos)){
                    foreach ($avisos as &$aviso) {
                        $aviso['arquivos'] = TAvisosArquivos::where('aviso_id', $aviso->id)->get();
                        if(!empty($aviso['arquivos'])){
                            foreach ($aviso['arquivos'] as &$arq4) {
                                $arq4['link'] = base64_encode(microtime().'!'.$arq4['id'].'!'.$arq4['aviso_id'].'!'.$arq4['arquivo']);
                            }
                        }
                    }
                
                    $data['avisos'] = $avisos;
                }
            }

        }

        $data['usuario'] = $this->usuario;

        $this->app->render('index.html.twig', array('data' => $data));
    }

    public function download($link) {


        $link = base64_decode($link);



        try {
            list($time, $id, $usuario_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }


        $file = UsuarioContratoModel::find($id)->toArray();

        if ( $file['usuario_id'] != $usuario_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'usuario_contratos'.DS.$file['arquivo']));
        }

    }


    public function downloadLista($link) {


        $link = base64_decode($link);

        try {
            list($time, $id, $empresa_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }


        $file = EmpresaDocumentosModel::find($id)->toArray();

        if ( $file['empresa_id'] != $empresa_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'empresa_documentos'.DS.$file['arquivo']));
        }

    }

    public function downloadAviso($link) {


        $link = base64_decode($link);

        try {
            list($time, $id, $aviso_id ,$name) = explode('!', $link);
        } catch (ErrorException $e) {
            echo utf8_decode("Operação inválida - ".$e->getMessage());
            die();
        }

        $file = TAvisosArquivos::find($id)->toArray();

        if ( $file['aviso_id'] != $aviso_id ) {
            echo "Operação inválida!";
        } else {
            $response = $this->app->response();
            $response->header("Content-Type", $file['tipo']);
            $response->header("Content-Disposition", "attachment; filename=" . basename($file['arquivo']));
            $response->body(file_get_contents(APP_ROOT.'public'.DS.'arquivos'.DS.'avisos_arquivos'.DS.$file['arquivo']));
        }

    }

}
