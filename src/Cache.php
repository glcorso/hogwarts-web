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
 * Recupera configuração da aplicação
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */

namespace Lidere;

use \DateTime;

class Cache
{

    /**
     * Armazena o cache
     * @var array
     */
    static $cache;

    /**
     * Verifica se o cache existe
     * @param  string  $key
     * @return boolean
     */
    public static function has($key = null)
    {
        $file = APP_ROOT.'storage'.DS.'cache'.DS.md5($key).'.cache';
        if (file_exists($file)) {
            self::$cache[$key] = unserialize(file_get_contents($file));
            if ((new DateTime())->getTimestamp() > (int)self::$cache[$key]['timestamp']) {
                @unlink($file);
                return false;
            }
            return !empty(self::$cache[$key]);
        }
        return false;
    }

    /**
     * Recupera o cache atraves da chave
     * @param  string $key
     * @param  mixed $default valor padrão caso não exista
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        $value = false;

        if (self::has($key)) {
            $value = self::$cache[$key]['value'];

            if (empty($value)) {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Cria o cache
     * @param  string $key
     * @param  mixed $value
     * @param  integer $minutes
     * @return void
     */
    public static function put($key = null, $value = null, $minutes = null)
    {
        $date = new DateTime('now');
        $date->modify("+{$minutes} minutes");
        self::$cache[$key] = array(
            'session_id' => session_id(),
            'date' => $date,
            'timestamp' => $date->getTimestamp(),
            'key' => $key,
            'value' => $value,
            'minutes' => $minutes
        );
        file_put_contents(APP_ROOT.'storage'.DS.'cache'.DS.md5($key).'.cache', serialize(self::$cache[$key]));
    }

    /**
     * Exclui o cache
     * @param  string $key
     * @return void
     */
    public static function delete($key)
    {
        $file = APP_ROOT.'storage'.DS.'cache'.DS.md5($key).'.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
