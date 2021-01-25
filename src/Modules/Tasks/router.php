<?php

/* List */
$app->get('/auxiliares/tasks', 'Lidere\Modules\Tasks\Controllers\Tasks:index');
$app->get('/auxiliares/tasks/pagina/:pagina', 'Lidere\Modules\Tasks\Controllers\Tasks:pagina');

/* Form */
$app->get('/auxiliares/tasks/(adicionar|editar)(/:id)', 'Lidere\Modules\Tasks\Controllers\Tasks:form');

/* Add*/
$app->post('/auxiliares/tasks', 'Lidere\Modules\Tasks\Controllers\Tasks:add');

/* Edit */
$app->put('/auxiliares/tasks', 'Lidere\Modules\Tasks\Controllers\Tasks:edit');

/* Delete */
$app->delete('/auxiliares/tasks', 'Lidere\Modules\Tasks\Controllers\Tasks:delete');
