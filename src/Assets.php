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
 * Carregamento dos assets do sistema
 *
 * @package  Assets
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */

namespace Lidere;

class Assets
{
    private static $css = [];
    private static $js = [];

    public static function add($file = null, $module = null, $dependence = array())
    {
        $pathinfo = pathinfo($file);
        $filename = str_replace('.min', '', $pathinfo['filename']);
        if ($pathinfo['extension'] == 'js') {
            self::$js[$filename]['dirname'] = $pathinfo['dirname'];
            self::$js[$filename]['basename'] = $pathinfo['basename'];
            self::$js[$filename]['extension'] = $pathinfo['extension'];
            self::$js[$filename]['module'] = $module;
            self::$js[$filename]['dependence'] = $dependence;
        } elseif ($pathinfo['extension'] == 'css') {
            self::$css[$filename]['dirname'] = $pathinfo['dirname'];
            self::$css[$filename]['basename'] = $pathinfo['basename'];
            self::$css[$filename]['extension'] = $pathinfo['extension'];
            self::$css[$filename]['module'] = $module;
            self::$css[$filename]['dependence'] = $dependence;
        }
    }

    public static function getJs()
    {
        return self::$js;
    }

    public static function getCss()
    {
        return self::$css;
    }

    public static function routerCss($file = null)
    {
        $app = \Slim\Slim::getInstance();
        $module = $app->request()->get('module');
        $app->response()->header('Content-Type', 'text/css');
        if (is_file(APP_ROOT.'modules'.DS.$module.DS.'assets'.DS.'css'.DS.$file)) {
            $app->response()->status('200');
            $css = file_get_contents(APP_ROOT.'modules'.DS.$module.DS.'assets'.DS.'css'.DS.$file);
            $app->response()->body($css);
        } elseif (is_file(APP_ROOT.'src'.DS.'Modules'.DS.$module.DS.'assets'.DS.'css'.DS.$file)) {
            $app->response()->status('200');
            $css = file_get_contents(APP_ROOT.'src'.DS.'Modules'.DS.$module.DS.'assets'.DS.'css'.DS.$file);
            $app->response()->body($css);
        } else {
            $app->response()->status('404');
        }
    }

    public static function routerJs($file = null)
    {
        $app = \Slim\Slim::getInstance();
        $module = $app->request()->get('module');
        $app->response()->header('Content-Type', 'text/javascript');
        if (is_file(APP_ROOT.'modules'.DS.$module.DS.'assets'.DS.'js'.DS.$file)) {
            $app->response()->status('200');
            $js = file_get_contents(APP_ROOT.'modules'.DS.$module.DS.'assets'.DS.'js'.DS.$file);
            $app->response()->body($js);
        } elseif (is_file(APP_ROOT.'src'.DS.'Modules'.DS.$module.DS.'assets'.DS.'js'.DS.$file)) {
            $app->response()->status('200');
            $js = file_get_contents(APP_ROOT.'src'.DS.'Modules'.DS.$module.DS.'assets'.DS.'js'.DS.$file);
            $app->response()->body($js);
        } else {
            $app->response()->status('404');
        }
    }

    public static function loadJs()
    {
        $change = new \Lidere\ChangeLog();
        $files = self::getJs();
        $scripts = [];
        if (!empty($files)) {
            //self::dependencies($files);
            foreach ($files as $filename => $file) {
                $src = '';
                if ($file['module']) {
                    $src = $file['dirname'].'/'.$file['basename'].'?module='.$file['module'].'&v='.$change->lastCommit();
                } else {
                    $src = $file['dirname'].'/'.$file['basename'].'?v='.$change->lastCommit();
                }
                $scripts[] = '<script type="text/javascript" src="'.$src.'"></script>';
            }
        }
        return implode('', $scripts);
    }

    public static function loadCss()
    {
        $change = new \Lidere\ChangeLog();
        $files = self::getCss();
        $styles = [];
        if (!empty($files)) {
            //self::dependencies($files);
            foreach ($files as $filename => $file) {
                $href = '';
                if ($file['module']) {
                    $href = $file['dirname'].'/'.$file['basename'].'?module='.$file['module'].'&v='.$change->lastCommit();
                } else {
                    $href = $file['dirname'].'/'.$file['basename'].'?v='.$change->lastCommit();
                }
                $styles[] = '<link rel="stylesheet" type="text/css" href="'.$href.'" />';
            }
        }
        return implode('', $styles);
    }

    public static function dependencies($files = array())
    {
    }
}
