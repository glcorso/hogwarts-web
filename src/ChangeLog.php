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
 * Carrega o feed do repositório para controle do cache
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */
namespace Lidere;

use Feed;
use DOMDocument;

class ChangeLog
{
    public static $feed = false;
    public static $log;
    public static $rss;

    public function __construct()
    {
        try {
            self::$rss = new \stdClass();
            self::$rss->item = new \stdClass();
            self::$rss->branch = 'master';
            self::$rss->log = @file_get_contents(APP_ROOT.'.git'.DS.'logs'.DS.'refs'.DS.'heads'.DS.self::$rss->branch);
            self::$rss->hash = trim(@file_get_contents(APP_ROOT.'.git'.DS.'refs'.DS.'heads'.DS.self::$rss->branch));
            self::$rss->tags = glob(APP_ROOT.'.git'.DS.'refs'.DS.'tags'.DS.'*');
            if (!empty(self::$rss->log)) {
                self::$rss->logs = explode("\n", self::$rss->log);
                $preg = "/(".self::$rss->hash.")/";
                self::$rss->commit = explode("\t", current(preg_grep($preg, self::$rss->logs)));
            }
            self::$rss->item->title = Config::read('APP_NAME');
            self::$rss->item->pubDate = date('Y-m-d');
            self::$rss->item->link = substr(self::$rss->hash, 0, 8);
            if (class_exists('Feed') && self::$feed) {
                Feed::$cacheDir = APP_LOGS;
                Feed::$cacheExpire = '5 hours';
                if (!self::hasCache() && php_sapi_name() != 'cli') {
                    if (self::isXMLFileValid(Config::read('APP_FEED'))) {
                        self::$rss = Feed::loadRss(Config::read('APP_FEED'));
                    }
                }
            }
            self::$log = self::$rss->item;
        } catch (Exception $e) {
            dlog('error', $e->getMessage());
        }
    }

    public static function rss()
    {
        return self::$rss;
    }

    public static function title()
    {
        return trim(self::$log->title);
    }

    public static function date()
    {
        return trim(self::$log->pubDate);
    }

    public static function lastCommit()
    {
        return str_replace(Config::read('APP_REPOSITORY').'/commits/', '', trim(self::$log->link));
    }

    public static function tags()
    {
        return array_map(function ($t) {
            return basename($t);
        }, self::$rss->tags);
    }

    public static function hasCache()
    {
        dlog('info', 'ChangeLog::hasCache()');
        $e = Feed::$cacheExpire;
        $cacheFile = Feed::$cacheDir . '/feed.' . md5(serialize(array(Config::read('APP_FEED'), null, null))) . '.xml';
        return file_exists($cacheFile)
               && (time() - @filemtime($cacheFile) <= (is_string($e) ? strtotime($e) - time() : $e))
               && $data = @file_get_contents($cacheFile);
    }

    /**
     * @param string $xmlFilename Path to the XML file
     * @param string $version 1.0
     * @param string $encoding utf-8
     * @return bool
     */
    public function isXMLFileValid($xmlFilename, $version = '1.0', $encoding = 'utf-8')
    {
        $xmlContent = file_get_contents($xmlFilename);
        return $this->isXMLContentValid($xmlContent, $version, $encoding);
    }

    /**
     * @param string $xmlContent A well-formed XML string
     * @param string $version 1.0
     * @param string $encoding utf-8
     * @return bool
     */
    public function isXMLContentValid($xmlContent, $version = '1.0', $encoding = 'utf-8')
    {
        if (trim($xmlContent) == '') {
            return false;
        }

        libxml_use_internal_errors(true);

        $doc = new DOMDocument($version, $encoding);
        $doc->loadXML($xmlContent);

        $errors = libxml_get_errors();
        libxml_clear_errors();

        $msg = empty($errors) ? 'Rss carregado com sucesso!' : 'Não foi possível abrir o rss do repositório, será ignorado!';

        dlog('info', $msg);

        return empty($errors);
    }
}
