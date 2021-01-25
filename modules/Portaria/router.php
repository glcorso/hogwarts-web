<?php

/** Controle **/

$app->get('/portaria/controles', 'Lidere\Modules\Portaria\Controllers\Controles:pagina');

$app->get('/portaria/controles/pagina/:pagina', 'Lidere\Modules\Portaria\Controllers\Controles:pagina');

$app->post('/portaria/addAjax', 'Lidere\Modules\Portaria\Controllers\Controles:addAjax');

$app->put('/portaria/editAjax', 'Lidere\Modules\Portaria\Controllers\Controles:editAjax');

$app->delete('/portaria/controles', 'Lidere\Modules\Portaria\Controllers\Controles:delete');

$app->get('/portaria/controle/retorna-placa-anterior', 'Lidere\Modules\Portaria\Controllers\Controles:retornaPlacaAnterior');


/** Tipo de Assunto **/

$app->get('/portaria/tipo-assunto', 'Lidere\Modules\Portaria\Controllers\TipoAssuntos:index');

$app->get('/portaria/tipo-assunto/pagina/:pagina', 'Lidere\Modules\Portaria\Controllers\TipoAssuntos:pagina');

$app->get('/portaria/tipo-assunto/(adicionar|editar)(/:id)', 'Lidere\Modules\Portaria\Controllers\TipoAssuntos:form');

$app->post('/portaria/tipo-assunto', 'Lidere\Modules\Portaria\Controllers\TipoAssuntos:add');

$app->put('/portaria/tipo-assunto', 'Lidere\Modules\Portaria\Controllers\TipoAssuntos:edit');

$app->delete('/portaria/tipo-assunto', 'Lidere\Modules\Portaria\Controllers\TipoAssuntos:delete');


/** Veículos **/

$app->get('/portaria/veiculos', 'Lidere\Modules\Portaria\Controllers\Veiculos:index');

$app->get('/portaria/veiculos/pagina/:pagina', 'Lidere\Modules\Portaria\Controllers\Veiculos:pagina');

$app->get('/portaria/veiculos/(adicionar|editar)(/:id)', 'Lidere\Modules\Portaria\Controllers\Veiculos:form');

$app->post('/portaria/veiculos', 'Lidere\Modules\Portaria\Controllers\Veiculos:add');

$app->put('/portaria/veiculos', 'Lidere\Modules\Portaria\Controllers\Veiculos:edit');

$app->delete('/portaria/veiculos', 'Lidere\Modules\Portaria\Controllers\Veiculos:delete');

$app->get('/portaria/veiculos/retorna-km', 'Lidere\Modules\Portaria\Controllers\Veiculos:retornaKm');


/** Funcionários **/

$app->get('/portaria/funcionarios', 'Lidere\Modules\Portaria\Controllers\Funcionarios:index');

$app->get('/portaria/funcionarios/pagina/:pagina', 'Lidere\Modules\Portaria\Controllers\Funcionarios:pagina');

$app->get('/portaria/funcionarios/(adicionar|editar)(/:id)', 'Lidere\Modules\Portaria\Controllers\Funcionarios:form');

$app->post('/portaria/funcionarios', 'Lidere\Modules\Portaria\Controllers\Funcionarios:add');

$app->put('/portaria/funcionarios', 'Lidere\Modules\Portaria\Controllers\Funcionarios:edit');

$app->delete('/portaria/funcionarios', 'Lidere\Modules\Portaria\Controllers\Funcionarios:delete');
