var ticketsJs = {

    onReady: function() {

        $('.date_start').mask('99/99/9999', { placeholder: " " });
        $('.date_end').mask('99/99/9999', { placeholder: " " });

        $('input[name="date"]').mask('99/99/9999', { placeholder: " " });
        $('input[name="hour_start"]').mask('99:99', { placeholder: " " });
        $('input[name="hour_finish"]').mask('99:99', { placeholder: " " });

        $('.btn-clean-period').on('click', function() {
            $('.date_start').val('');
            $('.date_end').val('');
        });

        $('.btn-billing-modal').on('click', function() { // abre a modal
            var element = $(this);
            ticketsJs.prepareModalBilling(element);
        });
        $('.btn-submit-form-modal-billing').on('click', function() { // processa o form

            var btn = $(this),
                id = btn.parents('form').find('input[name="id"]').val(),
                num_nf = btn.parents('form').find('input[name="num_nf"]').val(),
                div = $('.div-billing-' + id),
                boxModal = $('body').find('.modal-billing');

            btn.button('loading');

            $.ajax({
                type: 'POST',
                url: '/billing/billing',
                data: { 'id': id, 'num_nf': num_nf },
                dataType: 'json',
                success: function(data, textStatus, jqXHR) {
                    if (data.error == false) {
                        div.css('display', 'none');
                        $('.billed-' + id).html('<span class="label label-success">Sim</span>');
                        boxModal.modal('toggle');
                        btn.button('reset');
                        location.reload();
                    }
                }
            });
        });

    },

    prepareModalBilling: function(element) {
        var id = element.data('id'),
            financial_details = element.data('financial-details'),
            boxModal = $('body').find('.modal-billing');

        if (financial_details != '') {
            financial_details = 'Detalhes Financeiros: ' + financial_details;
            boxModal.find('.financial-details').html(financial_details);
        }

        boxModal.find('input[name="id"]').val(id);
        boxModal.find('.modal-body strong').html(id);
        boxModal.modal('toggle');
    },

};

$(document).ready(function() {
    ticketsJs.onReady();
});
