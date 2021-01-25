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

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        $config = Lidere\Config::read($key);

        return !empty($config) ? $config : $default;
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if (empty($value)) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (Illuminate\Support\Str::startsWith($value, '"') && Illuminate\Support\Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        $app = Slim\Slim::getInstance();
        return $app['config']['path.storage'].($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (!function_exists('app_config')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function app_config($config = '')
    {
        $app = Slim\Slim::getInstance();
        return $config ? $app->config($config) : null;
    }
}

/**
 * Altera as variaveis do template do email para o valor informado
 *
 * @access  public
 * @return  bool
 */
if (!function_exists('mail_replace')) {
    function mail_replace($body_email, $array)
    {
        foreach ($array as $key => $value) {
            $body_email = str_replace('{'.$key.'}', $value, $body_email);
        }
        return $body_email;
    }
}

/**
 * Recupera a url base da aplicação
 */
if (!function_exists('base')) {
    function base($withUri = true, $appName = 'default')
    {
        $req = Slim\Slim::getInstance($appName)->request();
        $uri = $req->getUrl();

        if ($withUri) {
            $uri .= $req->getRootUri();
        }
        return $uri;
    }
}

/**
 * Recupera a url informada com a url base
 */
if (!function_exists('site')) {
    function site($url, $withUri = true, $appName = 'default')
    {
        return base($withUri, $appName) . '/' . ltrim($url, '/');
    }
}

/**
 * Recupera a url informada com a url do arquivos publicos do assets
 */
if (!function_exists('asset')) {
    function asset($url, $withUri = true, $appName = 'default')
    {
        return base($withUri, $appName) . '/' . ltrim($url, '/');
    }
}

/**
 * Recupera a rota para o nome
 */
if (!function_exists('route')) {
    function route($name, $data = [], $queryParams = [], $appName = 'default')
    {
        return Slim\Slim::getInstance($appName)->router->urlFor($name, $data, $queryParams);
    }
}

/**
 * Recupera a url corrente
 */
if (!function_exists('current')) {
    function current($withQueryString = true, $appName = 'default')
    {
        $app = Slim\Slim::getInstance($appName);
        $req = $app->request();
        $uri = $req->getUrl() . $req->getPath();

        if ($withQueryString) {
            $env = $app->environment();

            if ($env['QUERY_STRING']) {
                $uri .= '?' . $env['QUERY_STRING'];
            }
        }

        return $uri;
    }
}

/**
 * Converte bytes
 */
if (!function_exists('convert')) {
    function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024, ($i=floor(log($size, 1024)))), 2).' '.$unit[$i];
    }
}
