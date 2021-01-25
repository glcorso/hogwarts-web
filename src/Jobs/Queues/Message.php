<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

/**
 * Define as mensagens que serão enviadas para a fila de processamento
 *
 * @package Core
 * @subpackage Jobs
 * @category Message
 * @author Ramon Barros
 */

namespace Lidere\Jobs\Queues;

class Message
{
    /**
     * @var private
     */
    private $key = '';
    private $data = array();
    private $status = 0;

    /**
     * Constructor: Pass over the data we need
     */
    public function __construct($key, $data, $status)
    {
        $this->key = $key;
        $this->data = $data;
        $this->status = $status;
    }
    /**
     * getKey: Returns the key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * getData: Returns the data
     */
    public function getData()
    {
        return $this->data;
    }

    public function setStatus($status = 0)
    {
        $this->status = $status;
    }

    /**
     * getStatus: Returns the status
     */
    public function getStatus()
    {
        return $this->status;
    }
}
