var companiesJs = {

    onReady: function(){

        $('.form-companies').myValidate();

        $('.btn-submit-filter').on('click', function(){
            $('.form-search').submit();
        });

        if ($('input[name="cnpj"]').length > 0) {
            $('input[name="cnpj"]').mask('99.999.999/9999-99', {placeholder:" "});
            $('input[name="cnpj"]').parent('.form-group').css('display','none');
        }
        if ($('input[name="cpf"]').length > 0) {
            $('input[name="cpf"]').mask('999.999.999-99', {placeholder:" "});
            $('input[name="cpf"]').parent('.form-group').css('display','none');
        }
        if ($('input[name="zipcode"]').length > 0) {
            $('input[name="zipcode"]').mask('99999-999', {placeholder:" "});
        }
        if ($('input[name="value"]').length > 0) {
            $('input[name="value"]').maskMoney({thousands:'.', decimal:',', affixesStay: false, allowZero: true});
        }
        if ($('input[name="tax_percentage"]').length > 0) {
            $('input[name="tax_percentage"]').maskMoney({thousands:'.', decimal:',', affixesStay: false, allowZero: true});
        }

        $('select[name="type"]').on('change', function(){
            if ( $(this).val() == 'PF' ) {
                $('input[name="cnpj"]').parent('.form-group').css('display','none');
                $('input[name="cpf"]').parent('.form-group').css('display','');
            } else {
                $('input[name="cpf"]').parent('.form-group').css('display','none');
                $('input[name="cnpj"]').parent('.form-group').css('display','');
            }
        });

        if ( $('select[name="type"]').val() == 'PF' ) {
            $('input[name="cpf"]').parent('.form-group').css('display','');
        } else {
            $('input[name="cnpj"]').parent('.form-group').css('display','');
        }

        $('.btn-update-company-hour').on('click', function(e){
            e.preventDefault();

            var form = $('#form_'+$(this).data('id'));

            $.ajax({
                type: 'POST',
                url: '/companies/hour/edit',
                data: form.serialize(),
                dataType: 'json',
                success: function (data, textStatus, jqXHR){
                    //form.find('.notification').html(data.message);
                },
            });

        });

        $('.show-hidden-hour').on('click', function(){
            $('.hour-value').toggleClass('hidden');
        });

    },

};

$(document).ready(function(){
    companiesJs.onReady();
});
