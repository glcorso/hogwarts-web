<?php

$app->get('/ajax/teste', 'Lidere\Modules\Ajax\Controllers\Ajax:teste');
//$app->post('/ajax/teste', 'Lidere\Modules\Ajax\Controllers\Ajax:teste');
$app->post('/ajax/controla-empresa-principal', 'Lidere\Modules\Ajax\Controllers\Ajax:controlaEmpresaPrincipal');
$app->get('/ajax/cliente-erp', 'Lidere\Modules\Ajax\Controllers\Ajax:cliente');
