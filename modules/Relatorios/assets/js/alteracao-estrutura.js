alteracaoEstrutura = {
    onReady: function() {

        adicionaSelect2Item();
        btnEnviarEmail();


        $('.btn-filtrar').on('click', function(){
            $('.form-filtro').attr('action', '/relatorios/alteracao-estrutura').removeAttr('target').submit();
        });

        $('.btn-limpar-filtro').on('click', function(){
            $('.form-filtro').find('input:text, input:password, input:file, select, textarea').val('');
            $('.select-item').val(null).trigger('change');
        });


        $('#check-sel-all-print').click(function(event) { 
            var values = [];  
            if(this.checked) { 
                if($('.checked-linhas').length > 0){
                    $('.btn-enviar-email').show();
                }
                // Iterate each checkbox
                $('.check:checkbox').each(function() {
                    this.checked = true;            
                    values.push($(this).val());            
                });
                $('#registros_ids').val(values.join('-'));
            } else {
                $('.check:checkbox').each(function() {
                    this.checked = false;                   
                });
                $('.btn-enviar-email').hide();
                $('#registros_ids').val('');

            }

        });

        $('.check').click(function(event) {   
            var count =  $('.check:checked').length;
            if(this.checked) { 
                $('.btn-enviar-email').show();
               
            }else{
                if(count == 0){
                    $('.btn-enviar-email').hide();
                    $('#registros_ids').val('');
                }
            }

            var values = $('.check:checked').map(function() {
                        return $(this).val();
                    }).toArray();
            $('#registros_ids').val(values.join('-'));
        });


    },

    formatRepo: function (repo) {
        if (repo.loading) return 'Buscando...';
        var markup = '<div>' +
            '<div>' + repo.cod_item + ' - ' + repo.desc_tecnica + '</div>' +
            '</div>';
        markup += '</div></div>';
        return markup;
    },

    formatRepoSelection: function (repo) {
        if ( repo.cod_item ) {
            return repo.cod_item + ' - ' + repo.desc_tecnica;
        } else if (repo.desc_tecnica) {
            return repo.desc_tecnica;
        } else {
            return repo.text;
        }
    },
};


var adicionaSelect2Item = function() {
    $('.select-item').select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/relatorios/ajax/itens',
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
        minimumInputLength: 3,
        templateResult: alteracaoEstrutura.formatRepo,
        templateSelection: alteracaoEstrutura.formatRepoSelection

    });

};

var btnEnviarEmail = function() {

    $('.btn-enviar-email').on('click', function() {
        $.confirm({
            title: 'Enviar E-mails',
            content: 'Você tem certeza que deseja enviar os registros selecionados por e-mail?',
            buttons: {
                formSubmit: {
                    text: 'Sim',
                    btnClass: 'btn-green',     
                    action: function () {
                        var form = this.$content.find('.form-material-recebido').serialize();

                            $.ajax({
                                type: 'POST',
                                url: '/relatorios/ajax/enviar-email-alteracao-estrutura',
                                data: {'registros_ids': $('#registros_ids').val()},
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
    });

};


$(document).ready(function(){
   alteracaoEstrutura.onReady();
});