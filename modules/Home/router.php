<?php

$app->get('/', 'Lidere\Modules\Home\Controllers\Home:index');
$app->get('/home', 'Lidere\Modules\Home\Controllers\Home:index');
$app->get('/home/arquivos/download/:link', 'Lidere\Modules\Home\Controllers\Home:download');
$app->get('/home/arquivos/lista-preco/download/:link', 'Lidere\Modules\Home\Controllers\Home:downloadLista');
$app->get('/home/arquivos/aviso/download/:link', 'Lidere\Modules\Home\Controllers\Home:downloadAviso');