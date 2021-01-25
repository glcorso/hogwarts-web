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
 * Lidere Sistemas
 * Registra o tempo de execução de um processo
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */
namespace Lidere;

class Timer
{
    private $func, $iterations, $start, $stop;
    public function __construct($func = false, $iterations = 500)
    {
        if (!isset($func) && !isset($iterations)) {
            throw new Exception("func and iterations not null");
        } elseif (is_callable($func)) {
            $this->func = $func;
            $this->iterations = is_int($iterations) ? $iterations : 500;

            $this->execute();
        } elseif (!$func && is_int($iterations)) {
            $this->iterations = $iterations;
        } elseif (!$func && !is_int($iterations)) {
            $this->iterations = 500;
        }
    }
    private function execute()
    {
        $this->start();
        for ($i = 0; $i <= $this->iterations; $i++) {
            call_user_func($this->func);
        }
        $this->stop();
    }
    public function start()
    {
        $this->start = microtime(true);
        return $this->start;
    }
    public function stop()
    {
        $this->stop = microtime(true);
        return $this->stop;
    }
    public function getTimes()
    {
        $time = ($this->stop - $this->start) / $this->iterations;
        return number_format($time, 10, '.', '').' msec';
    }
}
