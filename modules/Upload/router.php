<?php

$app->get('/upload', 'Lidere\Modules\Upload\Controllers\Uploads:index');
$app->get('/upload/pagina/:pagina', 'Lidere\Modules\Upload\Controllers\Uploads:pagina');
$app->get('/upload/(adicionar|editar)(/:id)', 'Lidere\Modules\Upload\Controllers\Uploads:form');
$app->post('/upload', 'Lidere\Modules\Upload\Controllers\Uploads:add');
$app->put('/upload', 'Lidere\Modules\Upload\Controllers\Uploads:edit');
