<?php

/**
	Assistência
*/

$app->get('/assistencia-tecnica/atendimento(/:id)', 'Lidere\Modules\Assistencia\Controllers\Atendimento:index');
$app->post('/assistencia-tecnica/atendimento', 'Lidere\Modules\Assistencia\Controllers\Atendimento:add');
$app->put('/assistencia-tecnica/atendimento', 'Lidere\Modules\Assistencia\Controllers\Atendimento:edit');
$app->get('/assistencia-tecnica/atendimento/arquivos/download/:link', 'Lidere\Modules\Assistencia\Controllers\Atendimento:download');
$app->delete('/assistencia-tecnica/atendimento/excluir/arquivo', 'Lidere\Modules\Assistencia\Controllers\Atendimento:excluirArquivo');
$app->delete('/assistencia-tecnica/atendimento/excluir/arquivo/laudo', 'Lidere\Modules\Assistencia\Controllers\Atendimento:excluirArquivoLaudo');
$app->get('/assistencia-tecnica/atendimento/imprimir/:id', 'Lidere\Modules\Assistencia\Controllers\Atendimento:imprimirAtendimento');

/* Detalhes */
$app->get('/assistencia-tecnica/atendimento/detalhes/:id', 'Lidere\Modules\Assistencia\Controllers\Atendimento:detalhes');
$app->put('/assistencia-tecnica/atendimento/detalhes', 'Lidere\Modules\Assistencia\Controllers\Atendimento:editDetalhes');
$app->delete('/assistencia-tecnica/atendimento/detalhes/excluir/atendimento-tecnico', 'Lidere\Modules\Assistencia\Controllers\Atendimento:excluirAtendimentoTecnico');
$app->get('/assistencia-tecnica/atendimento/arquivos/laudo/download/:link', 'Lidere\Modules\Assistencia\Controllers\Atendimento:downloadLaudo');

/* Laudo */
$app->get('/assistencia-tecnica/atendimento/detalhes/laudo/:id', 'Lidere\Modules\Assistencia\Controllers\Atendimento:imprimirLaudo');

/**
	Consulta
*/

$app->get('/assistencia-tecnica/consulta', 'Lidere\Modules\Assistencia\Controllers\Consulta:index');
$app->get('/assistencia-tecnica/consulta/pagina/:pagina', 'Lidere\Modules\Assistencia\Controllers\Consulta:pagina');

$app->get('/assistencia-tecnica/consulta/excel', 'Lidere\Modules\Assistencia\Controllers\Consulta:excel');
/**
	Ajax
*/
$app->post('/assistencia-tecnica/atendimento/ajax/busca-cliente-assistencia', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClienteAssistencia');
$app->post('/assistencia-tecnica/atendimento/ajax/busca-cliente-erp', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClienteErp');
$app->post('/assistencia-tecnica/atendimento/ajax/cadastrar-cliente', 'Lidere\Modules\Assistencia\Controllers\Ajax:cadastrarCliente');
$app->post('/assistencia-tecnica/atendimento/ajax/busca-dados-item', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaSerieSequencial');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-itens', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaItens');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-clientes', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClientesSelect2');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-motivos', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaMotivosSelect2');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-historico-atendimentos', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaHistoricoAtendimentos');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-defeitos', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaDefeitosSelect2');
$app->get('/assistencia-tecnica/atendimento/ajax/verifica-defeito-obrigatorio', 'Lidere\Modules\Assistencia\Controllers\Ajax:verificaDefeitoObrigatorio');
$app->get('/assistencia-tecnica/atendimento/ajax/verifica-protocolo-cliente', 'Lidere\Modules\Assistencia\Controllers\Ajax:verificaProtocoloCliente');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-clientes-assistencia', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClientesAssistenciaSelect2');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-fornecedores', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaFornecedoresSelect2');
$app->post('/assistencia-tecnica/atendimento/ajax/adicionar-atendimento-protocolo', 'Lidere\Modules\Assistencia\Controllers\Ajax:adicionarAtendimentoProtocolo');
$app->post('/assistencia-tecnica/atendimento/ajax/editar-atendimento-protocolo', 'Lidere\Modules\Assistencia\Controllers\Ajax:editarAtendimentoProtocolo');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-chamados-erp', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaChamadosErpSelect2');
$app->post('/assistencia-tecnica/atendimento/ajax/busca-cliente-erp-id', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClienteERPById');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-clientes-consulta', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClientesConsultaSelect2');
$app->post('/assistencia-tecnica/consulta/ajax/material-recebido', 'Lidere\Modules\Assistencia\Controllers\Ajax:confirmarRecebimento');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-clientes-cnpj', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClientesCnpjSelect2');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-clientes-usuarios-select2', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaClientesUsuariosSelect2');
$app->get('/assistencia-tecnica/atendimento/ajax/retorna-usuarios-consulta-select2', 'Lidere\Modules\Assistencia\Controllers\Ajax:retornaUsuariosConsultaSelect2');

/**
	Motivos
*/

$app->get('/assistencia-tecnica/motivos', 'Lidere\Modules\Assistencia\Controllers\Motivos:index');
$app->get('/assistencia-tecnica/motivos/(adicionar|editar)(/:id)', 'Lidere\Modules\Assistencia\Controllers\Motivos:form');
$app->post('/assistencia-tecnica/motivos/add', 'Lidere\Modules\Assistencia\Controllers\Motivos:add');
$app->put('/assistencia-tecnica/motivos/edit', 'Lidere\Modules\Assistencia\Controllers\Motivos:edit');
$app->delete('/assistencia-tecnica/motivos', 'Lidere\Modules\Assistencia\Controllers\Motivos:delete');

/**
	Defeitos
*/

$app->get('/assistencia-tecnica/defeitos', 'Lidere\Modules\Assistencia\Controllers\Defeitos:index');
$app->get('/assistencia-tecnica/defeitos/(adicionar|editar)(/:id)', 'Lidere\Modules\Assistencia\Controllers\Defeitos:form');
$app->post('/assistencia-tecnica/defeitos/add', 'Lidere\Modules\Assistencia\Controllers\Defeitos:add');
$app->put('/assistencia-tecnica/defeitos/edit', 'Lidere\Modules\Assistencia\Controllers\Defeitos:edit');
$app->delete('/assistencia-tecnica/defeitos', 'Lidere\Modules\Assistencia\Controllers\Defeitos:delete');

/**
	Itens
*/

$app->get('/assistencia-tecnica/itens', 'Lidere\Modules\Assistencia\Controllers\Itens:index');
$app->get('/assistencia-tecnica/itens/(adicionar|editar)(/:id)', 'Lidere\Modules\Assistencia\Controllers\Itens:form');
$app->post('/assistencia-tecnica/itens/add', 'Lidere\Modules\Assistencia\Controllers\Itens:add');
$app->put('/assistencia-tecnica/itens/edit', 'Lidere\Modules\Assistencia\Controllers\Itens:edit');
$app->delete('/assistencia-tecnica/itens', 'Lidere\Modules\Assistencia\Controllers\Itens:delete');


/**
	Painel
*/

$app->get('/assistencia-tecnica/painel', 'Lidere\Modules\Assistencia\Controllers\Painel:index');

/**
	Geração de Protocolo
*/

$app->get('/assistencia-tecnica/geracao-protocolo-interno', 'Lidere\Modules\Assistencia\Controllers\GeracaoProtocolo:index');

$app->post('/assistencia-tecnica/geracao-protocolo-interno', 'Lidere\Modules\Assistencia\Controllers\GeracaoProtocolo:add');

/**
	Relatórios
*/

$app->get('/assistencia-tecnica/relatorios/defeitos-item', 'Lidere\Modules\Assistencia\Controllers\Relatorios:defeitosItem');

$app->get('/assistencia-tecnica/relatorios/listagem', 'Lidere\Modules\Assistencia\Controllers\Relatorios:listagem');

$app->get('/assistencia-tecnica/relatorios/defeitos-item/imprimir', 'Lidere\Modules\Assistencia\Controllers\Relatorios:defeitosItemImprimir');

$app->get('/assistencia-tecnica/relatorios/listagem/imprimir', 'Lidere\Modules\Assistencia\Controllers\Relatorios:listagemImprimir');



/**
	RastreamentoGarantia
*/

$app->get('/assistencia-tecnica/rastreamento-garantias', 'Lidere\Modules\Assistencia\Controllers\RastreamentoGarantia:index');
$app->post('/assistencia-tecnica/rastreamento-garantias/realiza-operacoes', 'Lidere\Modules\Assistencia\Controllers\RastreamentoGarantia:operacoes');

$app->post('/assistencia/rastreamento-garantias/anexar-nfe', 'Lidere\Modules\Assistencia\Controllers\RastreamentoGarantia:anexarNfe');