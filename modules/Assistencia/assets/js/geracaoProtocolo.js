geracaoProtocoloJs = {
    y:9999,
    linha:2,
    index:1,
    onReady: function() {
        retornaSerie();
        retornaSequencial();
        adicionaSelect2Item($('#select-item-0'));
        adicionaSelect2Cliente($('#select-cliente-0'));
        adicionaSelect2ClienteCapa($('#select-cliente'));
        adicionaSelect2Motivos($('#select-motivo-0'),0);
        adicionaSelect2Defeitos($('#select-defeito-0'));
        mascaras();
        acoesTela();
    },

    formatRepo: function (repo) {
        if (repo.loading) return 'Buscando...';
        var markup = '<div>' +
            '<div>' + repo.codigo + ' - ' + repo.descricao + '</div>' +
            '</div>';
        markup += '</div></div>';
        return markup;
    },

    formatRepoSelection: function (repo) {
        if ( repo.codigo ) {
            return repo.codigo + ' - ' + repo.descricao;
        } else if (repo.descricao) {
            return repo.descricao;
        } else {
            return repo.text;
        }
    },
};

var buscaDadosItem = function(codigo, linha,  tipo) {

	url = '/assistencia-tecnica/atendimento/ajax/busca-dados-item';

	$.ajax({
        type: 'POST',
        url: url,
        data: {'codigo': codigo , 'tipo':tipo},
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
 
           	if (!data.error ){
             	$('#campo-numero-serie-id-'+linha).val(data.dadosSerie.serie_id);
                if(data.dadosSerie.sequencial_id != null){
             	    $('#campo-sequencial-id-'+linha).val(data.dadosSerie.sequencial_id);
                    $('#campo-sequencial-'+linha).val(data.dadosSerie.sequencial);
                }
             	$('#campo-numero-serie-'+linha).val(data.dadosSerie.nro_serie);
             	$('#campo-data-fab-'+linha).val(data.dadosSerie.data_fab);
               // $('#campo-data-compra-'+linha).val(data.dadosSerie.data_compra);
             	$('#grupo-serie-'+linha).removeClass('has-danger');
             	$('#grupo-sequencial-'+linha).removeClass('has-danger');
             	$('#warning-serie-'+linha).hide();
             	$('#warning-sequencial-'+linha).hide();

				var item = {
				    id: data.dadosSerie.item_id,
				    text: data.dadosSerie.cod_item+' - '+data.dadosSerie.desc_tecnica
				};

				var selecionado_item = new Option(item.text, item.id, false, false);
				$('#select-item-'+linha).append(selecionado_item).trigger('change');

           //     console.log(data.dadosSerie.cli_id);
                if(data.dadosSerie.cli_id != null){
    				var cliente = {
    				    id: data.dadosSerie.cli_id,
    				    text: data.dadosSerie.cod_cli+' - '+data.dadosSerie.descricao
    				};

    				var selecionado_cliente = new Option(cliente.text, cliente.id, false, false);
    				$('#select-cliente-'+linha).append(selecionado_cliente).trigger('change');
                }

           	}else{
           		if(tipo == 'serie'){
	        		$('#grupo-serie-'+linha).addClass('has-danger');
	        		$('#warning-serie-'+linha).show();
	        		$('#campo-numero-serie-'+linha).val('');
	        		$('#campo-sequencial-'+linha).val('');
	        	}else{
	        		$('#grupo-sequencial-'+linha).addClass('has-danger');
	        		$('#warning-sequencial-'+linha).show();
	        		$('#campo-numero-serie-'+linha).val('');
	        		$('#campo-sequencial-'+linha).val('');
	        	}
           	}
        }
   	});

};

var retornaSerie = function() {
	$(document).on('blur','.campo-numero-serie',function() {
		var value = $(this).val(),
            linha = $(this).data('linha');
		if(value != ''){
			buscaDadosItem(value,linha,'serie');
		}
	});
};

var retornaSequencial = function() {
	$(document).on('blur','.campo-sequencial', function() {
		var value = $(this).val(),
            linha = $(this).data('linha');
		if(value != ''){
			buscaDadosItem(value,linha,'sequencial');
		}
	});
};


var adicionaSelect2Item = function(campo) {
    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-itens',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return { 
                    codigoOuDescricao: params.term,
                    testaParametro: 'S'
                };
            },
            processResults: function (data) {
                return {
                    results: data.items
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 0,
        templateResult: geracaoProtocoloJs.formatRepo,
        templateSelection: geracaoProtocoloJs.formatRepoSelection

    });

};

var adicionaSelect2Cliente = function(campo) {

    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição do cliente para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-clientes',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return { 
                    codigoOuDescricao: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.items
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 2,
        templateResult: geracaoProtocoloJs.formatRepo,
        templateSelection: geracaoProtocoloJs.formatRepoSelection

    });

};


var adicionaSelect2Motivos = function(campo,index) {
    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-motivos',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return { 
                    codigoOuDescricao: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.items
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 0,
        templateResult: geracaoProtocoloJs.formatRepo,
        templateSelection: geracaoProtocoloJs.formatRepoSelection

    }).on('select2:select', function (e) {
            var selected_element = $(e.currentTarget);
            var id = selected_element.val();

            $.ajax({
                type: 'GET',
                url: '/assistencia-tecnica/atendimento/ajax/verifica-defeito-obrigatorio',
                data: {'id': id },
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if (data.defeito_obrigatorio ){
                       $('#select-defeito-'+index).attr('required','required');
                       $('#div-defeito-'+index).show();
                    }else{
                       $('#div-defeito-'+index).hide();
                       $('#select-defeito-'+index).removeAttr('required');
                    }
                }
            });
    });

};

var adicionaSelect2Defeitos = function(campo) {
    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-defeitos',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return { 
                    codigoOuDescricao: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.items
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 0,
        templateResult: geracaoProtocoloJs.formatRepo,
        templateSelection: geracaoProtocoloJs.formatRepoSelection

    });

};


var mascaras = function () {
	$(".data").mask('00/00/0000');
    $(".telefone").mask('(00) 0000-00000');
};

var acoesTela = function() {
	$('#btn-limpar-campos').on('click', function(){
		window.location.reload();
	});

    $('.btn-adicionar-produto').on('click', function(){
        adicionaTemplate();
    });

    $(document).on('click', '.btn-excluir', function(){
        var linha = $(this).data('id');
        $('.reg-'+linha).remove();
    });
};


var adicionaTemplate = function () {
    var html  = '<div class="reg-'+geracaoProtocoloJs.linha+'">';
    html += '<div class="card card-outline-info">';
        html+= '<div class="card-header">';
        html+= '    <h4 class="m-b-0 text-white">Item #'+geracaoProtocoloJs.linha+'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class=" btn btn-danger btn-excluir" data-id="'+geracaoProtocoloJs.linha+'"> Excluir </button></h4>';
        html+= '    ';
        html+= '</div>';
        html+= '<div class="card-body card-itens" >';
            html+= '<div class="row row-itens">';
                html+= '<div class="col-lg-6">';
                    html+= '<div class="form-group" id="grupo-serie-'+geracaoProtocoloJs.index+'">';
                    html+= '    <input type="hidden" name="item['+geracaoProtocoloJs.index+'][serie_id]" id="campo-numero-serie-id-'+geracaoProtocoloJs.index+'" value="">';
                    html+= '    <label>Número de Série</label>';
                    html+= '    <input type="text" name="item['+geracaoProtocoloJs.index+'][numero_serie]" id="campo-numero-serie-'+geracaoProtocoloJs.index+'" class="form-control campo-numero-serie" data-linha="'+geracaoProtocoloJs.index+'" placeholder="Informe o Número de Série" value="">';
                    html+= '<div class="form-control-feedback" style="display:none;" id="warning-serie-'+geracaoProtocoloJs.index+'" >Desculpe, mas este número de série não existe!</div>';
                    html+= '</div>';
                html+= '</div>';
                html+= '<div class="col-lg-6">';
                    html+= '<div class="form-group" id="grupo-sequencial-'+geracaoProtocoloJs.index+'">';
                    html+= '    <input type="hidden" name="item['+geracaoProtocoloJs.index+'][sequencial_id]" id="campo-sequencial-id-'+geracaoProtocoloJs.index+'" value="">';
                    html+= '    <label>Sequencial</label>';
                    html+= '    <input type="text"  name="item['+geracaoProtocoloJs.index+'][sequencial]"  id="campo-sequencial-'+geracaoProtocoloJs.index+'"  maxlength="100" class="form-control campo-sequencial" data-linha="'+geracaoProtocoloJs.index+'"   placeholder="Informe o Sequencial" value="">';
                    html+= '<div class="form-control-feedback" style="display:none;" id="warning-sequencial-'+geracaoProtocoloJs.index+'">Desculpe, mas este sequencial não existe!</div>';
                    html+= '</div>';
                html+= '</div>';
                html+= '<div class="col-lg-12">';
                    html+= '<div class="form-group">';
                        html+= '<label>Produto</label>';
                        html+= '<select name="item['+geracaoProtocoloJs.index+'][item_id]" id="select-item-'+geracaoProtocoloJs.index+'"  class="form-control" required="required">';
                        html+= '</select>';
                    html+= '</div>';
                html+= '</div>';
                html+= '<div class="col-lg-6">';
                    html+= '<div class="form-group">';
                        html+= '<label>Data Fabricação</label>';
                        html+= '<input type="text" name="item['+geracaoProtocoloJs.index+'][dt_fabricacao]" id="campo-data-fab-'+geracaoProtocoloJs.index+'" class="form-control data" placeholder="Informe a Data de Fabricação" value="">';
                    html+= '</div>';
                html+= '</div>';
                html+= '<div class="col-lg-6">';
                    html+= '<div class="form-group">';
                    html+= '<label>Data da Compra</label>';
                    html+= '<input type="text" name="item['+geracaoProtocoloJs.index+'][dt_compra]" id="campo-data-compra-'+geracaoProtocoloJs.index+'" class="form-control data" placeholder="Informe a Data de Compra" value="">';
                    html+= '</div>';
                html+= '</div>';
                html+= '<div class="col-lg-12">';
                    html+= '<div class="form-group">';
                        html+= '<label>Cliente Origem</label>';
                        html+= '<select name="item['+geracaoProtocoloJs.index+'][cliente_origem_id]" id="select-cliente-'+geracaoProtocoloJs.index+'"  class="form-control">';
                        html+= '</select>';
                    html+= '</div>';
                html+= '</div>';
                html+= '<div class="col-lg-12">';
                    html+= '<div class="form-group">';
                        html+= '<label>Motivo*</label>';
                        html+= '<select name="item['+geracaoProtocoloJs.index+'][motivo_id]" id="select-motivo-'+geracaoProtocoloJs.index+'" required="required" class="form-control">';
                        html+= '</select>';
                    html+= '</div>';
                html+= '</div>';
                html+= '<div class="col-lg-12" id="div-defeito-'+geracaoProtocoloJs.index+'" style="display:none;">';
                    html+= '<div class="form-group"> ';
                        html+= '<label>Defeito*</label> ';
                        html+= '<select name="item['+geracaoProtocoloJs.index+'][defeito_principal_id]" id="select-defeito-'+geracaoProtocoloJs.index+'"  class="form-control">';
                        html+= '</select>';
                    html+= '</div>';
                html+= '</div>   ';               
            html+= '</div>';
        html+= '</div>';
    html+= '</div>';
    html+= '</div>';


    $('.itens').append(html);
    adicionaSelect2Item($('#select-item-'+geracaoProtocoloJs.index));
    adicionaSelect2Cliente($('#select-cliente-'+geracaoProtocoloJs.index));
    adicionaSelect2Motivos($('#select-motivo-'+geracaoProtocoloJs.index),geracaoProtocoloJs.index);
    adicionaSelect2Defeitos($('#select-defeito-'+geracaoProtocoloJs.index));

    geracaoProtocoloJs.linha++;
    geracaoProtocoloJs.index++;
};


var buscaClienteById = function(id) {

    $.ajax({
        type: 'POST',
        url: '/assistencia-tecnica/atendimento/ajax/busca-cliente-erp-id',
        data: {'id': id},
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
            if (!data.error ){
                preencheCamposCliente(data.cliente,'erp');
            }
        }
    });

};


var preencheCamposCliente = function (cliente, tipo) {

    if(cliente.e_mail !== false ){
        $('#campo-email').val(cliente.e_mail);  
    }
    if(cliente.telefone !== false ){
        $('#campo-telefone').val(cliente.telefone); 
    }
};


var adicionaSelect2ClienteCapa = function(campo) {

    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição do cliente para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-clientes-cnpj',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return { 
                    codigoOuDescricao: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.items
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 2,
        templateResult: geracaoProtocoloJs.formatRepo,
        templateSelection: geracaoProtocoloJs.formatRepoSelection

    }).on('select2:select', function (e) {
            var selected_element = $(e.currentTarget);
            var id = selected_element.val();

            buscaClienteById(id);
    });

};


$(document).ready(function(){
    geracaoProtocoloJs.onReady();
});
