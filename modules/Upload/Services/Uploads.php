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
namespace Lidere\Modules\Upload\Services;

use Lidere\Config;
use Lidere\Modules\Services\Services;
use Lidere\Modules\Upload\Models\Upload;

/**
 * Uploads
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Upload\Services\Uploads
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Uploads extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list()
    {
        $per_page = (int)Config::read('APP_PERPAGE');

        $this->data['uploads'] = Upload::paginate($per_page);

        return $this->data;
    }

    public function form($id = null)
    {
        $this->data['upload'] = Upload::find($id);

        return $this->data;
    }

    public function add($upload)
    {
        $upload = Upload::create($upload);
        return !empty($upload);
    }

    public function edit()
    {
        $upload = Upload::find($this->input['id'])
                        ->update($this->input['id']);
        return !empty($upload);
    }
}
