<?php

/**
	EstruturaProduto
*/

$app->get('/relatorios/estrutura-produto', 'Lidere\Modules\Relatorios\Controllers\EstruturaProduto:index');

$app->get('/relatorios/estrutura-produto/imprimir', 'Lidere\Modules\Relatorios\Controllers\EstruturaProduto:imprimir');


/**
	AJAX
*/

$app->get('/relatorios/ajax/retorna-itens-pn', 'Lidere\Modules\Relatorios\Controllers\Ajax:retornaItensPn');

$app->get('/relatorios/ajax/itens', 'Lidere\Modules\Relatorios\Controllers\Ajax:retornaItens');

$app->post('/relatorios/ajax/enviar-email-alteracao-estrutura', 'Lidere\Modules\Relatorios\Controllers\Ajax:enviaEmailAlteracaoEstrutura');

/**
	AlteracaoEstrutura
*/


$app->get('/relatorios/alteracao-estrutura', 'Lidere\Modules\Relatorios\Controllers\AlteracaoEstrutura:pagina');

$app->get('/relatorios/alteracao-estrutura/pagina/:pagina', 'Lidere\Modules\Relatorios\Controllers\AlteracaoEstrutura:pagina');

$app->get('/relatorios/alteracao-estrutura/imprimir', 'Lidere\Modules\Relatorios\Controllers\AlteracaoEstrutura:imprimir');
