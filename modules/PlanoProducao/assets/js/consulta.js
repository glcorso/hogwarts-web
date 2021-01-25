consultaJs = {
    onReady: function() {
    	adicionaAcoes();
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

var adicionaAcoes = function () {

    setTimeout(function() {
        window.location.reload()
    }, 300000);

    setTimeout(function() {
        $('.sidebartoggler').trigger('click');
    }, 30);

    $('.link-abre-demandas').on('click', function(){ 
        if (!isDoubleClicked($(this))){
            var ordem_id = $(this).data('ordem-id'),
                num_ordem = $(this).data('num-ordem');
            abreModalDemandas(ordem_id, num_ordem);
        }
    });

};


var abreModalDemandas = function(ordem_id, num_ordem){


    html = '<div style="font-size:11px;"><table class="table table-bordered"> \
            <thead> \
              <tr>\
               <th width="10%" class="cab">Item</th>\
               <th width="60%" class="cab">Descrição</th>\
               <th width="20%" class="cab">Almoxarifado</th>\
               <th width="5%" class="cab">Unid.</th>\
               <th width="5%" class="cab">Qtde.</th>\
               </tr>\
            </thead> \
            <tbody>';


    $.ajax({
        type: 'POST',
        url: '/plano-producao/ajax/retorna-demandas-ordem',
        data: { 'ordem_id': ordem_id },
        dataType: 'json',
        success: function(data, textStatus, jqXHR) {
            if (!data.error) {
                   
                $.each(data.demandas, function(i,v){
                    html += '<tr><td class="pd-nw">'+v['cod_item']+'</td>\
                             <td class="pd-nw">'+v['desc_tecnica']+'</td>\
                             <td class="pd-nw">'+v['cod_almox']+'-'+v['descricao']+'</td>\
                             <td class="pd-nw text-center">'+v['cod_unid_med']+'</td>\
                             <td class="pd-nw text-center">'+v['qtde']+'</td>\
                             </tr>';
                });
                html += "</tbody></table></div>";

                $.alert({
                    title: 'Demandas Ordem: '+num_ordem,
                    content: html,
                    columnClass: "col-md-12",
                    buttons: {
                        Sair: function(){

                        },

                    },
                });

            }else{
                $.alert({
                    title: 'Opsss!',
                    content: 'Não foi possível realizar a consulta! Tente novamente mais tarde!',
                    buttons: {
                        Sair: function(){

                        },

                    },
                });
            }
        }
    });
        
};


var isDoubleClicked = function (element) {
    
    if (element.data("isclicked")) return true;

    element.data("isclicked", true);
    setTimeout(function () {
        element.removeData("isclicked");
    }, 1000);

    return false;
}


$(document).ready(function(){
    consultaJs.onReady();
});