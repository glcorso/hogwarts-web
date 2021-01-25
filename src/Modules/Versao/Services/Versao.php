<?php

namespace Lidere\Modules\Versao\Services;

use Lidere\Modules\Services\Services;
use Lidere\ChangeLog;

/**
 * Versao
 *
 * @package Lidere\Modules
 * @subpackage Versao\Services
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Versao extends Services
{
    /**
     * Retorna os dados da listagem de parametros
     * @return array
     */
    public function list($pagina = 1)
    {
        $change = new ChangeLog();

        $rss = $change->rss();
        $pattern = "#^([A-z0-9]{40})\s([A-z0-9]{40})([^<]+)\s<([^>]+)>\s([\d]{10})\s([-\d]{5})\s([^:]+):(.*)$#";
        $commits = [];
        foreach ($rss->logs as $commit) {
            if (preg_match_all($pattern, $commit, $matches)) {
                $commits[] = [
                    'hashPrevious' => current($matches[1]),
                    'hashCurrent' => current($matches[2]),
                    'author' => trim(current($matches[3])),
                    'email' => current($matches[4]),
                    'pubDate' => current($matches[5]),
                    'zone' => current($matches[6]),
                    'action' => current($matches[7]),
                    'description' => current($matches[8])
                ];
            }
        }

        $commits = array_reverse($commits);
        $this->data['commits'] = $commits;
        return $this->data;
    }
}
