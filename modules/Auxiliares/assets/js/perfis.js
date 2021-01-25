var perfisJs = {

    myValidate: null,

    onReady: function() {

        if ($('.form-perfil').length > 0) {
            perfisJs.myValidate = $('.form-perfil').myValidate({
            instance: true,
            removeData: true,
            callError: function() {
                $('.btn-submit').button('reset');
            },
            callSuccess: function() {
                $('.btn-submit').button('reset');
            }
           });
        }

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
            perfisJs.myValidate.reset();
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
            perfisJs.myValidate.reset();
        });
    }
};

$(document).ready(function(){
    perfisJs.onReady();
});