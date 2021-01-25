var usuariosJs = {

    myValidate: null,

    onReady: function() {

        if ($('.form-usuario').length > 0) {
           usuariosJs.myValidate = $('.form-usuario').myValidate({
            instance: true,
            removeData: true,
            callError: function() {
                $('.btn-submit').button('reset');
            },
            callSuccess: function() {
                $('.btn-submit').button('reset');
            }
           });

            uploadFiles();
            deletaArquivos();
        }

        if ($('select[name=cliente_erp]').length > 0) {
            $('select[name=cliente_erp]').select2();
        }

        if ($('select.grupos-usuario').length > 0) {
            $('select.grupos-usuario').select2();
        }

        if ( $('input[name="id"]').val() == null ) {
            $('input[name="senha"]').attr('data-myrules','required');
        }

        if ($('.check_ad').length) {
            var  check = $('.check_ad');

            if ( check.val() == 1) {
                check.prop('checked', true);
                $('input[name="senha"]').prop( "disabled", true );
            }else{
                check.prop('checked', false);
                $('input[name="senha"]').prop( "disabled", false );
            }
        }


        $('#tipo').on('change', function() {
            var tipo = $(this).val();

            if(tipo == 'ate'){
                $('.setor').attr('disabled','disabled');
                $('.unidade').attr('disabled','disabled');
                $('.div-cliente').show();
            }else{
                $('.setor').removeAttr('disabled');
                $('.unidade').removeAttr('disabled');
                $('#select-cliente').removeAttr('disabled');
                $('.div-cliente').hide();
            }
        }).trigger('change');

        // $('input[name="nome"]').on('blur', function(){
        //     var nome = $(this).val();
        //     if(nome != ''){
        //         $.ajax({
        //             type: 'POST',
        //             url: '/ajax/retorna-usuario',
        //             data: {'nome': nome},
        //             dataType: 'json',
        //             success: function (data, textStatus, jqXHR) {
        //                 if (!data.error ){
        //                     $('input[name="usuario"]').val(data.user);
        //                 }
        //             }
        //         });
        //     }
        // });

        // $('input[name="usuario"]').on('blur', function(){
        //     var usuario = $(this).val(),
        //         id = $('input[name="id"]').val();
        //     if(usuario != ''){
        //         $.ajax({
        //             type: 'POST',
        //             url: '/ajax/valida-usuario',
        //             data: {'usuario': usuario, 'id': id},
        //             dataType: 'json',
        //             success: function (data, textStatus, jqXHR) {
        //                 if (!data.error ){
        //                     $('input[name="usuario"]').val(data.user);
        //                 }
        //             }
        //         });
        //     }
        // });

        $.fn.select2.amd.require([
            'select2/core',
            'select2/utils',
            'select2/compat/matcher'
        ], function(Select2, Utils, oldMatcher) {
            var $cliente = $('select[name=cliente_erp]');
            $cliente.select2({
                language: 'pt-BR',
                placeholder: 'Digite o código ou nome do cliente para pesquisar',
                width: '100%',
                ajax: {
                    url: '/assistencia-tecnica/atendimento/ajax/retorna-clientes-usuarios-select2',
                    dataType: 'json',
                    type: 'GET',
                    delay: 250,
                    data: function(params) {
                        // console.log($(select).data('item-id'));
                        return {
                            codigoOuDescricao: params.term,
                            // id: $(select).data('item-id'),
                            page: params.page
                        };
                    },
                    processResults: function(data, page) {
                        return {
                            results: data.items
                        };
                    },
                    cache: true
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
                minimumInputLength: 3,
                templateResult: function(repo) {
                    if (repo.loading) return repo.text;
                    var markup = '<div class="clearfix">' +
                        '<div class="col-sm-12">' + repo.codigo + ' - ' + repo.descricao + '</div>' +
                        '</div>';
                    markup += '</div></div>';
                    return markup;
                },
                templateSelection: function(repo) {
                    if (!repo.codigo && repo.id) {
                        var cli = repo.id.split('#');
                            repo.codigo = cli[1];
                            repo.descricao = cli[2];
                    }
                    return repo.codigo ? repo.codigo + ' - ' + repo.descricao : 'Digite o código ou nome do cliente para pesquisar';
                }
            });
        });

        if ( $('.form-usuario').length > 0 ) {
            $('.form-usuario').myValidate({
                beforeValidate : function() {
                    var btn = $('.btn-submit');
                    btn.button('loading');
                },
                callError : function(event, el, status) {
                    if (this.debug) { console.log(event, el, status); }
                    event.preventDefault();
                    event.stopPropagation();
                    var btn = $('.btn-submit');
                    btn.button('reset');
                },
                // Função executada quando não ha erro
                callSuccess : function(event, el, status) {
                    if (this.debug) { console.log(event, el, status); }
                    var btn = $('.btn-submit');
                    btn.button('reset');
                }
            });
        }

        // $('.btn-submit').on('click', function() {
        //     var btn = $(this);
        //     btn.button('loading');
        // });

        $('.check-all').on('click', function(){
            var empr_id = $(this).data('empr-id'),
                id = $(this).data('id');
            if ( $(this).is(':checked') ) {
                $('#tab_'+empr_id).find('.check-'+id).prop('checked', true);
                $('#tab_'+empr_id).find('.select-'+id).prop('disabled', false).attr('data-myrules', 'required');
                $('#tab_'+empr_id).find('#'+id).prop('disabled', false).attr('data-myrules', 'required');
            } else {
                $('#tab_'+empr_id).find('.check-'+id).prop('checked', false);
                $('#tab_'+empr_id).find('.select-'+id).prop('disabled', true).val('').removeAttr('data-myrules');
                $('#tab_'+empr_id).find('#'+id).prop('disabled', true).val('').removeAttr('data-myrules');
            }
            usuariosJs.myValidate.reset();
        });

        $('.select-all').on('change', function(){
            var empr_id = $(this).data('empr-id'),
                id = $(this).data('id'),
                value = $(this).val();
            $('#tab_'+empr_id).find('.select-'+id).val(value);
        });

        $('.check-menu').on('click', function(){
            var empr_id = $(this).data('empr-id'),
                menu_id = $(this).data('menu-id');
            if ( $(this).is(':checked') ) {
                $('#tab_'+empr_id).find('#'+menu_id).prop('disabled', false).attr('data-myrules', 'required');
            } else {
                $('#tab_'+empr_id).find('#'+menu_id).prop('disabled', true).val('').removeAttr('data-myrules');
            }
            usuariosJs.myValidate.reset();
        });

        $('.check_ad').on('change', function () {
            if ($(this).is(':checked')){
                $('input[name="senha"]').data( "myrules",null );
                $('input[name="senha"]').prop( "disabled", true );
                $(this).val('1');

               usuariosJs.myValidate.reset();
            }else{
                $('input[name="senha"]').data("myrules","required");
                $('input[name="senha"]').prop( "disabled", false );
                $(this).val('0');
            }
        });
    }
};


var uploadFiles = function () {
    $("#upload").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 10000,
        maxFilesNum: 1,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        },

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


$(document).ready(function(){
    usuariosJs.onReady();
});
