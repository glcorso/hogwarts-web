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
namespace Lidere\Modules\Assistencia\Controllers;

use Lidere\Controllers\Controller;
use Lidere\Models\Aplicacao;
use Lidere\Assets;
use Lidere\Core;

/**
 * Consulta
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Assistencia\Controllers\Consulta
 * @author     Sergio Sirtoli <sergio@lideresistemas.com.br>
 * @copyright  2019 Lidere Sistemas
 * @license    Copyright (c) 2019
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Consulta extends Controller {

    public $url = 'assistencia-tecnica/consulta';

    public function pagina($pagina = 1)
    {
    
        Assets::add('/assets/js/consulta.js', 'Assistencia');
        $this->app->render(
            'index.html.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }

    public function excel()
    {   
       
        $html = $this->app->view->render(
            'excel.html.twig',
            array(
                'data' => $this->app->service->listDados()
            )
        );

        $arquivo = 'consulta_protocolos_'.date('dmYHis').'_.xls';

        // Configurações header para forçar o download
        header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header ("Cache-Control: no-cache, must-revalidate");
        header ("Pragma: no-cache");
        header ("Content-type: application/x-msexcel;charset=utf-8");
        header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
        header ("Content-Description: PHP Generated Data" );
        echo "\xEF\xBB\xBF";
        // Envia o conteúdo do arquivo
        echo $html;
        exit;
    }
}