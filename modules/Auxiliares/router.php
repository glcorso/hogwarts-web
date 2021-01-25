<?php

/* List */
$app->get('/auxiliares/parametros', 'Lidere\Modules\Auxiliares\Controllers\Parametros:index');

/* Form */
$app->get('/auxiliares/parametros/(adicionar|editar)(/:id)', 'Lidere\Modules\Auxiliares\Controllers\Parametros:form');

/* Edit */
$app->put('/auxiliares/parametros', 'Lidere\Modules\Auxiliares\Controllers\Parametros:edit');

$app->get('/auxiliares/usuarios', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:index');

/* List */
$app->get('/auxiliares/usuarios/pagina/:pagina', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:pagina');

/* Form */
$app->get('/auxiliares/usuarios/(adicionar|editar)(/:id)', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:form');

/* Add*/
$app->post('/auxiliares/usuarios', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:add');

/* Edit */
$app->put('/auxiliares/usuarios', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:edit');

/* Delete */
$app->delete('/auxiliares/usuarios', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:delete');

/**

 SETORES

**/

$app->get('/auxiliares/setores', 'Lidere\Modules\Auxiliares\Controllers\Setores:index');

/* List */
$app->get('/auxiliares/setores/pagina/:pagina', 'Lidere\Modules\Auxiliares\Controllers\Setores:pagina');

/* Form */
$app->get('/auxiliares/setores/(adicionar|editar)(/:id)', 'Lidere\Modules\Auxiliares\Controllers\Setores:form');

/* Add*/
$app->post('/auxiliares/setores', 'Lidere\Modules\Auxiliares\Controllers\Setores:add');

/* Edit */
$app->put('/auxiliares/setores', 'Lidere\Modules\Auxiliares\Controllers\Setores:edit');

/* Delete */
$app->delete('/auxiliares/setores', 'Lidere\Modules\Auxiliares\Controllers\Setores:delete');



/**

 PERFIS

**/

$app->get('/auxiliares/perfis', 'Lidere\Modules\Auxiliares\Controllers\Perfis:index');

/* List */
$app->get('/auxiliares/perfis/pagina/:pagina', 'Lidere\Modules\Auxiliares\Controllers\Perfis:pagina');

/* Form */
$app->get('/auxiliares/perfis/(adicionar|editar)(/:id)', 'Lidere\Modules\Auxiliares\Controllers\Perfis:form');

/* Add*/
$app->post('/auxiliares/perfis', 'Lidere\Modules\Auxiliares\Controllers\Perfis:add');

/* Edit */
$app->put('/auxiliares/perfis', 'Lidere\Modules\Auxiliares\Controllers\Perfis:edit');

/* Delete */
$app->delete('/auxiliares/perfis', 'Lidere\Modules\Auxiliares\Controllers\Perfis:delete');



/**

 Vinculo Vendedor

**/

$app->get('/auxiliares/vinculo-vendedor', 'Lidere\Modules\Auxiliares\Controllers\VinculoVendedor:index');

/* List */
$app->get('/auxiliares/vinculo-vendedor/pagina/:pagina', 'Lidere\Modules\Auxiliares\Controllers\VinculoVendedor:pagina');

/* Form */
$app->get('/auxiliares/vinculo-vendedor/(adicionar|editar)(/:id)', 'Lidere\Modules\Auxiliares\Controllers\VinculoVendedor:form');

/* Add*/
$app->post('/auxiliares/vinculo-vendedor', 'Lidere\Modules\Auxiliares\Controllers\VinculoVendedor:add');

/* Edit */
$app->put('/auxiliares/vinculo-vendedor', 'Lidere\Modules\Auxiliares\Controllers\VinculoVendedor:edit');

/* Delete */
$app->delete('/auxiliares/vinculo-vendedor', 'Lidere\Modules\Auxiliares\Controllers\VinculoVendedor:delete');


$app->get('/usuarios/arquivos/download/:link', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:download');
$app->delete('/auxiliares/usuarios/apagar-arquivo', 'Lidere\Modules\Auxiliares\Controllers\Usuarios:deleteArquivo');