empresasPrincipalJs = {

onReady: function(){

        if ( $('.combo-empresa').length ) {
			empresasPrincipalJs.controlaEmpresa();			
		}

		$('.combo-empresa').on('change', function(){
			empresasPrincipalJs.controlaEmpresa();
			document.location.reload();
		});

  	},


	controlaEmpresa: function(){
        
		var combo = $('.combo-empresa'),
			empr_id = combo.val(),			
			html; 


		$.ajax({
			type: 'POST',
			url: '/ajax/controla-empresa-principal',
			data: {'empr_id': empr_id},
			dataType: 'json',
			success: function (data, textStatus, jqXHR) { 

                if (data.diretorio){
  					$('.logo-header').attr("src","/images/"+data.diretorio+"/logo-header.png");
  			    }

			}
		});
	},

	urlParameter: function(name){
		var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	    if (results==null){
	       return null;
	    }else{
	       return results[1] || 0;
	    }
	},
};

$(document).ready(function(){
	empresasPrincipalJs.onReady();
});
