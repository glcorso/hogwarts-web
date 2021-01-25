<?php

$app->get('/login', 'Lidere\Modules\Login\Controllers\Login:index');
$app->post('/login', 'Lidere\Modules\Login\Controllers\Login:login');
$app->get('/forgot', 'Lidere\Modules\Login\Controllers\Login:forgot');
$app->post('/forgot', 'Lidere\Modules\Login\Controllers\Login:recover');
$app->get('/new-password', 'Lidere\Modules\Login\Controllers\Login:newPassword');
$app->post('/new-password', 'Lidere\Modules\Login\Controllers\Login:resetaSenha');
$app->map('/error', 'Lidere\Modules\Login\Controllers\Login:error')->via('GET', 'POST');
$app->get('/logout', 'Lidere\Modules\Login\Controllers\Login:logout');
