estruturaProduto = {
    onReady: function() {

    	adicionaSelect2Item();


        $('.btn-filtrar').on('click', function(){
            $('.form-filtro').attr('action', '/relatorios/estrutura-produto').removeAttr('target').submit();
        });

        $('.btn-imprimir').on('click', function(){
            $('.form-filtro').attr('action', '/relatorios/estrutura-produto/imprimir').attr('target', '_blank').submit();
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


var adicionaSelect2Item = function() {
    $('#select-item').select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/relatorios/ajax/retorna-itens-pn',
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
        templateResult:estruturaProduto.formatRepo,
        templateSelection:estruturaProduto.formatRepoSelection

    }).on('select2:select', function (e) {

        if(e.params.data.mascara_id != null){
            $('#mascara_id').val(e.params.data.mascara_id);
        }

    });

};



$(document).ready(function(){
   estruturaProduto.onReady();
});