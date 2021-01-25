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

if (!function_exists("array_column")) {
    function array_column($array, $column_name)
    {
        return array_map(
            function ($element) use ($column_name) {
                return $element[$column_name];
            },
            $array
        );
    }
}
