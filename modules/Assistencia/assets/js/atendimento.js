atendimentoJs = {
    onReady: function() {
        gerarProtocolo();
        validaProtocolo();
        validarCPF_CNPJ();
        retornaSerie();
        retornaSequencial();
        adicionaSelect2Item();
        adicionaSelect2Cliente($('#select-cliente'));
        adicionaSelect2Cliente($('#select-cliente-envio'));
        adicionaSelect2Motivos();
        adicionaSelect2Defeitos();
        mascaras();
        acoesTela();
        uploadFiles();
        retornaTimeline($('#campo-cpf-cnpj').val());
        deletaArquivos();
        abreModalProcurarClienteNome();
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

var gerarProtocolo = function () {

	$('#btn-gerar-protocolo').on('click', function () {
		var currentdate = new Date()
		  , day = ("0" + currentdate.getDate()).slice(-2)
		  , year = currentdate.getFullYear().toString().substr(-2)
		  , month = ("0" + (currentdate.getMonth()+1)).slice(-2)
		  , minute = ("0" + currentdate.getMinutes()).slice(-2)
		  , second = ("0" + currentdate.getMilliseconds()).slice(-3)
		  , complement = '2'
		  , protocolo = '';

		protocolo = complement+second+day+month+year;

	   
	    $('#campo-protocolo').val(protocolo);
	});

};

var validarCPF_CNPJ = function (){

	$('#campo-cpf-cnpj').blur(function(){
        var cpf_cnpj = $(this).val(),
            existe_erp = false,
            cpf_cnpj_number = $(this).val().replace(/[^\d]+/g,'');

        if ( valida_cpf_cnpj( cpf_cnpj ) ) {
            buscaCliente(cpf_cnpj_number,'erp');
        } else {
            $.alert({
			    title: 'Erro!',
			    content: 'O Documento informado é invalido!',
			});
            $(this).val('');
        }
        
    });
};


var buscaCliente = function(cpf_cnpj, tipo) {

	url = (tipo == 'erp' ) ? '/assistencia-tecnica/atendimento/ajax/busca-cliente-erp' : '/assistencia-tecnica/atendimento/ajax/busca-cliente-assistencia'

	$.ajax({
        type: 'POST',
        url: url,
        data: {'cpf_cnpj': cpf_cnpj},
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
           	if (!data.error ){
	            $('#campo-cpf-cnpj').val( formata_cpf_cnpj( cpf_cnpj ) );
             	preencheCamposCliente(data.cliente, tipo);
                retornaTimeline(cpf_cnpj);
                if(typeof $('#campo-id').val() == 'undefined' ){
                    vincularProtocolo(cpf_cnpj);
                }
           	}else{
           		if(tipo == 'erp'){
	        		buscaCliente(cpf_cnpj,'assistencia');
	        	}else{
	        		abreModalNovoCliente(cpf_cnpj);
	        	}
           	}
        }
   	});

};

var preencheCamposCliente = function (cliente, tipo) {
	$('#campo-nome-cliente').val(cliente.nome);

    if(tipo == 'erp'){
        $('#campo-nome-cliente').attr('readonly','readonly');
	    $('#campo-cliente-assistencia-erp-id').val(cliente.id);
        $('#campo-cliente-assistencia-id').val('');
    }else{
        $('#campo-nome-cliente').removeAttr('readonly');
        $('#campo-cliente-assistencia-erp-id').val('');
        $('#campo-cliente-assistencia-id').val(cliente.id);
    }

	if(cliente.e_mail !== false ){
		$('#campo-email').val(cliente.e_mail);	
	}
	if(cliente.telefone !== false ){
		$('#campo-telefone').val(cliente.telefone);	
	}
};

var abreModalNovoCliente = function (cpf_cnpj){
	$.confirm({
	    title: 'Cadastrar Novo Cliente',
	    content: '' +
	    '<form action="javascript:void(0);" class="form-novo-cliente">' +
		    '<div class="form-group">' +
			    '<input type="hidden" name="cpf_cnpj" class="cpf_cnpj form-control" value="'+cpf_cnpj+'"/>' +
			    '<label>Nome*</label>' +
			    '<input type="text" placeholder="Informe o Nome" maxlength="100" name="nome" class="name form-control" required />' +
		    '</div>' +
		    '<div class="form-group">' +
			    '<label>Telefone*</label>' +
			    '<input type="text" placeholder="Informe o Telefone" maxlength="80" name="telefone" class="fone form-control telefone" required />' +
			    '</div>' +
		    '<div class="form-group">' +
			    '<label>E-mail</label>' +
			    '<input type="text" placeholder="Informe o E-mail" maxlength="80" name="e_mail" class="mail form-control" />' +
			'</div>' +
	    '</form>',
        onOpen: function () {
            $(".telefone").mask('(00) 0000-00000');
        },
	    buttons: {
	        formSubmit: {
	            text: 'Salvar',
	            btnClass: 'btn-green',     
	            action: function () {
                   
	                var name = this.$content.find('.name').val(),
	                 	fone = this.$content.find('.fone').val(),
	                 	mail = this.$content.find('.mail').val(),
	                 	form = this.$content.find('form');
	                if(!name){
	                    $.alert('Informe o nome!');
	                    return false;
	                }
	                if(!fone){
	                    $.alert('Informe o Telefone!');
	                    return false;
	                }
	               
		        	$.ajax({
				        type: 'POST',
				        url: '/assistencia-tecnica/atendimento/ajax/cadastrar-cliente',
				        data: form.serialize(),
				        dataType: 'json',
				        success: function (data, textStatus, jqXHR) {
				           	if (!data.error ){
				             	preencheCamposCliente(data.cliente);
				           	}else{
				           		$.alert({
								    title: 'Erro!',
								    content: 'Não foi possível cadastrar o cliente! Tente Novamente!',
								});
				           	}
				        }
				   	});
	            }
	        },
	        Cancelar: function () {
	            $('#campo-cpf-cnpj').val('');
	        },
	    },
	});
};

var buscaDadosItem = function(codigo, tipo) {

	url = '/assistencia-tecnica/atendimento/ajax/busca-dados-item';

	$.ajax({
        type: 'POST',
        url: url,
        data: {'codigo': codigo , 'tipo':tipo},
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
 
           	if (!data.error ){
	
             	$('#campo-numero-serie-id').val(data.dadosSerie.serie_id);
                if(data.dadosSerie.sequencial_id != null){
             	    $('#campo-sequencial-id').val(data.dadosSerie.sequencial_id);
                    $('#campo-sequencial').val(data.dadosSerie.sequencial);
                }
             	$('#campo-numero-serie').val(data.dadosSerie.nro_serie);
             	$('#campo-data-fab').val(data.dadosSerie.data_fab);
             	$('#grupo-serie').removeClass('has-danger');
             	$('#grupo-sequencial').removeClass('has-danger');
             	$('#warning-serie').hide();
             	$('#warning-sequencial').hide();

				var item = {
				    id: data.dadosSerie.item_id,
				    text: data.dadosSerie.cod_item+' - '+data.dadosSerie.desc_tecnica
				};

				var selecionado_item = new Option(item.text, item.id, false, false);
				$('#select-item').append(selecionado_item).trigger('change');

                console.log(data.dadosSerie.cli_id);
                if(data.dadosSerie.cli_id != null){
    				var cliente = {
    				    id: data.dadosSerie.cli_id,
    				    text: data.dadosSerie.cod_cli+' - '+data.dadosSerie.descricao
    				};

    				var selecionado_cliente = new Option(cliente.text, cliente.id, false, false);
    				$('#select-cliente').append(selecionado_cliente).trigger('change');
                }

           	}else{
           		if(tipo == 'serie'){
	        		$('#grupo-serie').addClass('has-danger');
	        		$('#warning-serie').show();
	        		$('#campo-numero-serie').val('');
	        		$('#campo-sequencial').val('');
	        	}else{
	        		$('#grupo-sequencial').addClass('has-danger');
	        		$('#warning-sequencial').show();
	        		$('#campo-numero-serie').val('');
	        		$('#campo-sequencial').val('');
	        	}
           	}
        }
   	});

};

var retornaSerie = function() {
	$('#campo-numero-serie').on('blur', function() {
		var value = $(this).val();
		if(value != ''){
			buscaDadosItem(value,'serie');
		}
	});
};

var retornaSequencial = function() {
	$('#campo-sequencial').on('blur', function() {
		var value = $(this).val();
		if(value != ''){
			buscaDadosItem(value,'sequencial');
		}
	});
};


var adicionaSelect2Item = function() {
    $('#select-item').select2({
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
        templateResult: atendimentoJs.formatRepo,
        templateSelection: atendimentoJs.formatRepoSelection

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
        templateResult: atendimentoJs.formatRepo,
        templateSelection: atendimentoJs.formatRepoSelection

    });

};


var adicionaSelect2Motivos = function() {
    $('#select-motivo').select2({
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
        templateResult: atendimentoJs.formatRepo,
        templateSelection: atendimentoJs.formatRepoSelection

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
                       $('#select-defeito').attr('required','required');
                       $('#div-defeito').show();
                    }else{
                       $('#div-defeito').hide();
                       $('#select-defeito').removeAttr('required');
                    }
                }
            });
    });

};

var adicionaSelect2Defeitos = function() {
    $('#select-defeito').select2({
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
        templateResult: atendimentoJs.formatRepo,
        templateSelection: atendimentoJs.formatRepoSelection

    });

};

var retornaTimeline = function (cpf_cnpj) {
    if (cpf_cnpj != ''){
        $.ajax({
            type: 'GET',
            url: '/assistencia-tecnica/atendimento/ajax/retorna-historico-atendimentos',
            data: {'cpf_cnpj': cpf_cnpj },
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
     
                if (!data.error ){
                    var html = "";
                    $.each( data.historico, function( k, v ) {
                        if (k % 2 === 0) { 
                            html += '<li>';
                            html += '    <div class="timeline-badge success"><div class="timeline-badge info"><i class="fa fa-save"></i> </div> </div>';
                            html += '       <div class="timeline-panel">';
                            html += '          <div class="timeline-heading">';
                            html += '             <h4 class="timeline-title"><strong>Protocolo: <a href="/assistencia-tecnica/atendimento/'+v.id+'" target="_BLANK">#'+v.protocolo+'</a></strong></h4>';
                            html += '             <p><small class="text-muted"><i class="fa fa-clock-o"></i> '+v.data_criacao+'</small> </p>';
                            html += '          </div>';
                            html += '        <div class="timeline-body">';
                            html += '           <p><i>Item: '+v.cod_item+' - '+v.desc_tecnica+'</i></p>';
                            html += '           <p><i>Relato do Cliente: '+v.obs_cliente+'</i></p>';
                            html += '           <p><strong> Retratação Interna: '+v.obs_interna+'</strong></p>';
                            html += '        </div>';
                            html += '     </div>';
                            html += '</li>';

                        }else { 
                            html += '<li class="timeline-inverted">';
                            html += '    <div class="timeline-badge success"><div class="timeline-badge info"><i class="fa fa-save"></i> </div> </div>';
                            html += '       <div class="timeline-panel">';
                            html += '          <div class="timeline-heading">';
                            html += '             <h4 class="timeline-title"><strong>Protocolo: <a href="/assistencia-tecnica/atendimento/'+v.id+'" target="_BLANK">#'+v.protocolo+'</a></strong></h4>';
                            html += '             <p><small class="text-muted"><i class="fa fa-clock-o"></i> '+v.data_criacao+'</small> </p>';
                            html += '          </div>';
                            html += '        <div class="timeline-body">';
                            html += '           <p><i>Item: '+v.cod_item+' - '+v.desc_tecnica+'</i></p>';
                            html += '           <p><i>Relato do Cliente: '+v.obs_cliente+'</i></p>';
                            html += '           <p><strong> Retratação Interna: '+v.obs_interna+'</strong></p>';
                            html += '        </div>';
                            html += '     </div>';
                            html += '</li>';
                        }
                    });

                    $('#timeline').html(html);
                    $('.div-timeline').show();

                }   
            }
        });
    }   

};


var mascaras = function () {
	$(".data").mask('00/00/0000');
    $(".telefone").mask('(00) 0000-00000');
};

var acoesTela = function() {
	$('#btn-limpar-campos').on('click', function(){
		window.location.reload();
	});
};

var uploadFiles = function () {
    $("#upload").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 40000,
        maxFilesNum: 20,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        }

    }); 
};

var vincularProtocolo = function(cpf_cnpj) {

        $.ajax({
            type: 'GET',
            url: '/assistencia-tecnica/atendimento/ajax/verifica-protocolo-cliente',
            data: {'cpf_cnpj':cpf_cnpj },
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                if (!data.error ){
                    $.confirm({
                        title: 'Vínculo de Protocolo',
                        content: '' +
                        '<p>Este cliente possui um protocolo em aberto: <strong>'+data.dadosChamado.protocolo +'</strong></p>'+ 
                        '<p>Item: '+data.dadosChamado.cod_item+' - '+data.dadosChamado.desc_tecnica+'</p>'+ 
                        '<p>Deseja vincular a este atendimento ?</p>', 
                        buttons: {
                            formSubmit: {
                                text: 'Vincular',
                                btnClass: 'btn-green',     
                                action: function () {
                                    $('#campo-protocolo-vinculado').val(data.dadosChamado.id);
                                }
                            },
                            Cancelar: function () {
                                $('#campo-protocolo-vinculado').val('');
                            },
                        },
                    });
                }
            }
        });

};

var deletaArquivos = function () {

    $('.btn-delete-file-modal').on('click', function () {
        var element = $(this);
        var id = element.data('id'),
            name = element.data('name'),
            boxModal = $('body').find('.modal-delete-arquivo');

        boxModal.find('input[name="id"]').val(id);
        boxModal.find('[name="name"]').val(name);
        boxModal.find('.modal-body strong').html(name);
        boxModal.modal('toggle');
    });
};


validaProtocolo = function (){

    $('#campo-protocolo').on('blur',function (){
        val = $(this).val();

       /* if(val != ''){
            if(val.length > 10 ){
                $.alert({
                    title: 'Erro!',
                    content: 'O Protocolo é invalido!',
                });
                $(this).val('');
            }

        }*/
    });

};


var abreModalProcurarClienteNome = function (){
    $('#btn-procurar-nome').on('click' , function () {

        $.confirm({
            title: 'Buscar Cliente por Nome',
            columnClass: 'col-md-6 col-md-offset-3',
            content: '' +
                '<div class="form-group">' +
                    '<label>Cliente*</label>' +
                    '<select name="cliente_id" id="select-cliente-busca" class="form-control"></select>' +
                '</div>',
            onOpen: function () {
                adicionaSelect2Cliente($("#select-cliente-busca"));
            },
            buttons: {
                formSubmit: {
                    text: 'Adicionar',
                    btnClass: 'btn-green',     
                    action: function () {
                        var cliente_id = this.$content.find('#select-cliente-busca').val();

                        if(cliente_id != undefined){
                            $('#campo-cliente-assistencia-erp-id').val(cliente_id);
                            buscaClienteById(cliente_id);
                        }
                      
                    }
                },
                Cancelar: function () {
                },
            },
        });
    }); 
};

var buscaClienteById = function(id) {

    $.ajax({
        type: 'POST',
        url: '/assistencia-tecnica/atendimento/ajax/busca-cliente-erp-id',
        data: {'id': id},
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
            if (!data.error ){
                $('#campo-cpf-cnpj').val( formata_cpf_cnpj( data.cliente.cpf_cnpj ) );
                retornaTimeline(data.cliente.cpf_cnpj);
                preencheCamposCliente(data.cliente,'erp');
                if(typeof $('#campo-id').val() == 'undefined' ){
                    vincularProtocolo(data.cliente.cpf_cnpj);
                }
            }
        }
    });

};

$(document).ready(function(){
    atendimentoJs.onReady();
});
