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
 * Helpers - funções utilizadas em toda a aplicação
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */

if (!function_exists('d')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function d()
    {
        array_map(function ($x) {
            var_dump($x);
        }, func_get_args());
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd()
    {
        array_map(function ($x) {
            var_dump($x);
        }, func_get_args());
        die;
    }
}

if (!function_exists('dlog')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dlog($level = 'log', $message = null)
    {
        $app = Slim\Slim::getInstance();
        if (!empty($level)) {
            $app->log->{$level}(strtoupper($level).'-'.date('Y-m-d H:i:s').' '.$message);
        } else {
            $app->log->error('Verifique os campos passados para o helper dlog(level, message)');
            $app->log->error('Você deve informar o level (debug, info, notice, warning, warn, error, critical, fatal, alert, emergency, log)');
        }
    }
}

if (!function_exists('db')) {
    /**
     * Dump debug backtracer
     * @param  boolean $nivel Return nivel debug backtracer
     * @return array
     */
    function db($nivel = false)
    {
        $bt = debug_backtrace();
        $caller = !empty($nivel) ? $bt[$nivel] : array_shift($bt);
        $object = !empty($caller['object']) ? $caller['object'] : null;
        $namespace = null;
        $class = null;
        $function = $caller['function'];
        $args = !empty($caller['args']) ? $caller['args'] : null;
        $file = !empty($caller['file']) ? str_replace(APP_ROOT, '', $caller['file']) : null;
        $line = !empty($caller['line']) ? $caller['line'] : null;
        if (!empty($object)) {
            $calledClass = $object->calledClass;
            $reflectionClass = new \ReflectionClass($calledClass);
            $namespace = $reflectionClass->getNamespaceName();
            $class = $reflectionClass->getShortName();
        }
        return [
            'namespace' => $namespace,
            'class' => $class,
            'function' => $caller['function'],
            'args' => $args,
            //'file' => $file,
            'line' => $line,
            'short' => "{$namespace}\\{$class}::{$function}()[{$line}]"
        ];
    }
}
