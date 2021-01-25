<?php

/**
	Relatório Visitas
*/

$app->get('/comercial/relatorio-visitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:index');
$app->get('/comercial/relatorio-visitas/pagina/:pagina', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:pagina');
$app->get('/comercial/relatorio-visitas/(adicionar|editar)(/:id)', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:form');
$app->post('/comercial/relatorio-visitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:add');
$app->put('/comercial/relatorio-visitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:edit');
$app->delete('/comercial/relatorio-visitas', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:delete');
$app->get('/comercial/relatorio-visitas/arquivos/download/:link', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:download');

$app->delete('/comercial/relatorio-visitas/excluir/arquivo', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:excluirArquivo');

$app->get('/comercial/relatorio-visitas/imprimir/:ids', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitas:imprimir');

/**
	Concorrentes
*/

$app->get('/comercial/cadastros/concorrentes', 'Lidere\Modules\Comercial\Controllers\Concorrentes:index');
$app->get('/comercial/cadastros/concorrentes/pagina/:pagina', 'Lidere\Modules\Comercial\Controllers\Concorrentes:pagina');
$app->get('/comercial/cadastros/concorrentes/(adicionar|editar)(/:id)', 'Lidere\Modules\Comercial\Controllers\Concorrentes:form');
$app->post('/comercial/cadastros/concorrentes', 'Lidere\Modules\Comercial\Controllers\Concorrentes:add');
$app->put('/comercial/cadastros/concorrentes', 'Lidere\Modules\Comercial\Controllers\Concorrentes:edit');
$app->delete('/comercial/cadastros/concorrentes', 'Lidere\Modules\Comercial\Controllers\Concorrentes:delete');


/**
	Categoria Concorrentes
*/

$app->get('/comercial/cadastros/categoria-concorrentes', 'Lidere\Modules\Comercial\Controllers\CategoriaConcorrentes:index');
$app->get('/comercial/cadastros/categoria-concorrentes/pagina/:pagina', 'Lidere\Modules\Comercial\Controllers\CategoriaConcorrentes:pagina');
$app->get('/comercial/cadastros/categoria-concorrentes/(adicionar|editar)(/:id)', 'Lidere\Modules\Comercial\Controllers\CategoriaConcorrentes:form');
$app->post('/comercial/cadastros/categoria-concorrentes', 'Lidere\Modules\Comercial\Controllers\CategoriaConcorrentes:add');
$app->put('/comercial/cadastros/categoria-concorrentes', 'Lidere\Modules\Comercial\Controllers\CategoriaConcorrentes:edit');
$app->delete('/comercial/cadastros/categoria-concorrentes', 'Lidere\Modules\Comercial\Controllers\CategoriaConcorrentes:delete');


/**
	AJAX
**/

$app->get('/comercial/ajax/retorna-concorrentes-por-categoria', 'Lidere\Modules\Comercial\Controllers\Ajax:retornaConcorrentesPorCategoriaSelect2');
$app->get('/comercial/relatorio-visitas/ajax/retorna-telefone-estabelecimento', 'Lidere\Modules\Comercial\Controllers\Ajax:retornaTelefoneEstabelecimento');
$app->get('/comercial/ajax/retorna-select2-clientes-consulta', 'Lidere\Modules\Comercial\Controllers\Ajax:retornaEstabelecimentosProspectsConsultaSelect2');

$app->get('/comercial/ajax/relatorio-visita-montadora/retorna-participantes', 'Lidere\Modules\Comercial\Controllers\Ajax:retornaParticipantesSelect2');

$app->get('/comercial/ajax/retorna-select2-clientes-erp', 'Lidere\Modules\Comercial\Controllers\Ajax:retornaClientesErpSelect2');

$app->post('/comercial/ajax/nao-foi-possivel-contato', 'Lidere\Modules\Comercial\Controllers\Ajax:naoFoiPossivelContato');


/**
	RELATORIO VISITAS MONTADORAS
**/


$app->get('/comercial-montadoras/relatorio-visitas-montadoras', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:index');
$app->get('/comercial-montadoras/relatorio-visitas-montadoras/pagina/:pagina', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:pagina');
$app->get('/comercial-montadoras/relatorio-visitas-montadoras/(adicionar|editar)(/:id)', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:form');
$app->post('/comercial-montadoras/relatorio-visitas-montadoras', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:add');
$app->put('/comercial-montadoras/relatorio-visitas-montadoras', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:edit');
$app->delete('/comercial-montadoras/relatorio-visitas-montadoras', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:delete');
$app->get('/comercial-montadoras/relatorio-visitas-montadoras/arquivos/download/:link', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:download');

$app->delete('/comercial-montadoras/relatorio-visitas-montadoras/excluir/arquivo', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:excluirArquivo');

$app->get('/comercial-montadoras/relatorio-visitas-montadoras/imprimir/:ids', 'Lidere\Modules\Comercial\Controllers\RelatorioVisitasMontadoras:imprimir');



