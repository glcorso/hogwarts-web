<?php
/**
	Ordem Serviço
*/

$app->get('/assistencia-externa/ordem-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:index');
$app->get('/assistencia-externa/ordem-servico/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:pagina');
$app->get('/assistencia-externa/ordem-servico/(adicionar|editar)(/:id)', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:form');
$app->post('/assistencia-externa/ordem-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:add');
$app->put('/assistencia-externa/ordem-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:edit');
$app->delete('/assistencia-externa/ordem-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:delete');
$app->put('/assistencia-externa/ordem-servico/editar-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:editServicos');
$app->put('/assistencia-externa/ordem-servico/aprovar-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:aprovarServicos');
$app->put('/assistencia-externa/ordem-servico/concluir-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:concluirServicos');
$app->get('/assistencia-externa/ordem-servico/arquivos/download/:link', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:download');
$app->get('/assistencia-externa/ordem-servico/imprimir-atendimento/:id', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:imprimirAtendimento');
$app->get('/assistencia-externa/ordem-servico/imprimir-conclusao/:id', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:imprimirConclusao');

/**
	Categorias
*/

$app->get('/assistencia-externa/categorias-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\Categorias:index');
$app->get('/assistencia-externa/categorias-servico/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\Categorias:pagina');
$app->get('/assistencia-externa/categorias-servico/(adicionar|editar)(/:id)', 'Lidere\Modules\AssistenciaExterna\Controllers\Categorias:form');
$app->post('/assistencia-externa/categorias-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\Categorias:add');
$app->put('/assistencia-externa/categorias-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\Categorias:edit');
$app->delete('/assistencia-externa/categorias-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\Categorias:delete');

/**
	Serviços
*/

$app->get('/assistencia-externa/servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\Servicos:index');
$app->get('/assistencia-externa/servicos/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\Servicos:pagina');
$app->get('/assistencia-externa/servicos/(adicionar|editar)(/:id)', 'Lidere\Modules\AssistenciaExterna\Controllers\Servicos:form');
$app->post('/assistencia-externa/servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\Servicos:add');
$app->put('/assistencia-externa/servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\Servicos:edit');
$app->delete('/assistencia-externa/servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\Servicos:delete');


/**
	Valores por Categoria
*/

$app->get('/assistencia-externa/valores-categoria', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresCategoria:index');
$app->get('/assistencia-externa/valores-categoria/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresCategoria:pagina');
$app->get('/assistencia-externa/valores-categoria/(adicionar|editar)(/:id)', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresCategoria:form');
$app->post('/assistencia-externa/valores-categoria', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresCategoria:add');
$app->put('/assistencia-externa/valores-categoria', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresCategoria:edit');
$app->delete('/assistencia-externa/valores-categoria', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresCategoria:delete');

/**
	Valores por Serviço
*/
$app->get('/assistencia-externa/valores-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresServico:index');
$app->get('/assistencia-externa/valores-servicos/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresServico:pagina');
$app->get('/assistencia-externa/valores-servicos/(adicionar|editar)(/:id)', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresServico:form');
$app->post('/assistencia-externa/valores-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresServico:add');
$app->put('/assistencia-externa/valores-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresServico:edit');
$app->delete('/assistencia-externa/valores-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\ValoresServico:delete');

/**
	Encerramento - Ordens
*/

$app->get('/assistencia-externa/encerramento-ordens', 'Lidere\Modules\AssistenciaExterna\Controllers\EncerramentoOrdens:index');
$app->get('/assistencia-externa/encerramento-ordens/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\EncerramentoOrdens:pagina');
$app->post('/assistencia-externa/encerramento-ordens','Lidere\Modules\AssistenciaExterna\Controllers\EncerramentoOrdens:add');

/**
	Valores por Serviço
*/
$app->get('/assistencia-externa/agrupador-itens', 'Lidere\Modules\AssistenciaExterna\Controllers\Agrupadores:index');
$app->get('/assistencia-externa/agrupador-itens/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\Agrupadores:pagina');
$app->get('/assistencia-externa/agrupador-itens/(adicionar|editar)(/:id)', 'Lidere\Modules\AssistenciaExterna\Controllers\Agrupadores:form');
$app->post('/assistencia-externa/agrupador-itens', 'Lidere\Modules\AssistenciaExterna\Controllers\Agrupadores:add');
$app->put('/assistencia-externa/agrupador-itens', 'Lidere\Modules\AssistenciaExterna\Controllers\Agrupadores:edit');
$app->delete('/assistencia-externa/agrupador-itens', 'Lidere\Modules\AssistenciaExterna\Controllers\Agrupadores:delete');


/**
*	Geração de Pagamentos das Ordens
*/

$app->get('/assistencia-externa/pagamento-ordem', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoOrdem:index');
$app->get('/assistencia-externa/pagamento-ordem/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoOrdem:pagina');

$app->post('/assistencia-externa/pagamento-ordem/gera-pagamento','Lidere\Modules\AssistenciaExterna\Controllers\PagamentoOrdem:geraPagamento');

/**
*	Geração de Pagamentos Efetuados das Ordens
*/

$app->get('/assistencia-externa/pagamento-efetuado', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:index');

$app->get('/assistencia-externa/pagamento-efetuado/pagina/:pagina', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:pagina');

$app->post('/assistencia-externa/pagamento-efetuado/autorizar', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:autorizar');

$app->get('/assistencia-externa/pagamento-efetuado/imprimir-pagamento/:id','Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:imprimirPagamento');

$app->post('/assistencia-externa/pagamento-efetuado/anexar-nfe', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:anexarNFe');

$app->get('/assistencia-externa/pagamento-efetuado/retorna-anexos', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:retornaAnexos');

$app->get('/assistencia-externa/pagamento-efetuado/download/:link', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:download');

$app->delete('/assistencia-externa/pagamento-efetuado', 'Lidere\Modules\AssistenciaExterna\Controllers\PagamentoEfetuado:delete');


/**
	Ajax
*/

$app->get('/assistencia-externa/itens', 'Lidere\Modules\AssistenciaExterna\Controllers\Ajax:retornaItens');
$app->post('/assistencia-tecnica/atendimento/ajax/reprovar-servico', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:reprovarServicos');
$app->get('/assistencia-externa/verifica-servicos', 'Lidere\Modules\AssistenciaExterna\Controllers\Ajax:retornaItemListaPreco');
$app->post('/assistencia-externa/anexa-imagem-assinada', 'Lidere\Modules\AssistenciaExterna\Controllers\Ajax:anexaImagemAssinada');
$app->get('/assistencia-externa/atendimento/ajax/retorna-usuarios-consulta-select2', 'Lidere\Modules\AssistenciaExterna\Controllers\OrdemServico:retornaUsuariosConsultaSelect2');
$app->get('/assistencia-externa/atendimento/ajax/retorna-assistencias-consulta-select2', 'Lidere\Modules\AssistenciaExterna\Controllers\Ajax:retornaAssistenciasExternasConsultaSelect2');


/** 

	Relatórios

**/

$app->get('/assistencia-externa/relatorios/ocorrencias-por-item', 'Lidere\Modules\AssistenciaExterna\Controllers\Relatorios:ocorrenciasItem');


$app->get('/assistencia-externa/relatorios/ocorrencias-por-item/imprimir', 'Lidere\Modules\AssistenciaExterna\Controllers\Relatorios:imprimirOcorrenciasItem');