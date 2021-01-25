ordemServicoJs = {
    onReady: function() {
    	naoSeioCep();
    	consultaCep();
    	aplicaMascaras();
    	validarCPF_CNPJ();
    	retornaSerie();
    	if(ordem_id == ''){
    		capturaQr();
    	}
    	coletarAssinatura();
    	uploadFiles();
        controlaTabs();
        adicionaClick();
        bloqueiaSubmit();
   	},
};

const adicionaClick = () => {
    $(".btn-reprovar").on("click", () => {
        $.confirm({
            title: "Reprovar Serviço",
            content:
                "" +
                '<div class="form-group">' +
                "<strong>Você confirma a Reprovação do Serviço? <strong>" +
                "<br><small>Após confirmar a Reprovação do Serviço, este procedimento não poderá ser desfeito.</small>" +
                "</div>",
            buttons: {
                formSubmit: {
                    text: "Reprovar Serviço",
                    btnClass: "btn-green",
                    action: function() {
                        $.ajax({
                            type: "POST",
                            url:
                                "/assistencia-tecnica/atendimento/ajax/reprovar-servico",
                            data: {
                                id : ordem_id
                            },
                            dataType: "json",
                            success: function(data, textStatus, jqXHR) {
                                if (!data.error) {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                },
                Cancelar: function() {}
            }
        });
    });
};

var aplicaMascaras = function() {
	//$('#campo-cpf').mask('000.000.000-00', {reverse: true});
	$('.date').mask("99/99/9999");
	$('#cep').mask('00000-000');
	$('.telefone').mask('(00) 0000-0000');
	$('.celular').mask('(00) 00000-0000');
};


var consultaCep = function (){

	$('#cep').on('blur', function () {
		cep =  $(this).val();

		$.ajax({
	        type: 'GET',
	        url: 'https://viacep.com.br/ws/'+cep+'/json/',
	        dataType: 'json',
	        success: function (data, textStatus, jqXHR) {
	         	if(data){
                 	$('#bairro').val(data.bairro);
				 	$('#cidade').val(data.localidade);
				 	$('#uf').val(data.uf);
				 	$('#endereco').val(data.logradouro);
				 	$('#complemento').val(data.complemento);
	         	} else {
					$.alert('Ocorreu um Erro! Tente Novamente Mais Tarde!');
				}

	        }
	   	});

	});

};

var naoSeioCep = function () {

	$('#link-nao-sei-cep').on('click', function (){

		$.confirm({
		    title: 'Buscar CEP',
		    columnClass: 'col-md-8 col-md-offset-2',
		    content: '' +
		    '<form action="javascript:void(0);" class="form-buscar-cep">' +
		    	'<div class="form-group">' +
				    '<label>Rua*</label>' +
				    '<input type="text" placeholder="Informe a Rua" maxlength="300" name="rua" class="rua form-control" required />' +
			    '</div>' +
			    '<div class="form-group">' +
				    '<label>Cidade*</label>' +
				    '<input type="text" placeholder="Informe a Cidade" maxlength="300" name="cidade" class="cidade form-control" required />' +
			    '</div>' +
			    '<div class="form-group">' +
				    '<label>Estado*</label>' +
				   	'<select name="uf" class="form-control estado" required="required">'+
				   	'<option value=""></option>'+
				   	'<option value="AC">Acre</option>'+
				   	'<option value="AL">Alagoas</option>'+
				   	'<option value="AP">Amapá</option>'+
				   	'<option value="AM">Amazonas</option>'+
				   	'<option value="BA">Bahia</option>'+
				   	'<option value="CE">Ceará</option>'+
				   	'<option value="DF">Distrito Federal</option>'+
				   	'<option value="ES">Espírito Santo</option>'+
				   	'<option value="GO">Goiás</option>'+
				   	'<option value="MA">Maranhão</option>'+
				   	'<option value="MT">Mato Grosso</option>'+
				   	'<option value="MS">Mato Grosso do Sul</option>'+
				   	'<option value="MG">Minas Gerais</option>'+
				   	'<option value="PA">Pará</option>'+
				   	'<option value="PB">Paraíba</option>'+
				   	'<option value="PR">Paraná</option>'+
				   	'<option value="PE">Pernambuco</option>'+
				   	'<option value="PI">Piauí</option>'+
				   	'<option value="RJ">Rio de Janeiro</option>'+
				   	'<option value="RN">Rio Grande do Norte</option>'+
				   	'<option value="RS">Rio Grande do Sul</option>'+
				   	'<option value="RO">Rondônia</option>'+
				   	'<option value="RR">Roraima</option>'+
				   	'<option value="SC">Santa Catarina</option>'+
				   	'<option value="SP">São Paulo</option>'+
				   	'<option value="SE">Sergipe</option>'+
				   	'<option value="TO">Tocantins</option>'+
					'</select>'+
				'</div>' +
				'<div class="form-group">' +
					'<button type="button" class="btn btn-success btn-consulta-cep">Consultar Cep</button>'+
				'</div>' +
				'<div class="form-group div-cep-encontrado" style="display:none;">' +
				    '<label>CEPs Encontrados*</label>' +
				   	'<select name="cep_encontrado" class="form-control cep_encontrado" required="required">'+
				   	'</select>'+
				   	'<input type="hidden" name="bairro_hidden" class="bairro_hidden">'+
				   	'<input type="hidden" name="cidade_hidden" class="cidade_hidden">'+
				   	'<input type="hidden" name="estado_hidden" class="estado_hidden">'+
				   	'<input type="hidden" name="endereco_hidden" class="endereco_hidden">'+
				   	'<input type="hidden" name="complemento_hidden" class="complemento_hidden">'+
				'</div>' +
		    '</form>',
		    onContentReady: function () {
		        var self = this;
		        this.buttons.formSubmit.disable();
		        this.$content.find('.btn-consulta-cep').click(function(){

		        	var cidade = self.$content.find('.cidade').val(),
	                 	estado = self.$content.find('.estado').val(),
	                 	rua    = self.$content.find('.rua').val();

	                if(!rua){
	                    $.alert('Informe a Rua!');
	                    return false;
	                }
	                if(!cidade){
	                    $.alert('Informe a Cidade!');
	                    return false;
	                }
	                if(!estado){
	                    $.alert('Informe o Estado!');
	                    return false;
	                }

		        	$.ajax({
				        type: 'GET',
				        url: 'https://viacep.com.br/ws/'+estado+'/'+cidade+'/'+rua+'/json',
				        dataType: 'json',
				        success: function (data, textStatus, jqXHR) {
				        	var html = '<option value="">Selecione um CEP </option>';
				         	if(data.length > 0 ){
							 	$.each(data, function(i,v){
									html += '<option value="'+v['cep']+'">'+v['cep']+' - '+v['logradouro']+' - '+v['bairro']+' - '+v['localidade']+'</option>';
								});
				         	} else {
								html = '<option value="">Nenhum CEP encontrado, verifique os dados informados.</option>';
							}

		        			self.$content.find('.div-cep-encontrado').show();
							self.$content.find('.cep_encontrado').html(html);
				        }
				   	});

		        	self.$content.find('.cep_encontrado').on('change', function (){
		        		cep_selecionado = $(this).val();

		        		$.ajax({
					        type: 'GET',
					        url: 'https://viacep.com.br/ws/'+cep_selecionado+'/json/',
					        dataType: 'json',
					        success: function (data, textStatus, jqXHR) {
					         	if(data){
								 	self.$content.find('.bairro_hidden').val(data.bairro);
								 	self.$content.find('.cidade_hidden').val(data.localidade);
								 	self.$content.find('.estado_hidden').val(data.uf);
								 	self.$content.find('.endereco_hidden').val(data.logradouro);
								 	self.$content.find('.complemento_hidden').val(data.complemento);
									self.buttons.formSubmit.enable();
					         	} else {
									$.alert('Ocorreu um Erro! Tente Novamente Mais Tarde!');
								}


					        }
					   	});

		        	});


		        });
		    },

		    buttons: {
		        formSubmit: {
		            text: 'Confirmar',
		            btnClass: 'btn-green',
		            action: function () {
		            	$('#cep').val(this.$content.find('.cep_encontrado').val());
	                 	$('#bairro').val(this.$content.find('.bairro_hidden').val());
					 	$('#cidade').val(this.$content.find('.cidade_hidden').val());
					 	$('#uf').val(this.$content.find('.estado_hidden').val());
					 	$('#endereco').val(this.$content.find('.endereco_hidden').val());
					 	$('#complemento').val(this.$content.find('.complemento_hidden').val());

		            }
		        },
		        Cancelar: function () {

		        },
		    },
		});

	});

};


var validarCPF_CNPJ = function (){

	$('#campo-cpf-cnpj').blur(function(){
        var cpf_cnpj = $(this).val(),
            existe_erp = false,
            cpf_cnpj_number = $(this).val().replace(/[^\d]+/g,'');

          //  console.log('aaa');

        if ( valida_cpf_cnpj( cpf_cnpj ) ) {

	        $('#campo-cpf-cnpj').val( formata_cpf_cnpj( cpf_cnpj ) );
        	//formata_cpf_cnpj(cpf_cnpj);
        	//console.log('aaaaaaaaeeeeeeeeee');
            buscaCliente(cpf_cnpj_number,'erp');
        } else {
            $.alert({
			    title: 'Erro!',
			    content: 'O Documento informado é invalido!',
			});
            $(this).val('');
        }

    });
};




var buscaCliente = function(cpf_cnpj, tipo) {

	url = (tipo == 'erp' ) ? '/assistencia-tecnica/atendimento/ajax/busca-cliente-erp' : '/assistencia-tecnica/atendimento/ajax/busca-cliente-assistencia'

	$.ajax({
        type: 'POST',
        url: url,
        data: {'cpf_cnpj': cpf_cnpj},
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
           	if (!data.error ){
	            $('#campo-cpf-cnpj').val( formata_cpf_cnpj( cpf_cnpj ) );
             	preencheCamposCliente(data.cliente, tipo);
                //retornaTimeline(cpf_cnpj);
           	}else{
           		if(tipo == 'erp'){
	        		buscaCliente(cpf_cnpj,'assistencia');
	        	}//else{
	        	//	abreModalNovoCliente(cpf_cnpj);
	        	//	}
           	}
        }
   	});

};


var preencheCamposCliente = function (cliente, tipo) {
	$('#campo-nome-cliente').val(cliente.nome);

    if(tipo == 'erp'){
        $('#campo-nome-cliente').attr('readonly','readonly');
	    $('#campo-cliente-assistencia-erp-id').val(cliente.id);
        $('#campo-cliente-assistencia-id').val('');
    }else{
        $('#campo-nome-cliente').removeAttr('readonly');
        $('#campo-cliente-assistencia-erp-id').val('');
        $('#campo-cliente-assistencia-id').val(cliente.id);
    }

	if(cliente.e_mail !== false ){
		$('#e_mail').val(cliente.e_mail);
	}
	if(cliente.telefone !== false ){
		$('#telefone').val(cliente.telefone);
	}

	if(cliente.cep !== false ){
		$('#cep').val(cliente.cep);
	}

	if(cliente.endereco !== false ){
		$('#endereco').val(cliente.endereco);
	}

	if(cliente.numero !== false ){
		$('#numero').val(cliente.numero);
	}

	if(cliente.bairro !== false ){
		$('#bairro').val(cliente.bairro);
	}

	if(cliente.cidade !== false ){
		$('#cidade').val(cliente.cidade);
	}

	if(cliente.uf !== false ){
		$('#uf').val(cliente.uf);
	}

	if(cliente.complemento !== false ){
		$('#complemento').val(cliente.complemento);
	}
};

var retornaSerie = function() {
	$('#campo-numero-serie').on('blur', function() {
		var value = $(this).val();
		if(value != ''){
			buscaDadosItem(value,'serie');
		}
	});
};

var buscaDadosItem = function(codigo, tipo) {

	url = '/assistencia-tecnica/atendimento/ajax/busca-dados-item';

	$.ajax({
        type: 'POST',
        url: url,
        data: {'codigo': codigo , 'tipo':tipo},
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {

           	if (!data.error ){

             	$('#campo-numero-serie-id').val(data.dadosSerie.serie_id);
                if(data.dadosSerie.sequencial_id != null){
             	    $('#campo-sequencial-id').val(data.dadosSerie.sequencial_id);
                    $('#campo-sequencial').val(data.dadosSerie.sequencial);
                }
             	$('#campo-numero-serie').val(data.dadosSerie.nro_serie);
             	$('#campo-data-fab').val(data.dadosSerie.data_fab);
             	$('#grupo-serie').removeClass('has-danger');
             	$('#grupo-sequencial').removeClass('has-danger');
             	$('#warning-serie').hide();
             	$('#warning-sequencial').hide();

				$('#campo-item-id').val(data.dadosSerie.item_id);
				$('#campo-descricao-item').val(data.dadosSerie.cod_item+' - '+data.dadosSerie.desc_tecnica);
                
                // REALIZA VALIDAÇÕES NRO SERIE

                if(tipo == 'serie'){
					$.ajax({
				        type: 'GET',
				        url: '/assistencia-externa/verifica-servicos',
				        data: {'item_id': data.dadosSerie.item_id},
				        dataType: 'json',
				        success: function (data, textStatus, jqXHR) {
				        	if(!data.retorno){
				        		$.alert('O item informado não possui serviços ativos! Entre em contato com a Resfri Ar! (54) 3511-1111');
				        		$('#grupo-serie').addClass('has-danger');
				        		//$('#warning-serie').show();
				        		$('#campo-numero-serie').val('');
				        		$('#campo-sequencial').val('');
				        		$('#campo-item-id').val('');
				        		$('#campo-descricao-item').val('');
				        	}

	 					}
	   				});
				}
   
           	}else{
           		if(tipo == 'serie'){
	        		$('#grupo-serie').addClass('has-danger');
	        		$('#warning-serie').show();
	        		$('#campo-numero-serie').val('');
	        		$('#campo-sequencial').val('');
	        	}else{
	        		$('#grupo-sequencial').addClass('has-danger');
	        		$('#warning-sequencial').show();
	        		$('#campo-numero-serie').val('');
	        		$('#campo-sequencial').val('');
	        	}
           	}
        }
   	});

};


var capturaQr = function() {

	/*document.querySelector('.btn-captura-qr').addEventListener('click', async (e) => {

		var video = document.createElement("video");
	    var canvasElement = document.getElementById("canvas");
	    var canvas = canvasElement.getContext("2d");
	    var loadingMessage = document.getElementById("loadingMessage");
	    var outputContainer = document.getElementById("output");
	    var outputMessage = document.getElementById("outputMessage");
	    var outputData = document.getElementById("outputData");
	    var gumStream;
	    var textQR;
	    var nroSerie;
	    var posIni;
	    var posFim;


	    function drawLine(begin, end, color) {
	      canvas.beginPath();
	      canvas.moveTo(begin.x, begin.y);
	      canvas.lineTo(end.x, end.y);
	      canvas.lineWidth = 4;
	      canvas.strokeStyle = color;
	      canvas.stroke();
	    }

	    // Use facingMode: environment to attemt to get the front camera on phones
	    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } }).then(function(stream) {
	      video.srcObject = stream;
	      video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
	      video.play();
	      requestAnimationFrame(tick);
	    });



	    function tick() {

	    if (video.readyState === video.HAVE_ENOUGH_DATA) {
	        canvasElement.hidden = false;

	        canvasElement.height = video.videoHeight;
	        canvasElement.width = video.videoWidth;
	        canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
	        var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
	        var code = jsQR(imageData.data, imageData.width, imageData.height, {
	          inversionAttempts: "dontInvert",
	        });
	        if (code) {
	          	drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
	          	drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
	          	drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
	          	drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");

	          	textQR = code.data;

	          	posIni = textQR.indexOf("#NS<");
	          	posFim = textQR.indexOf(">##DT");

	          	nroSerie = textQR.substring((parseInt(posIni) + parseInt(4)), posFim);

	          	$('#campo-numero-serie').val(nroSerie).blur();

	          	canvasElement.hidden = true;
	          	stopStreamedVideo(video);
	        }
	      }
	      requestAnimationFrame(tick);
	    }
	})*/


	$('.btn-captura-qr').on('click' , function() {
		$('#preview').show();
        let scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
      	scanner.addListener('scan', function (content) {
        
      		let textQR = content;

          	posIni = textQR.indexOf("#NS<");
          	posFim = textQR.indexOf(">##DT");

          	nroSerie = textQR.substring((parseInt(posIni) + parseInt(4)), posFim);

          	$('#campo-numero-serie').val(nroSerie).blur();

          	scanner.stop();
          	$('#preview').hide();
	    });


      	Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {
          	scanner.start(cameras[0]);
        } else {
          	console.error('No cameras found.');
        }
      	}).catch(function (e) {
        	console.error(e);
      	});
 	});

};


function stopStreamedVideo(videoElem) {
  	let stream = videoElem.srcObject;
  	let tracks = stream.getTracks();

  	tracks.forEach(function(track) {
    	track.stop();
  	});

 	videoElem.srcObject = null;
}


var coletarAssinatura =  function() {

	$('.btn-assinatura').on('click', function() {
		$('.div-assinatura').toggle();
	});
};

var uploadFiles = function () {
    $("#upload").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 10000,
        maxFilesNum: 20,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        },

    });

     $("#upload2").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 10000,
        maxFilesNum: 20,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        },

    });
};

var controlaTabs = function(){
	$(".nav-link").on("click", function(){
		if(!$(this).hasClass('.disabled')){
		  	var curId = $(this).attr("href");
		  	$(".tab-pane").removeClass("active show");
		  	$(".nav-justified .nav-link").removeClass("active");
		  	$(".tab-pane" + curId).addClass("active show");
	  	}
	});
};

var bloqueiaSubmit = function () {
   $('.form-at-externa').on('submit', function(e) {
         //  e.preventDefault();
         //  e.stopPropagation();
         $('.btn-submit').attr('disabled','disabled');
        //$this.button('loading');
    });

    $('.form-aprovacao').on('submit', function(e) {
         //  e.preventDefault();
         //  e.stopPropagation();
         $('.btn-submit').attr('disabled','disabled');
        //$this.button('loading');
    });

    $('.form-conclusao').on('submit', function(e) {
         //  e.preventDefault();
         //  e.stopPropagation();
         $('.btn-submit').attr('disabled','disabled');
        //$this.button('loading');
    });
};



$(document).ready(function(){
    ordemServicoJs.onReady();
});
