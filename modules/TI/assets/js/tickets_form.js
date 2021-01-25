var ticketsFormJs = {

    onReady: function() {


        /** FAZ A CONSULTA DOS USUÁRIOS DO CLIENTE PARA PREENCHER O CAMPO SOLICITANTE **/
        $('select[name="project_id"]').on('change', function(){
            var project_id = $(this).val();
            if ( project_id != '' ) {
                $('.user-id').attr('disabled', false);
                ticketsFormJs.getCompanyUsers(project_id);
            } else {
                $('.user-id').attr('disabled', true);
            }
        });

        /** FAZ O TRATAMENTO DE ATENDIMENTOS COM VALOR FECHADO **/
        $('.form-edit-called .closed-value').on('change', function(){
            if ( $(this).is(':checked') ) {
                //desativa as horas
                $('.form-edit-called .modal-body .form-group input[name="hour_start"]').val('00:00').prop('disabled', true);
                $('.form-edit-called .modal-body .form-group input[name="hour_finish"]').val('00:00').prop('disabled', true);
                $('.group-closed-value').removeClass('hidden');
            } else {
                // ativa as horas
                $('.form-edit-called .modal-body .form-group input[name="hour_start"]').removeAttr('disabled').val('');
                $('.form-edit-called .modal-body .form-group input[name="hour_finish"]').removeAttr('disabled').val('');
                $('.group-closed-value').addClass('hidden').val('');
            }
        });

        $('input[name="date"]').mask('99/99/9999', {placeholder:" "});
        $('input[name="hour_start"]').mask('99:99', {placeholder:" "});
        $('input[name="hour_finish"]').mask('99:99', {placeholder:" "});
        $('input[name="value"]').maskMoney({thousands:'.', decimal:',', affixesStay: false, allowZero: true});

        $('.form-ticket-file').myValidate();

        $('.btn-edit-modal').on('click', function () {
            var element = $(this);
            ticketsFormJs.prepareModalEdit(element);
        });

        $('.btn-delete-modal').on('click', function () {
            var element = $(this);
            ticketsFormJs.prepareModalDelete(element);
        });

        $('.btn-add-expense').on('click', function () {
            var element = $(this);
            ticketsFormJs.prepareModalExpense(element);
        });

        $('.btn-edit-expense').on('click', function () {
            var element = $(this);
            ticketsFormJs.prepareModalEditExpense(element);
        });

        $('.btn-delete-expense').on('click', function () {
            var element = $(this);
            ticketsFormJs.prepareModalDeleteExpense(element);
        });

        $('.btn-add-file').on('click', function () {
            var element = $(this);
            ticketsFormJs.prepareModalFile(element);
        });

        $('.btn-delete-file-modal').on('click', function () {
            var element = $(this);
            ticketsFormJs.prepareModalDeleteFile(element);
        });

        $('.btn-submit-form-modal').on('click', function(){
            $('.form-delete').submit();
        });

        $('.btn-edit-called-submit').on('click', function(){ // processa o form
            $(this).parents('form').submit();
        });

        $('.btn-expense-submit').on('click', function(){ // processa o form
            $(this).parents('form').submit();
        });

        $('.btn-file-submit').on('click', function(){ // processa o form
            $(this).parents('form').submit();
        });

        $('.btn-submit-delete-expense').on('click', function(){
            $('.form-delete-expense').submit();
        });

        $('.btn-submit-delete-file').on('click', function(){
            $('.form-delete-file').submit();
        });

        /** VALIDA O FORMULÁRIO DE INCLUSÃO DE ATENDIMENTO E PROCESSA O FORM **/
        $('.form-edit-called').myValidate({
            callSuccess:function(){

                var btn = $('.btn-edit-called-submit');
                btn.button('loading');

                $.ajax({
                    type: 'POST',
                    url: '/ti/tickets/update/called',
                    data: $('.form-edit-called').serialize(),
                    dataType: 'json',
                    success: function (data, textStatus, jqXHR) {

                        if(data.error){
                            $('.form-edit-called').find('.success').html('');
                            $('.form-edit-called').find('.notification').html(data.message).css('display','inline');
                        } else {
                            $('.form-edit-called').find('.notification').html('').css('display','none');
                            $('.form-edit-called').find('.success').html(data.message);

                            $('body').find('.modal-edit-called').on('hide.bs.modal', function (e) {
                                location.reload();
                            });

                            setTimeout(function(){
                                $('.modal-edit-called').modal('toggle');
                            }, 3000);
                        }
                        btn.button('reset');

                    }
                });

            }
        });

        /** VALIDA O FORMULÁRIO DE INCLUSÃO/EDIÇÃO DE DESPESA E PROCESSA O FORM **/
        $('.form-expense').myValidate({
            callSuccess:function(){

                var btn = $('.btn-expense-submit');
                btn.button('loading');

                $.ajax({
                    type: 'POST',
                    url: '/ti/tickets/add/expense',
                    data: $('.form-expense').serialize(),
                    dataType: 'json',
                    success: function (data, textStatus, jqXHR) {

                        if(data.error){
                            $('.form-expense').find('.success').html('');
                            $('.form-expense').find('.notification').html(data.message).css('display','inline');
                        } else {
                            $('.form-expense').find('.notification').html('').css('display','none');
                            $('.form-expense').find('.success').html(data.message);

                            $('body').find('.modal-expense').on('hide.bs.modal', function (e) {
                                location.reload();
                            });

                            setTimeout(function(){
                                $('.modal-expense').modal('toggle');
                            }, 3000);
                        }
                        btn.button('reset');

                    }
                });

            }
        });

        $('.chk-print-item').on('click', function(){
            var n = $('.chk-print-item:checked').length;
            $('.btn-print-calleds').css( 'display', (n + n >= 1 ? '' : 'none') );
        });

        $('.btn-print-calleds').on('click', function(){
            $('.form-print').submit();
        });

        $('.chk-expense-item').on('click', function(){
            var n = $('.chk-expense-item:checked').length;
            $('.btn-print-expense').css( 'display', (n + n >= 1 ? '' : 'none') );
        });

        $('.btn-print-expense').on('click', function(){
            $('.form-print-expense').submit();
        });

        $('body').on('change', '.expense-id', function(){
            var element = $(this),
                default_value = $(this).find('option:selected').attr('data-default-value');
            if ( default_value != '' ) {
                $('.form-expense').find('input[name="value"]').val(default_value);
            }
        });

    },

    prepareModalEdit: function(element){
        var id = element.data('id'),
            ticket_id = element.data('ticket-id'),
            date = element.data('date'),
            hour_start = element.data('hour-start'),
            hour_finish = element.data('hour-finish'),
            description = element.data('description'),
            hour_id = element.data('hour-id'),
            closed_value = element.data('closed-value'),
            hour_value = element.data('hour-value'),
            status = element.data('status'),
            user_id = element.data('user-id'),
            add_to_billing_report = element.data('add-to-billing-report'),
            billing = element.data('billing'),
            goal = element.data('goal'),
            boxModalEdit = $('body').find('.modal-edit-called');

        if ( status == 'FATURADO' ) {
            /* insere a opção faturado no select */
            var html_option = '<option value="FATURADO">Faturado</option>'
            boxModalEdit.find('select[name="status"]').append(html_option);
            /* esconde o botão */
            boxModalEdit.find('.btn-edit-called-submit').addClass('hidden');
            boxModalEdit.find('.success').html('Atendimento faturado não permite alteração.');
        } else {
            /* insere a opção faturado no select */
            if ( boxModalEdit.find('select[name="status"] option:last').val() == 'FATURADO' ) {
                boxModalEdit.find('select[name="status"] option:last').remove();
            }
            boxModalEdit.find('.btn-edit-called-submit').removeClass('hidden');
            boxModalEdit.find('.success').html('');
        }

        boxModalEdit.find('input[name="ticket_id"]').val(ticket_id);
        boxModalEdit.find('input[name="id"]').val(id);
        boxModalEdit.find('input[name="date"]').val(date);
        boxModalEdit.find('input[name="hour_start"]').val(hour_start);
        boxModalEdit.find('input[name="hour_finish"]').val(hour_finish);
        boxModalEdit.find('select[name="hour_id"]').val(hour_id);
        boxModalEdit.find('select[name="status"]').val(status);
        boxModalEdit.find('select[name="user_id"]').val(user_id);
        boxModalEdit.find('textarea[name="description"]').val(description);
        if ( closed_value == 'ON' ) {
            boxModalEdit.find('input[name="closed_value"]').prop('checked', true);
            $('.group-closed-value').removeClass('hidden');
            $('input[name="hour_value"]').val(hour_value);
            $('.form-edit-called .modal-body .form-group input[name="hour_start"]').prop('disabled', true);
            $('.form-edit-called .modal-body .form-group input[name="hour_finish"]').prop('disabled', true);
        } else {
            boxModalEdit.find('input[name="closed_value"]').prop('checked', false);
            $('.group-closed-value').addClass('hidden');
            $('input[name="hour_value"]').val('');
        }

        if (add_to_billing_report == 'ON') {
            boxModalEdit.find('input:checkbox[name="add_to_billing_report"]').prop('checked', true);
        } else {
            boxModalEdit.find('input:checkbox[name="add_to_billing_report"]').prop('checked', false);
        }

        var element = $("input:checkbox[name=add_to_billing_report].add_to_billing_report");
        var elementHidden = $("input:hidden[name=add_to_billing_report]");

        if (goal == 'ON' && billing == 'ON') {
            elementHidden.attr("disabled", false);
            element.attr("disabled", true);
        } else {
            elementHidden.attr("disabled", true);
            element.attr("disabled", false);
        }

        boxModalEdit.modal('toggle');
    },

    prepareModalDelete: function(element){
        var id = element.data('id'),
            boxModalDelete = $('body').find('.modal-delete-called');
        boxModalDelete.find('input[name="id"]').val(id);
        boxModalDelete.modal('toggle');
    },

    prepareModalExpense: function(element){
        var ticket_id = element.data('ticket-id')
            boxModalExpense = $('body').find('.modal-expense');

        boxModalExpense.find('input[name="ticket_id"]').val(ticket_id);
        boxModalExpense.find('.modal-body strong').html(ticket_id);
        boxModalExpense.modal('toggle');
        boxModalExpense.on('shown.bs.modal', function() {
            boxModalExpense.find('input[name="date"]').focus();
        });
    },

    prepareModalFile: function(element){
        var ticket_id = element.data('ticket-id')
            boxModalFile = $('body').find('.modal-file');

        boxModalFile.find('input[name="ticket_id"]').val(ticket_id);
        boxModalFile.find('.modal-body strong').html(ticket_id);
        boxModalFile.modal('toggle');
    },

    prepareModalEditExpense: function(element){
        var id = element.data('id'),
            ticket_id = element.data('ticket-id'),
            date = element.data('date'),
            expense_id = element.data('expense-id'),
            description = element.data('description'),
            value = element.data('value'),
            company_billing = element.data('company-billing'),
            boxModalEditExpense = $('body').find('.modal-expense');

        boxModalEditExpense.find('input[name="id"]').val(id);
        boxModalEditExpense.find('input[name="ticket_id"]').val(ticket_id);
        boxModalEditExpense.find('input[name="date"]').val(date);
        boxModalEditExpense.find('select[name="expense_id"]').val(expense_id);
        boxModalEditExpense.find('textarea[name="description"]').val(description);
        boxModalEditExpense.find('input[name="value"]').val(value);
        if ( company_billing == 'ON' ) {
            boxModalEditExpense.find('input[name="company_billing"]').prop('checked', true);
        } else {
            boxModalEditExpense.find('input[name="company_billing"]').prop('checked', false);
        }
        boxModalEditExpense.modal('toggle');
        boxModalEditExpense.on('shown.bs.modal', function() {
            boxModalEditExpense.find('input[name="date"]').focus();
        });
    },

    prepareModalDeleteExpense: function(element){
        var id = element.data('id'),
            boxModalDeleteExpense = $('body').find('.modal-delete-expense');
        boxModalDeleteExpense.find('input[name="id"]').val(id);
        boxModalDeleteExpense.modal('toggle');
    },

    prepareModalDeleteFile: function(element){
        var id = element.data('id'),
            ticket_id = element.data('ticket-id'),
            name = element.data('name'),
            boxModalDeleteFile = $('body').find('.modal-delete-file');
        boxModalDeleteFile.find('.modal-body strong').html(name);
        boxModalDeleteFile.find('input[name="id"]').val(id);
        boxModalDeleteFile.modal('toggle');
    },

    getCompanyUsers: function(project_id){
        $.ajax({
            type: 'POST',
            url: '/ti/tickets/get/users',
            data: {'project_id':project_id},
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                if(data.error){
                    console.log(data.error);
                } else {
                    var options = '<option></option>';
                    $.each(data.content, function(i,v){
                        options += '<option value="'+v.id+'">'+v.name+'</option>'
                    });
                    $('.user-id').html(options);
                }
            }
        });
    }
    

};

$(document).ready(function(){
    ticketsFormJs.onReady();
});

$(function(){
    $('.redactor-ticket').redactor({
        buttons: ['bold', 'italic', 'deleted', 'unorderedlist', 'orderedlist', 'outdent', 'indent', 'horizontalrule'],
        minHeight: 240,
    });
});