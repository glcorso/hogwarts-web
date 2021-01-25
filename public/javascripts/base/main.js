mainJs = {
    onReady: function() {
        /**
         * Ativar/Desativar o console.log
         */
        if (/localhost/.test(window.location.host) || /192\.168\.1/.test(window.location.host) || /.dev/.test(window.location.host)) {
            // window.localStorage.debug = true;
        } else {
            // delete window.localStorage.debug;
        }
        window.localStorage.debug = true;
        console.log('mainJs');
        $('.troca-empresa').on('change', function(){
            $.ajax({
                type: 'POST',
                url: '/ajax/altera-empresa-padrao',
                data: {'id': $(this).val()},
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if ( data.error == false ) {
                        document.location.reload();
                    }
                }
            });
        });

        if ( $('.form').length ) {
            $('.form').myValidate();
        }

        $('.btn-delete-modal').on('click', function () {
            var element = $(this);
            mainJs.prepareModalDelete(element);
        });

        $('.btn-submit-modal').on('click', function(){
            var btn = $(this);
            btn.button('loading');
            $(this).parents('form').submit();
        });

        $('.btn-limpar-filtro').on('click', function(){
            $('.form-filtro').find('input:text, input:password, input:file, select, textarea').val('');
        });

        $('select.paginacao').on('change', function(){
            var url = $(this).val();
            document.location.href = url;
        });

        $('.btn-change-password').on('click', function(){
            var element = $(this);
            mainJs.prepareModalPassword(element);
        });

        $('.btn-submit-modal-password').on('click', function(e){
            var btn = $(this);
            btn.button('loading');
            $.ajax({
                type: 'POST',
                url: '/ajax/altera-senha-usuario',
                data: $('.form-password').serialize(),
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if ( data.error ) {
                        $('.notification').addClass('error').removeClass('success');
                        $('.btn-cancel').html('Cancelar');
                        $('.btn-submit-modal-password').removeClass('hidden');
                    } else {
                        $('.btn-cancel').html('Fechar');
                        $('.btn-submit-modal-password').addClass('hidden');
                        $('.notification').addClass('success').removeClass('error');
                    }
                    $('.notification').html(data.message);
                    btn.button('reset');
                }
            });
        });

    },

    prepareModalDelete: function(element){
        var id = element.data('id'),
            name = element.data('name'),
            boxModal = $('body').find('.modal-delete');

        boxModal.find('input[name="id"]').val(id);
        boxModal.find('[name="name"]').val(name);
        boxModal.find('.modal-body strong').html(name);
        boxModal.modal('toggle');
    },

    prepareModalPassword: function(element){
        var boxModal = $('body').find('.modal-password');
        boxModal.modal('toggle');
    },
    initSelect2: function(options) {
        $.fn.select2.amd.require([
            'select2/core',
            'select2/utils',
            'select2/compat/matcher'
        ], function(Select2, Utils, oldMatcher) {
            var $select = $(options.el),
                minimumInputLength,
                escapeMarkup,
                templateResult,
                templateSelection;

            escapeMarkup = options.escapeMarkup || function(markup) {
                return markup;
            };

            minimumInputLength = options.minimumInputLength || 3;

            templateResult = options.templateResult || function(repo) {
                if (repo.loading) return repo.text;
                var text = '';
                if (repo.cod) {
                    text = repo.cod + (repo.desc ? ' - ' + repo.desc : '');
                }
                var markup = '<div class="clearfix">' +
                    '<div class="col-sm-12">' + text + '</div>' +
                    '</div>';
                return markup;
            };

            templateSelection = options.templateSelection || function(repo) {
                return repo.cod ? repo.cod + (repo.desc ? ' - ' + repo.desc : '') : repo.text;
            };

            $select.select2({
                tags: options.tags || false,
                language: 'pt-BR',
                placeholder: options.placeholder,
                width: options.width || '100%',
                ajax: {
                    url: options.url,
                    dataType: 'json',
                    type: 'GET',
                    delay: 250,
                    data: function(params) {
                        return $.extend({}, {
                            q: params.term,
                            page: params.page
                        }, options.data);
                    },
                    processResults: options.processResults || function(data, page) {
                        return {
                            results: data.items
                        };
                    },
                    cache: true
                },
                escapeMarkup: escapeMarkup,
                minimumInputLength: options.minimumInputLength,
                templateResult: templateResult,
                templateSelection: templateSelection
            });

            if (options.events) {
                $.each(options.events, function(k, event) {
                    var key = Object.keys(event).join(),
                        callback = event[key];
                    $select.on(event, callback);
                });
            }

            if (options.init) {
                options.init($select);
            }
        });
    }
};

$(document).ready(function(){
    mainJs.onReady();
});
