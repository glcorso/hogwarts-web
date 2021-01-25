valorServicoForm = {
    onReady: function() {
        carregaMascaraPreco();
        adicionaLinha();
        removeLinha();
        adicionaLinhaAgrupador();
    },
};

var carregaMascaraPreco = function () {
    $('table input.preco').maskMoney()
        .trigger('mask.maskMoney');
}

var adicionaLinha = function() {
    $(document).on('click','.btn-adicionar-linha', function() {
        var templateNovoPrecoPorServico = $('#novoPrecoPorServico').html(),
            table = $(this).closest('table'),
            trs = table.find('tr[data-index]')
                .map(function (k, tr) {
                    return parseInt(tr.dataset.index);
                }).toArray(),
            maxIndex = trs.length > 0
                ? trs.reduce(function(a, b) {
                    return Math.max(a, b);
                }) : -1,
            idxAgrup = $(this).data('idx-agrup');
        table.find('tbody.servicos').append(
            templateNovoPrecoPorServico.replace(/@index/gi, maxIndex+1).replace(/@idxAgrup/gi, idxAgrup)
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


var adicionaLinhaAgrupador = function() {
    $('.btn-adicionar-linha-agrupador').on('click', function() {
        var templateNovoPrecoPorServicoAgrupador = $('#novoPrecoAgrupador').html(),
            table = $(this).closest('table'),
            trs = table.find('tr[data-index]')
                .map(function (k, tr) {
                    return parseInt(tr.dataset.index);
                }).toArray(),
            maxIndex = trs.length > 0
                ? trs.reduce(function(a, b) {
                    return Math.max(a, b);
                }) : -1;

        table.find('tbody.agrupador').append(
            templateNovoPrecoPorServicoAgrupador.replace(/@index/gi, maxIndex+1).replace(/@idxLinha/gi, maxIndex+1)
        );
        table.find('input.preco').focus();
        table.find('input.preco')
             .maskMoney('unmasked');
        table.find('input.preco')
             .maskMoney();
    });
};


$(document).ready(function(){
    valorServicoForm.onReady();
});
