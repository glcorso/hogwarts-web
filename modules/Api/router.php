<?php

/**
 * Página inicial com instruções sobre a API
 */
$app->get('/welcome', 'Lidere\Modules\Api\Controllers\Welcome:index');
$app->get('/ok', 'Lidere\Modules\Api\Controllers\Welcome:ok');

// API group
$app->group('/api', function () use ($app) {
    /**
     * Versões
     */
    $app->group('/v1', function () use ($app) {

        // API
        $app->options('/', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->get('/', 'Lidere\Modules\Api\Controllers\Welcome:index');


        // Login
        $app->options('/(L|l)ogin', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->post('/(L|l)ogin', 'Lidere\Modules\Api\Controllers\Login:index');

        // CategoriaConcorrentes
        $app->options('/(C|c)ategoria-concorrentes', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->get('/(C|c)ategoria-concorrentes', 'Lidere\Modules\Api\Controllers\CategoriaConcorrentes:index');

        // CategoriaConcorrentes
        $app->options('/(C|c)oncorrentes', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->get('/(C|c)oncorrentes', 'Lidere\Modules\Api\Controllers\Concorrentes:index');

        // CategoriaConcorrentes
        $app->options('/(C|c)lientes', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->get('/(C|c)lientes', 'Lidere\Modules\Api\Controllers\ClientesEstabelecimento:index');

        // RelatorioVisitas
        $app->options('/(R|r)elatorio-visitas', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->get('/(R|r)elatorio-visitas', 'Lidere\Modules\Api\Controllers\RelatorioVisitas:index');
        $app->options('/(R|r)elatorio-visitas/adicionar', 'Lidere\Modules\Api\Controllers\RelatorioVisitas:options');
        $app->post('/(R|r)elatorio-visitas/adicionar', 'Lidere\Modules\Api\Controllers\RelatorioVisitas:add');
        $app->options('/(R|r)elatorio-visitas/upload', 'Lidere\Modules\Api\Controllers\RelatorioVisitas:options');
        $app->post('/(R|r)elatorio-visitas/upload', 'Lidere\Modules\Api\Controllers\RelatorioVisitas:upload');


        // Valida Serie
        $app->options('/(V|v)alida-serie', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->post('/(V|v)alida-serie', 'Lidere\Modules\Api\Controllers\ValidaSerie:read');

        // $app->get('/(C|c)omercial/(R|r)elatorio(V|v)isitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:index');
        // $app->get('/(C|c)omercial/(R|r)elatorio(V|v)isitas(/:id)', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:form');
        // $app->post('(C|c)omercial/(R|r)elatorio(V|v)isitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:add');
        // $app->put('(C|c)omercial/(R|r)elatorio(V|v)isitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:edit');
        // $app->delete('(C|c)omercial/(R|r)elatorio(V|v)isitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:delete');

         // Insere Teste
        $app->options('/(I|n)sere-teste', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->post('/(I|n)sere-teste', 'Lidere\Modules\Api\Controllers\InsereTeste:create');
        $app->post('/(I|n)sere-teste-detalhes', 'Lidere\Modules\Api\Controllers\InsereTeste:createDetalhes');
        $app->post('/(I|n)sere-teste-ciclo', 'Lidere\Modules\Api\Controllers\InsereTeste:createCiclo');

    });


    $app->group('/site', function () use ($app) {

        // CategoriaConcorrentes
        $app->options('/(R|r)evendedores/:token', 'Lidere\Modules\Api\Controllers\Core\Api:options');
        $app->get('/(R|r)evendedores/:token', 'Lidere\Modules\Api\Controllers\Revendedores:index');

    });
});
