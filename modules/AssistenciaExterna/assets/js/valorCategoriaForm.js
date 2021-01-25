valorCategoriaJs = {
    onReady: function() {
        carregaMascaraPreco();
        adicionaLinha();
        removeLinha();
    },
};

var carregaMascaraPreco = function () {
    $('table input.preco').maskMoney()
        .trigger('mask.maskMoney');
}

var adicionaLinha = function() {
    $('.btn-adicionar-linha').on('click', function() {
        var templateNovoPrecoPorCategoria = $('#novoPrecoPorCategoria').html(),
            table = $(this).closest('table'),
            trs = table.find('tr[data-index]')
                .map(function (k, tr) {
                    return parseInt(tr.dataset.index);
                }).toArray(),
            maxIndex = trs.length > 0
                ? trs.reduce(function(a, b) {
                    return Math.max(a, b);
                }) : -1;
        table.find('tbody').append(
            templateNovoPrecoPorCategoria.replace(/@index/gi, maxIndex+1)
        );
        table.find('input.preco').focus();
        table.find('input.preco')
             .maskMoney('unmasked');
        table.find('input.preco')
             .maskMoney();
    });
};

var removeLinha = function() {
    $(document).on('click','.btn-remover-linha', function() {
        var tr = $(this).parent().parent();
        tr.remove();
    });
};

$(document).ready(function(){
    valorCategoriaJs.onReady();
});
