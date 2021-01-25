var mainChamadosJs = {

    onReady: function() {

        if ($('input[name="hour_value"]').length) {
            $('input[name="hour_value"]').maskMoney({ thousands: '.', decimal: ',', affixesStay: false, allowZero: true });
        }

        /** FAZ O TRATAMENTO DE ATENDIMENTOS COM VALOR FECHADO **/
        $('.form-add-called .closed-value').on('change', function() {
            if ($(this).is(':checked')) {
                //desativa as horas
                $('.form-add-called .modal-body .form-group input[name="hour_start"]').val('00:00').prop('disabled', true);
                $('.form-add-called .modal-body .form-group input[name="hour_finish"]').val('00:00').prop('disabled', true);
                $('.group-closed-value').removeClass('hidden');
            } else {
                // ativa as horas
                $('.form-add-called .modal-body .form-group input[name="hour_start"]').removeAttr('disabled').val('');
                $('.form-add-called .modal-body .form-group input[name="hour_finish"]').removeAttr('disabled').val('');
                $('.group-closed-value').addClass('hidden').val('');
            }
        });

        /** ADICIONA O MYVALIDATE NOS FORMS GENÉRICOS **/
        if ($('.main-form').length) {
            $('.main-form').myValidate();
        }

        /** PROCESSA O FILTRO DA LISTAGEM **/
        /*
        if ( $('.btn-submit-filter').length ) {
            $('.btn-submit-filter').on('click', function(){
                $(this).parents('form').submit();
            });
        }
        */

        if ($('input[name=date]').length > 0) {
            if (typeof $.fn.datepicker == 'function' ) {
                $('input[name=date]').datepicker({
                    format: 'dd/mm/yyyy',
                    language: 'pt-BR',
                    autoclose: true
                });
            }
        }

        if ($('input[name=hour_start]').length > 0) {
            if (typeof $.fn.clockpicker == 'function' ) {
                $('input[name=hour_start]').clockpicker({
                    autoclose: true
                });
            }
        }

        if ($('input[name=hour_finish]').length > 0) {
            if (typeof $.fn.clockpicker == 'function' ) {
                $('input[name=hour_finish]').clockpicker({
                    autoclose: true
                });
            }
        }

        /** MODAL DE EXCLUSÃO **/
        $('.btn-delete-modal-ch').on('click', function() { // abre a modal
            var element = $(this);
            mainChamadosJs.prepareModalDelete(element);
        });
        $('.btn-submit-form-modal').on('click', function() { // processa o form
            var btn = $(this);
            btn.button('loading');
            $(this).parents('form').submit();
        });

        /** MODAL DE ALTERAÇÃO DE SENHA **/
        $('.btn-open-modal-password').on('click', function() { // abre a modal
            var element = $(this);
            mainChamadosJs.prepareModalPassword(element);
        });

        $('.btn-update-password-submit').on('click', function() { // processa o form
            $(this).parents('form').submit();
        });

        /** MODAL DE INCLUSÃO DE ATENDIMENTO NO CHAMADO **/
        $('.btn-add-called').on('click', function() { // abre a modal
            var element = $(this);
            mainChamadosJs.prepareModalCalled(element);
        });

        $('.btn-add-called-submit').on('click', function() { // processa o form
            $(this).parents('form').submit();
        });

        $('select[name=hour_id]').on('change', function() {
            var selected = $(this).find(":selected");
            var element = $("input:checkbox[name=add_to_billing_report]");
            var elementHidden = $("input:hidden[name=add_to_billing_report]");

            if (selected.data("goal") == 'ON' && selected.data("billing") == 'ON') {

                element.prop('checked', true);
                element.attr("disabled", true);

                elementHidden.attr("disabled", false);
            } else {
                element.attr("disabled", false);
                elementHidden.attr("disabled", true);
            }

        });

        /** VALIDA O FORMULÁRIO DE TROCA DE SENHA E PROCESSA O FORM **/
        $('.form-update-password').myValidate({
            callSuccess: function() {

                var btn = $('.btn-update-password-submit');
                btn.button('loading');

                $.ajax({
                    type: 'POST',
                    url: '/users/update-password',
                    data: $('.form-update-password').serialize(),
                    dataType: 'json',
                    success: function(data, textStatus, jqXHR) {
                        if (data.error) {
                            $('.form-update-password').find('.success').html('');
                            $('.form-update-password').find('.notification').html(data.message).css('display', 'inline');
                        } else {
                            $('.form-update-password').find('.notification').html('').css('display', 'none');
                            $('.form-update-password').find('.success').html(data.message);
                            setTimeout(function() {
                                $('.modal-update-password').modal('toggle');
                            }, 3000);
                        }
                        btn.button('reset');
                    },
                });

            }
        });

        /** VALIDA O FORMULÁRIO DE INCLUSÃO DE ATENDIMENTO E PROCESSA O FORM **/
        $('.form-add-called').myValidate({
            callSuccess: function() {

                var btn = $('.btn-add-called-submit');
                btn.button('loading');

                $.ajax({
                    type: 'POST',
                    url: '/ti/tickets/add/called',
                    data: $('.form-add-called').serialize(),
                    dataType: 'json',
                    success: function(data, textStatus, jqXHR) {
                        if (data.error) {
                            $('.form-add-called').find('.success').html('');
                            $('.form-add-called').find('.notification').html(data.message).css('display', 'inline');
                        } else {
                            $('.form-add-called').find('.notification').html('').css('display', 'none');
                            $('.form-add-called').find('.success').html(data.message);

                            $('body').find('.modal-add-called').on('hide.bs.modal', function(e) {
                                location.reload();
                            });

                            setTimeout(function() {
                                $('.modal-add-called').modal('toggle');
                            }, 3000);
                        }
                        btn.button('reset');
                    }
                });

            }
        });

    },

    prepareModalDelete: function(element) {
        var id = element.data('id'),
            name = element.data('name')
        boxModalDelete = $('body').find('.modal-delete');

        boxModalDelete.find('input[name="id"]').val(id);
        boxModalDelete.find('.modal-body strong').html(name);
        boxModalDelete.modal('toggle');
    },

    prepareModalPassword: function(element) {
        var boxModalPassword = $('body').find('.modal-update-password');
        boxModalPassword.find('input').removeClass('error');
        boxModalPassword.find('.notification').html('');
        boxModalPassword.modal('toggle');
        boxModalPassword.on('shown.bs.modal', function() {
            boxModalPassword.find('input').focus();
        });
    },

    prepareModalCalled: function(element) {
        var id = element.data('id'),
            boxModalCalled = $('body').find('.modal-add-called');

        boxModalCalled.find('input[name="ticket_id"]').val(id);
        boxModalCalled.find('.modal-body h4 strong').html(id);
        boxModalCalled.modal('toggle');
        boxModalCalled.on('shown.bs.modal', function() {
            boxModalCalled.find('input[name="date"]').focus();
        });
    }

};

$(document).ready(function() {
    mainChamadosJs.onReady();
});
