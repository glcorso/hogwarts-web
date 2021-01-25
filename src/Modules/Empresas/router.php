<?php

/* List */
$app->get('/auxiliares/empresas', 'Lidere\Modules\Empresas\Controllers\Empresas:index');
$app->get('/auxiliares/empresas/pagina/:pagina', 'Lidere\Modules\Empresas\Controllers\Empresas:pagina');

/* Form */
$app->get('/auxiliares/empresas/(adicionar|editar)(/:id)', 'Lidere\Modules\Empresas\Controllers\Empresas:form');

/* Add*/
$app->post('/auxiliares/empresas', 'Lidere\Modules\Empresas\Controllers\Empresas:add');

/* Edit */
$app->put('/auxiliares/empresas', 'Lidere\Modules\Empresas\Controllers\Empresas:edit');

/* Delete */
$app->delete('/auxiliares/empresas', 'Lidere\Modules\Empresas\Controllers\Empresas:delete');

$app->get('/auxiliares/empresas/arquivos/download/:link', 'Lidere\Modules\Empresas\Controllers\Empresas:download');
$app->delete('/auxiliares/empresas/arquivos/apagar-arquivo', 'Lidere\Modules\Empresas\Controllers\Empresas:deleteArquivo');