relatorioVisitasFormJs = {
    y:9999,
    onReady: function() {
        uploadFiles();
        adicionaSelect2ClienteCapa($('#select-cliente'));
        adicionaAcoesBotoes();
        adicionaSelect2Categorias($('#clima_concorrente'), 'clima');
        adicionaSelect2Categorias($('#rodoar_concorrente'), 'rodoar');
        adicionaSelect2Categorias($('#geladeira_concorrente'), 'geladeira');
        validacoesCampos();
        mascaras();
        deletaArquivos();
        validaHorarios();
        adicionarParticipantes();
        removerParticipantes();
        bloqueiaSubmit();
        adicionaSelectParticipante();
        adicionaSelectEmpresaCliente();
        removeSelectEmpresaCliente();
        removeSelectParticipante();
        semContato();
        adicionaSelect2Participante($('.select-participantes-table'));
        adicionaSelect2ClienteMontadora($('.select-cliente-existente-table'));
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

    formatRepo2: function (repo) {
        if (repo.loading) return 'Buscando...';
        var markup = '<div>' +
            '<div>' + repo.descricao + '</div>' +
            '</div>';
        markup += '</div></div>';
        return markup;
    },

    formatRepoSelection2: function (repo) {
        if (repo.descricao) {
            return repo.descricao;
        } else {
            return repo.text;
        }
    },


    formatRepo3: function (repo) {
        if (repo.loading) return 'Buscando...';
        var markup = '<div>' +
            '<div>' + repo.nome + '</div>' +
            '</div>';
        markup += '</div></div>';
        return markup;
    },

    formatRepoSelection3: function (repo) {
        if (repo.nome) {
            return repo.nome;
        } else {
            return repo.text;
        }
    },

    formatRepo4: function (repo) {
        if (repo.loading) return 'Buscando...';
        var markup = '<div>' +
            '<div>' + repo.cod_cli + ' - ' + repo.descricao + '</div>' +
            '</div>';
        markup += '</div></div>';
        return markup;
    },

    formatRepoSelection4: function (repo) {
        if ( repo.cod_cli ) {
            return repo.cod_cli + ' - ' + repo.descricao;
        } else if (repo.descricao) {
            return repo.descricao;
        } else {
            return repo.text;
        }
    },

};

var uploadFiles = function () {
    $("#upload").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 10000,
        maxFilesNum: 20,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        },

    }); 
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
        templateResult: relatorioVisitasFormJs.formatRepo,
        templateSelection: relatorioVisitasFormJs.formatRepoSelection

    }).on('select2:select', function (e) {
            var selected_element = $(e.currentTarget);
            var est_id = selected_element.val();

            $.ajax({
                type: 'GET',
                url: '/comercial/relatorio-visitas/ajax/retorna-telefone-estabelecimento',
                data: {'est_id': est_id },
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if(data.telefone_formatado){
                        $("#telefone-cliente").val(data.telefone_formatado);
                    }
                }
            });


    });

};


var adicionaAcoesBotoes = function () {

    $('#btn-cliente-novo').on('click', function() {
        $('.div-cliente').hide();
        $('.div-cliente-novo').show();
    });
    $('#btn-cliente-existente').on('click', function() {
        $('.div-cliente').show();
        $('.div-cliente-novo').hide();
    });

    $('#check-outros').on('change',function() {
        if($(this).is(":checked")) {
            $('#descreva_motivo').removeAttr('disabled');   
        }else{
            $('#descreva_motivo').attr('disabled','disabled'); 
        }
    }).trigger('change'); 

    $('#check-geladeiras-outros').on('change',function() {
        if($(this).is(":checked")) {
            $('#geladeira_concorrente').removeAttr('disabled');   
            $('#geladeira_concorrente').attr("required","required");
        }else{
            $('#geladeira_concorrente').attr('disabled','disabled'); 
            $('#geladeira_concorrente').removeAttr("required");   
        }
    }).trigger('change'); 

    $('#check-clima-outros').on('change',function() {
        if($(this).is(":checked")) {
            $('#clima_concorrente').removeAttr('disabled');   
            $('#clima_concorrente').attr("required","required");
        }else{
            $('#clima_concorrente').attr('disabled','disabled'); 
            $('#clima_concorrente').removeAttr("required");    
        }
    }).trigger('change'); 

    $('#check-rodoar-outros').on('change',function() {
        if($(this).is(":checked")) {
            $('#rodoar_concorrente').removeAttr('disabled'); 
            $('#rodoar_concorrente').attr("required","required");  
        }else{
            $('#rodoar_concorrente').attr('disabled','disabled'); 
            $('#rodoar_concorrente').removeAttr("required");
        }
    }).trigger('change'); 
};

var adicionaSelect2Categorias = function(campo , categoria) {

    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o nome do concorrente para pesquisar',
        ajax: {
            url: '/comercial/ajax/retorna-concorrentes-por-categoria',
            dataType: 'json',
            type: 'GET',
            delay: 250,
            data: function (params) {
                return { 
                    codigoOuDescricao: params.term,
                    categoriaItem: categoria 
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
        templateResult: relatorioVisitasFormJs.formatRepo2,
        templateSelection: relatorioVisitasFormJs.formatRepoSelection2

    });

};

var validacoesCampos = function() {
    $('#btn-cliente-novo').on('click', function () {
        $('#select-cliente').removeAttr("required");
        $('#razao_social').attr("required","required");
        $('#cidade').attr("required","required");
        $('#uf').attr("required","required");
        $('#contato').attr("required","required");
        $('#telefone').attr("required","required");
        $('#contato-cliente').removeAttr("required");
        $('#telefone-cliente').removeAttr("required");
    });


    $('#btn-cliente-existente').on('click', function () {
        $('#select-cliente').attr("required","required");
        $('#razao_social').removeAttr("required");
        $('#cidade').removeAttr("required");
        $('#uf').removeAttr("required");
        $('#contato').removeAttr("required");
        $('#telefone').removeAttr("required");
        $('#contato-cliente').attr("required","required");
        $('#telefone-cliente').attr("required","required");
    });

    if($('#id').val() != null){
        if($('#prospect_id').val() != ''){
            $('#select-cliente').removeAttr("required");
            $('#razao_social').attr("required","required");
            $('#cidade').attr("required","required");
            $('#uf').attr("required","required");
            $('#contato').attr("required","required");
            $('#telefone').attr("required","required");
            $('#contato-cliente').removeAttr("required");
            $('#telefone-cliente').removeAttr("required");
        }else{
            $('#select-cliente').attr("required","required");
            $('#razao_social').removeAttr("required");
            $('#cidade').removeAttr("required");
            $('#uf').removeAttr("required");
            $('#contato').removeAttr("required");
            $('#telefone').removeAttr("required");
            $('#contato-cliente').attr("required","required");
            $('#telefone-cliente').attr("required","required");
        }
    }
};

var mascaras = function () {
    $("#telefone").mask('(00) 00000-0000');
    $("#telefone-cliente").mask('(00) 000000000');
    $("#celular").mask('(00) 00000-0000');
    $("#cnpj_cpf").on('blur',function() {
        cpf_cnpj = $(this).val();
        if( valida_cpf_cnpj( cpf_cnpj )){
            $("#cnpj_cpf").val(formata_cpf_cnpj( cpf_cnpj ))
        }else{
            $.alert('O documento informado não é valido!');
            $("#cnpj_cpf").val('');
        }
    });
    $('.data').mask('00/00/0000');
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

var validaHorarios = function() {
    if(setor != 'admin'){
        var dt = new Date();
        var time = ("0"+dt.getHours()).slice(-2)  + ":" + ("0"+dt.getMinutes()).slice(-2) + ":" + ("0"+dt.getSeconds()).slice(-2);

        var startDate = new Date("1/1/1900 " + hora_ini);
        var endDate = new Date("1/1/1900 " + hora_fim);
        var horaAtual = new Date("1/1/1900 " + time);

        if (horaAtual >= startDate && horaAtual <= endDate){
           $('#btn-adicionar').show();
        }
        else{
           $('#btn-adicionar').remove();
        }
    }
};

var adicionarParticipantes = function () {

    if(tipo_rel == 'mont'){
        if(rel_id != ''){
           $('.btn-adicionar-participantes').on('click', function () {
               var row = $('.row-participantes-0').clone().removeClass('row-participantes-0').addClass('row-participantes-'+relatorioVisitasFormJs.y).removeClass('hidden');
               row.find('.nome').attr('name', 'participante['+relatorioVisitasFormJs.y+'][nome]').removeAttr("disabled");
               row.find('.select-participantes').attr('name', 'participante['+relatorioVisitasFormJs.y+'][participante_id]').attr('data-id',relatorioVisitasFormJs.y);
               row.find('.email').attr('name', 'participante['+relatorioVisitasFormJs.y+'][e_mail]').removeAttr("disabled");
               row.find('.setor').attr('name', 'participante['+relatorioVisitasFormJs.y+'][setor]').removeAttr("disabled");
               row.find('.cliente_descritivo').attr('name', 'participante['+relatorioVisitasFormJs.y+'][cliente_descritivo]').removeAttr("disabled");
               row.find('.select-cliente-existente').attr('name', 'participante['+relatorioVisitasFormJs.y+'][cliente_id]');
               row.find('.nome').val('');
               row.find('.email').val('');
               row.find('.setor').val('');
               row.find('.select-cliente-existente').val('');
               row.find('.cliente_descritivo').val('');
               row.find('.btn-remover-participante').removeAttr("disabled").removeClass('disabled');

               $('.div-participantes').append(row);
               relatorioVisitasFormJs.y++;
           });
        }else{
             $('.btn-adicionar-participantes').on('click', function () {
               var row = $('.row-participantes-0').clone().removeClass('row-participantes-0').addClass('row-participantes-'+relatorioVisitasFormJs.y).removeClass('hidden');
               row.find('.nome').attr('name', 'participante['+relatorioVisitasFormJs.y+'][nome]').removeAttr("disabled");
               row.find('.select-participantes').attr('name', 'participante['+relatorioVisitasFormJs.y+'][participante_id]').attr('data-id',relatorioVisitasFormJs.y);
               row.find('.email').attr('name', 'participante['+relatorioVisitasFormJs.y+'][e_mail]').removeAttr("disabled");
               row.find('.setor').attr('name', 'participante['+relatorioVisitasFormJs.y+'][setor]').removeAttr("disabled");
               row.find('.cliente_descritivo').attr('name', 'participante['+relatorioVisitasFormJs.y+'][cliente_descritivo]').removeAttr("disabled");
               row.find('.select-cliente-existente').attr('name', 'participante['+relatorioVisitasFormJs.y+'][cliente_id]');
               row.find('.nome').val('');
               row.find('.email').val('');
               row.find('.setor').val('');
               row.find('.select-cliente-existente').val('');
               row.find('.cliente_descritivo').val('');
               row.find('.btn-remover-participante').removeAttr("disabled").removeClass('disabled');

               $('.div-participantes').append(row);
               relatorioVisitasFormJs.y++;
           }).trigger('click');
        }
    
    }
};

var removerParticipantes = function () {
    $(document).on('click','.btn-remover-participante' , function() {
        if($('.btn-remover-participante:visible').length > 1 ) {
           $(this).parent().parent().parent().parent().remove();
        } 
    });
};

var bloqueiaSubmit = function () {
   $('.form-visitas').on('submit', function(e) {
         //  e.preventDefault();
         //  e.stopPropagation();
         $('#btn-adicionar').attr('disabled','disabled');
        //$this.button('loading');
    });
};

var adicionaSelectParticipante = function() {

    $(document).on('click','.btn-select-participante', function() {
        var tr = $(this).parent().parent(),
            select = tr.find('.select-participantes');
        tr.find('.nome').attr('disabled', 'disabled').hide();
        tr.find('.select-participantes').removeAttr("disabled").show();
        tr.find('.participante-query').show();
        adicionaSelect2Participante(select);

        $(this).removeClass('btn-select-participante').addClass('btn-remove-select-participante');
    });

};

var removeSelectParticipante = function() {

    $(document).on('click','.btn-remove-select-participante', function() {
        var tr = $(this).parent().parent(),
            select = tr.find('.select-participantes');
        tr.find('.nome').removeAttr('disabled').show();
        tr.find('.select-participantes').attr("disabled","disabled").hide();
        tr.find('.participante-query').hide();

        $(this).removeClass('btn-remove-select-participante').addClass('btn-select-participante');

    });

};

var adicionaSelectEmpresaCliente = function() {

    $(document).on('click','.link-cliente-existente', function() {
        var tr = $(this).parent().parent(),
            select = tr.find('.select-cliente-existente');
        tr.find('.cliente_descritivo').attr('disabled', 'disabled').hide();
        tr.find('.select-cliente-existente').removeAttr("disabled").show();
        tr.find('.cliente-existente').show();
        $(this).html('Incluir Cliente Novo');
        $(this).removeClass('link-cliente-existente').addClass('link-novo-cliente');

        adicionaSelect2ClienteMontadora(select);

    });

};

var removeSelectEmpresaCliente = function() {

    $(document).on('click','.link-novo-cliente', function() {
        var tr = $(this).parent().parent(),
            select = tr.find('.select-cliente-existente');
        tr.find('.cliente_descritivo').removeAttr('disabled').show();
        tr.find('.select-cliente-existente').attr("disabled","disabled").hide();
        tr.find('.cliente-existente').hide();
        $(this).html('Selecionar Cliente Existente');
        $(this).removeClass('link-novo-cliente').addClass('link-cliente-existente');

    });

};

var adicionaSelect2Participante = function(campo){

     campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o nome do participante para pesquisar',
        ajax: {
            url: '/comercial/ajax/relatorio-visita-montadora/retorna-participantes',
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
        templateResult: relatorioVisitasFormJs.formatRepo3,
        templateSelection: relatorioVisitasFormJs.formatRepoSelection3

    }).on('select2:select', function (e) {
            var data = e.params.data,
                selected_element = $(e.currentTarget),
                est_id = selected_element.val(),
                campo_id = selected_element.data('id');

            $('input[name="participante['+campo_id+'][e_mail]"]').val(data.e_mail);
            $('input[name="participante['+campo_id+'][setor]"]').val(data.setor);

            console.log(data.cliente);
            if(data.cliente){


                var dados = {
                    id: data.cliente_id,
                    text: data.cliente.cod_cli+' - '+data.cliente.descricao,
                };
                var newOption = false,
                newOption = new Option(dados.text, dados.id, true, true);

                $('select[name="participante['+campo_id+'][cliente_id]"]').removeAttr("disabled").show();
                adicionaSelect2ClienteMontadora($('select[name="participante['+campo_id+'][cliente_id]"]'));
                $('select[name="participante['+campo_id+'][cliente_id]"]').parent().show();
                $('input[name="participante['+campo_id+'][cliente_descritivo]"]').attr('disabled','disabled').hide();
                $('select[name="participante['+campo_id+'][cliente_id]"]').append(newOption).trigger('change');
                $('select[name="participante['+campo_id+'][cliente_id]"]').parent().parent().find('.link-cliente-existente').html('Incluir Cliente Novo').removeClass('link-cliente-existente').addClass('link-novo-cliente');
            }else{
                $('input[name="participante['+campo_id+'][cliente_descritivo]"]').val(data.cliente_descritivo);
            }

     

    });
};


var adicionaSelect2ClienteMontadora = function(campo) {

    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição pesquisar',
        ajax: {
            url: '/comercial/ajax/retorna-select2-clientes-erp',
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
        templateResult: relatorioVisitasFormJs.formatRepo4,
        templateSelection: relatorioVisitasFormJs.formatRepoSelection4

    });

};

var semContato = function() {
    $('.btn-sem-contato').on('click', function() {

        var id = $(this).data('id');

        $.confirm({
            title: 'Não foi possível entrar em contato',
            content: ' Ao confirmar esta operação, será possível entrar em contato e finalizar o relatório posteriormente. Nenhuma observação será salva.',
            buttons: {
                formSubmit: {
                    text: 'Salvar',
                    btnClass: 'btn-green',     
                    action: function () {
                        
                        $.ajax({
                            type: 'POST',
                            url: '/comercial/ajax/nao-foi-possivel-contato',
                            data: {'id':id},
                            dataType: 'json',
                            success: function (data, textStatus, jqXHR) {
                                    if (!data.error ){
                                        window.location.replace("/comercial/relatorio-visitas/pagina/1?");
                                    }else{
                                       $.alert({
                                        title: 'Erro!',
                                        content: 'Não foi atualizar o status do relatório! Tente Novamente!',
                                    });
                                }
                            }
                           });
                    }
                },
                Cancelar: function () {
                },
            },
        });
    });
};

$(document).ready(function(){
    relatorioVisitasFormJs.onReady();
});