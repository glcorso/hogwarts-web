relatorioVisitasIndexJs = {
    onReady: function() {
    	adicionaSelect2Cliente();
    	validaHorarios();
        validaImpressao();
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


var adicionaSelect2Cliente = function() {

    $('#select-cliente').select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite a descrição para pesquisar',
        ajax: {
            url: '/comercial/ajax/retorna-select2-clientes-consulta',
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
        templateResult: relatorioVisitasIndexJs.formatRepo,
        templateSelection: relatorioVisitasIndexJs.formatRepoSelection

    });

};

var validaHorarios = function() {
    if(setor != 'admin'){
        var dt = new Date();
        var time = ("0"+dt.getHours()).slice(-2)  + ":" + ("0"+dt.getMinutes()).slice(-2) + ":" + ("0"+dt.getSeconds()).slice(-2);

        var startDate = new Date("1/1/1900 " + hora_ini);
        var endDate = new Date("1/1/1900 " + hora_fim);
        var horaAtual = new Date("1/1/1900 " + time);

        if (horaAtual >= startDate && horaAtual <= endDate){
           $('#btn-adicionar').show();
        }
        else{
           $('#btn-adicionar').remove();
        }
    }
};

var validaImpressao = function (){

    $('#check-sel-all-print').click(function(event) { 
        var values = [];  
        if(this.checked) { 
            $('.btn-imprimir').show();
            // Iterate each checkbox
            $('.check:checkbox').each(function() {
                this.checked = true;            
                values.push($(this).val());            
            });
            $('.btn-imprimir').attr('href','/comercial/relatorio-visitas/imprimir/'+values.join('-'));
        } else {
            $('.check:checkbox').each(function() {
                this.checked = false;  
                $('.btn-imprimir').attr('href','#');                     
            });
            $('.btn-imprimir').hide();

        }

    });

    $('.check').click(function(event) {   
        var count =  $('.check:checked').length;
        if(this.checked) { 
            $('.btn-imprimir').show();
           
        }else{
            if(count == 0){
                $('.btn-imprimir').hide();
                $('.btn-imprimir').attr('href','#');
            }
        }

        var values = $('.check:checked').map(function() {
                    return $(this).val();
                }).toArray();

        $('.btn-imprimir').attr('href','/comercial/relatorio-visitas/imprimir/'+values.join('-'));
    });

    //MONTADORAS

    $('#check-sel-all-print-montadoras').click(function(event) { 
        var values = [];  
        if(this.checked) { 
            $('.btn-imprimir-montadoras').show();
            // Iterate each checkbox
            $('.check-montadoras:checkbox').each(function() {
                this.checked = true;            
                values.push($(this).val());            
            });
            $('.btn-imprimir-montadoras').attr('href','/comercial-montadoras/relatorio-visitas-montadoras/imprimir/'+values.join('-'));
        } else {
            $('.check-montadoras:checkbox').each(function() {
                this.checked = false;  
                $('.btn-imprimir-montadoras').attr('href','#');                     
            });
            $('.btn-imprimir-montadoras').hide();

        }

    });

    $('.check-montadoras').click(function(event) {   
        var count =  $('.check-montadoras:checked').length;
        if(this.checked) { 
            $('.btn-imprimir-montadoras').show();
           
        }else{
            if(count == 0){
                $('.btn-imprimir-montadoras').hide();
                $('.btn-imprimir-montadoras').attr('href','#');
            }
        }

        var values = $('.check-montadoras:checked').map(function() {
                    return $(this).val();
                }).toArray();

        $('.btn-imprimir-montadoras').attr('href','/comercial-montadoras/relatorio-visitas-montadoras/imprimir/'+values.join('-'));
    });
};

$(document).ready(function(){
    relatorioVisitasIndexJs.onReady();
});