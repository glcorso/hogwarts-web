<?php

$app->get('/logs', 'Lidere\Modules\Logs\Controllers\Logs:index');
$app->get('/logs/pagina/:pagina', 'Lidere\Modules\Logs\Controllers\Logs:pagina');
