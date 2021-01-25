agrupadoresForm = {
    onReady: function() {
        adicionaLinha();
        removeLinha();
        if(vid != ''){
            adicionaSelectItensTela();
        }else{
            adicionaSelect2Item('select-item-0');   
        }
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

var adicionaLinha = function() {
    $('.btn-adicionar-linha').on('click', function() {
        var templateNovoItem = $('#novoItem').html(),
            table = $(this).closest('table'),
            trs = table.find('tr[data-index]')
                .map(function (k, tr) {
                    return parseInt(tr.dataset.index);
                }).toArray(),
            maxIndex = trs.length > 0
                ? trs.reduce(function(a, b) {
                    return Math.max(a, b);
                }) : -1,
            idx = maxIndex+1;

        table.find('tbody').append(
            templateNovoItem.replace(/@index/gi, idx)
        );
        adicionaSelect2Item('select-item-'+idx);
    });
};

var removeLinha = function() {
    $(document).on('click','.btn-remover-linha', function() {
        var tr = $(this).parent().parent();
        tr.remove();
    });
};

var adicionaSelect2Item = function(id) {
    $('#'+id).select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/assistencia-externa/itens',
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
        templateResult: agrupadoresForm.formatRepo,
        templateSelection: agrupadoresForm.formatRepoSelection

    });

};


var adicionaSelectItensTela =  function(){

    var el = $("select[id^='select-item']");
    el.each(function(){ adicionaSelect2Item(this.id); });
};


$(document).ready(function(){
    agrupadoresForm.onReady();
});
