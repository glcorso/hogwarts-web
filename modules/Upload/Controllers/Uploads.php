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
namespace Lidere\Modules\Upload\Controllers;

use Lidere\Assets;
use Lidere\Controllers\Controller;
use Lidere\Upload;

/**
 * Uploads
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Upload\Controllers\Uploads
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Uploads extends Controller {

    public $url = 'upload';

    public function pagina($pagina = 1)
    {
        $this->app->render(
            'index.twig',
            array(
                'data' => $this->app->service->list($pagina)
            )
        );
    }

    public function form($id = null)
    {
        $this->app->render(
            'form.twig',
            [
                'data' => $this->app->service->form($id)
            ]
        );
    }

    public function add()
    {
        $upload = $this->upload();
        if (!empty($upload['full_path']) && file_exists($upload['full_path'])) {
            if ($this->app->service->add($upload)) {
                $this->app->flash('success', 'Arquivo enviado com sucesso!');
            }
        } else if (!empty($upload)) {
            $this->app->flash('error', $upload);
        }
        $this->redirect();
    }

    /**
    * Retorna os dados do arquivo enviado ou uma mensagem de erro
    * @var mixed
    * <code>
    * {
    *  "file_name": "065c4cc92ab180e95a30a59ce048f7db.png",
    *  "file_type": "image/png",
    *  "file_path": "/var/www/html/<project>/userfiles/banners/",
    *  "full_path": "/var/www/html/<project>/userfiles/banners/065c4cc92ab180e95a30a59ce048f7db.png",
    *  "raw_name": "065c4cc92ab180e95a30a59ce048f7db",
    *  "orig_name": "<filename>.png",
    *  "client_name": "<filename>.png",
    *  "file_ext": ".png",
    *  "file_size": 7.29,
    *  "is_image": true,
    *  "image_width": 965,
    *  "image_height": 511,
    *  "image_type": "png",
    *  "image_size_str": "width=\"965\" height=\"511\""
    * }
    * </code>
    */
    private function upload()
    {
        $upload = new Upload([
            'upload_path' => APP_ROOT.'public'.DS.'arquivos'.DS.'tmp',
            'allowed_types' => 'png|jpeg|jpg|gif',
            'max_size' => '1024'
        ]);
        $upload->encrypt_name = true;
        $return = null;
        if (!empty($_FILES['file']['tmp_name'])) {
            if (!$upload->do_upload('file')) {
                $return = $upload->display_errors();
            } else {
                $return = $upload->data();
            }
        }
        return $return;
    }
}
