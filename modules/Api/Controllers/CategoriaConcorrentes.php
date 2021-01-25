<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2019  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\Api\Controllers;

use Lidere\Core;
use Lidere\Modules\Api\Controllers\Core\Api;
use Lidere\Modules\Comercial\Services\CategoriaConcorrentes as CategoriaConcorrentesService;

/**
 * CategoriaConcorrentes
 *
 * @package Lidere\Modules
 * @subpackage Api\Controllers\Core
 * @author Sergio Sirtoli
 * @copyright 2019 Lidere Sistemas
 */
class CategoriaConcorrentes extends Api
{
    public $url = false;

    public function index()
    {
        $service = new CategoriaConcorrentesService(
            $this->usuario,
            $this->empresa,
            $this->modulo,
            $this->data,
            false
        );

        $data = $service->list();

        if (!empty($data['resultado'])) {
            $this->setData([
                'categoriaConcorrentes' => $data['resultado']
            ]);
            $this->response();
        } else {
            $this->setError('CATEGORIACONCORRENTES', 'Nenhuma Categoria Encontrada.')
                 ->response(403);
        }

    }
}
