relatoriosJs = {
    onReady: function() {
    	adicionaSelect2Item();
        adicionaSelect2Cliente();
        adicionaSelect2Motivos();
        adicionaSelect2Defeitos();
        adicionaSelect2ClienteInterno();


        $('.btn-filtrar-rel-def').on('click', function(){
            $('.form-filtro').attr('action', '/assistencia-tecnica/relatorios/defeitos-item').removeAttr('target').submit();
        });

        $('.btn-imprimir-rel-def').on('click', function(){
            $('.form-filtro').attr('action', '/assistencia-tecnica/relatorios/defeitos-item/imprimir').attr('target', '_blank').submit();
        });

        $('.btn-filtrar-listagem').on('click', function(){
            $('.form-filtro').attr('action', '/assistencia-tecnica/relatorios/listagem').removeAttr('target').submit();
        });

        $('.btn-imprimir-listagem').on('click', function(){
            $('.form-filtro').attr('action', '/assistencia-tecnica/relatorios/listagem/imprimir').attr('target', '_blank').submit();
        });

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


var adicionaSelect2Cliente = function() {

    $('#select-cliente').select2({
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
        templateResult: relatoriosJs.formatRepo,
        templateSelection: relatoriosJs.formatRepoSelection

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
        templateResult: relatoriosJs.formatRepo,
        templateSelection: relatoriosJs.formatRepoSelection

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
        templateResult: relatoriosJs.formatRepo,
        templateSelection: relatoriosJs.formatRepoSelection

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
                    testaParametro: 'N'
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
        templateResult: relatoriosJs.formatRepo,
        templateSelection: relatoriosJs.formatRepoSelection

    });

};

var adicionaSelect2ClienteInterno = function() {

    $('#select-cliente-interno').select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite a descrição para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-clientes-consulta',
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
        templateResult: relatoriosJs.formatRepo,
        templateSelection: relatoriosJs.formatRepoSelection

    });

};


var abreModalRecebimento = function (registro_id, protocolo, material,usuario_id){
    $.confirm({
        title: 'Material Recebido',
        content: '' +
        '<form action="javascript:void(0);" class="form-material-recebido" action="#" method="post" >' +
            '<div class="form-group">' +
                '<strong>Você confirma o Recebimento do Material <strong>'+ material +'</strong> com o Protocolo <strong style="font-size:20px;">'+ protocolo +' </strong>?' +
                '<br><small>Após confirmar o recebimento, este procedimento não poderá ser desfeito.</small>' +
                '<input type="hidden" name="registro_id" value="'+registro_id+'"/>' +
            '</div>' +
        '</form>',
        onOpen: function () {
            //$(".telefone").mask('(00) 0000-00000');
        },
        buttons: {
            formSubmit: {
                text: 'Confirmar Recebimento',
                btnClass: 'btn-green',     
                action: function () {
                    var form = this.$content.find('.form-material-recebido').serialize();

                        $.ajax({
                            type: 'POST',
                            url: '/assistencia-tecnica/consulta/ajax/material-recebido',
                            data: form,
                            dataType: 'json',
                            success: function(data, textStatus, jqXHR) {
                                if (!data.error) {
                                    window.location.reload();
                                }
                            }
                        });
                }
            },
            Cancelar: function () {
            },
        },
    });
};


$(document).ready(function(){
    relatoriosJs.onReady();
});