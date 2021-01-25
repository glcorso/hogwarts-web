<?php
/**
	Consulta
*/

$app->get('/plano-producao/consulta', 'Lidere\Modules\PlanoProducao\Controllers\Consulta:index');

/**
	Ajax
*/

$app->post('/plano-producao/ajax/retorna-demandas-ordem', 'Lidere\Modules\PlanoProducao\Controllers\Ajax:retornaDemandasOrdem');



/**
	Vinculo
*/

$app->get('/plano-producao/vinculo', 'Lidere\Modules\PlanoProducao\Controllers\Vinculo:index');
$app->get('/plano-producao/vinculo/(adicionar|editar)(/:id)', 'Lidere\Modules\PlanoProducao\Controllers\Vinculo:form');
$app->post('/plano-producao/vinculo/add', 'Lidere\Modules\PlanoProducao\Controllers\Vinculo:add');
$app->put('/plano-producao/vinculo/edit', 'Lidere\Modules\PlanoProducao\Controllers\Vinculo:edit');
$app->delete('/plano-producao/vinculo', 'Lidere\Modules\PlanoProducao\Controllers\Vinculo:delete');