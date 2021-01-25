<?php

/**
	Avisos
*/

$app->get('/avisos/cadastro', 'Lidere\Modules\Avisos\Controllers\Cadastro:index');
$app->get('/avisos/cadastro/pagina/:pagina', 'Lidere\Modules\Avisos\Controllers\Cadastro:pagina');
$app->get('/avisos/cadastro/(adicionar|editar)(/:id)', 'Lidere\Modules\Avisos\Controllers\Cadastro:form');
$app->post('/avisos/cadastro', 'Lidere\Modules\Avisos\Controllers\Cadastro:add');
$app->put('/avisos/cadastro', 'Lidere\Modules\Avisos\Controllers\Cadastro:edit');
$app->delete('/avisos/cadastro', 'Lidere\Modules\Avisos\Controllers\Cadastro:delete');
$app->get('/avisos/cadastro/arquivos/download/:link', 'Lidere\Modules\Avisos\Controllers\Cadastro:download');
$app->delete('/avisos/cadastro/excluir/arquivo', 'Lidere\Modules\Avisos\Controllers\Cadastro:deleteArquivo');

