rastreamentoGarantiasJs = {
    onReady: function() {
    	adicionaAnexos();
    	botoes();
   	},
};

var adicionaAnexos = function() {

    $('.uploadArquivos').fileinput({
        language: 'pt-BR',
        uploadUrl: '#',
        overwriteInitial: false,
        maxFileSize: 10000,
        maxFilesNum: 20,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-zoom').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        },

    });


    $(document).on('click','.btn-file', function() {
        $(document).find('.fileinput-remove').trigger('click');
    });

};


var botoes = function() {

	$(document).on('click', '.btn-acoes', function() {
		var id = $(this).data('id'),
		    acao = $(this).data('acao'),
		    chave_acesso = $(this).data('chave'),
		    dia = $(this).data('dia');

		if(acao == 'S'){
			var titulo =  'Informar Solicitação de Coleta';
		}else if(acao == 'C'){
			var titulo =  'Informar Coleta Realizada';
		}else if(acao == 'R') {
			var titulo =  'Informar Recebimento';
		}

		$.confirm({
            title: titulo,
            columnClass: 'col-md-6 col-md-offset-3',
            content:'<form action="/assistencia-tecnica/rastreamento-garantias/realiza-operacoes" method="post" role="form" class="form" id="form-modal">' +
		                '<input type="hidden" name="operacao" value="'+acao+'">'+
		                '<input type="hidden" name="id" value="'+id+'">'+
		                '<div class="form-group">' +
		                    ((acao == 'S' || acao == 'C' ) ? '<label>Data*</label>' : '<label>Chave Acesso*</label>') + 
		                    ((acao == 'S' || acao == 'C' ) ? '<input type="text" name="data" class="form-control data" value="'+dia+'">' : '<input type="text" name="chave_acesso" class="form-control chave-acesso">') + 
		                '</div>'+
                	'</form>',
            onOpen: function () {
                $('.data').mask('00/00/0000');
            },
            buttons: {
                formSubmit: {
                    text: 'Salvar',
                    btnClass: 'btn-green',     
                    action: function () {
                       if(acao == 'R'){
                       		var chave_acesso_informada = this.$content.find('.chave-acesso').val();
                       		if(chave_acesso_informada != chave_acesso){
                       			$.alert('A chave de acesso informada não pertence a nota selecionada! Verifique!');
	                    		return false;
                       		}else{
                       			this.$content.find('#form-modal').submit();
                       		}
                       }else{
                       		this.$content.find('#form-modal').submit();
                       }
                    }
                },
                Cancelar: function () {
                },
            },
        });

	});

};
$(document).ready(function(){
    rastreamentoGarantiasJs.onReady();
});