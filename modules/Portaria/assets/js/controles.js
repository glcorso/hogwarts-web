controles = {
    onReady: function() {
        salvarForms();
        iniciaForms();
        limparForms();
        carregaForms();
        insereDataAtual();
        selecionaTipo();
        retornaKmVeiculo();
        retornaPlacaAnterior();
    },
};

function exibeForms(tipo) {

    if (tipo == 1) {
        $('.terceiro').slideUp( function(){
            $('.veiculo_resfriar').slideDown();
        });
        $('.required_terceiro').attr('required',false);
        $('.required_resfriar').attr('required',true);
        $('.funcionario-id-1').select2();
        $('.veiculo-id-1').select2();

    } else {

        $('.assunto-id-0').select2();
        $('.veiculo_resfriar').slideUp( function(){
            $('.terceiro').slideDown();
        });
        $('.required_resfriar').attr('required',false);
        $('.required_terceiro').attr('required',true);

    }
}


var iniciaForms = function () {
    // $('.veiculo').select2();

    $('.data').mask("99/99/99 99:99:99");
}

var limparForms = function () {
    $('.limpar').click( function(e){
       window.location.reload();
        
    });
}

var carregaForms = function () {
    
    $('.editar').click( function(e){
        e.preventDefault();
        $("html, body").animate({ scrollTop: +58 }, "slow");
        const id = $(this).data('id');
        $('.cadastro_id').val(id);

        const entrada = $(this).data('entrada');
        const saida = $(this).data('saida');
        let pessoa = $(this).data('pessoa');
        const veiculo = $(this).data('veiculo');
        const destino = $(this).data('destino');
        const km_saida = $(this).data('km-saida');
        const km_entrada = $(this).data('km-entrada');
        const empresa = $(this).data('empresa');
        const placa = $(this).data('placa');
        const tipo = $(this).data('tipo');
        const tfuncionario_id = $(this).data('tfuncionario-id');
        const assunto_id = $(this).data('assunto-id');
        const veiculo_id = $(this).data('veiculo-id');
      

        $('.tipo').val(tipo);
        $('.entrada-'+tipo).val(entrada);
        $('.saida-'+tipo).val(saida);
        $('.veiculo-'+tipo).val(veiculo);
        $('.destino-'+tipo).val(destino);
        $('.km_saida-'+tipo).val(km_saida);
        $('.km_entrada-'+tipo).val(km_entrada);
        $('.empresa-'+tipo).val(empresa);
        $('.placa-'+tipo).val(placa);
        $('.veiculo-id-'+tipo).val(veiculo_id).trigger('change');
        $('.funcionario-id-'+tipo).val(tfuncionario_id).trigger('change');
        $('.assunto-id-'+tipo).val(assunto_id).trigger('change');

        $('.km-retorno').show();
        $('.hora-retorno').show();
        $('.saida-terceiro').show();
        
        let nome;
        if (tipo == 1) {
            pessoa = tfuncionario_id;

            $('.tipo').attr('readonly', true);
            $('.funcionario-id-1').attr('disabled', true);
            $('.saida-1').attr('readonly', true);
            $('.btn-agora-saida').attr('disabled', true);
            $('.veiculo-id-1').attr('disabled', true);
            $('.destino-1').attr('readonly', true);
            $('.km_saida-1').attr('readonly', true);

        }else{
            $('.tipo').attr('readonly', true);
            $('.placa-0').attr('readonly', true);
            $('.pessoa-0').attr('readonly', true);
            $('.empresa-0').attr('readonly', true);
            $('.veiculo-0').attr('readonly', true);
            $('.assunto-id-0').attr('disabled', true);
            $('.entrada-0').attr('readonly', true);
            $('.btn-agora-entrada').attr('disabled', true);

        }
        $('.pessoa-'+tipo).val(pessoa);

        //$('.titulo').html('');


        if ($('.adicionar_editar').hasClass('adicionar')) {

            //$('.acoes').prepend('<a href="" class="btn btn-info waves-effect waves-light novo__cadastro"> <i class="fa fa-address-card-o"></i> Novo Cadastro </a>');
            $('.adicionar_editar').removeClass('adicionar');
            $('.adicionar_editar').addClass('editar');
            $('.adicionar_editar').html('<i class="fa fa-check adicionar_editar_label"></i> Salvar');
            
            $('.novo__cadastro').click( function(e){
                e.preventDefault();
                $('.titulo').html('Cadastro de Controle');
                
                $('.limpa').val('');
                $('.cadastro_id').val('');

                $('.novo__cadastro').remove();
                $('.adicionar_editar').removeClass('editar');
                $('.adicionar_editar').addClass('adicionar');
                $('.adicionar_editar').html('<i class="fa fa-plus adicionar_editar_label"></i> Salvar');
            });
        }

        exibeForms(tipo);

    });
}


var insereDataAtual = function () {

    $('.insere__data').click( function(e){
        e.preventDefault();
        
        var formattedDate = new Date();
        var d = ("00" +  formattedDate.getDate()   ) .slice (-2);
        var m =  ("00" +  (formattedDate.getMonth() + 1)   ) .slice (-2);
        var y = formattedDate.getFullYear();
        var h = ("00" +  formattedDate.getHours()   ) .slice (-2);
        var min = ("00" +  formattedDate.getMinutes() ) .slice (-2);
        var s = ("00" +  formattedDate.getSeconds() ) .slice (-2);;
        
        console.log(d + "/" + m + "/" + y)

        $(this).closest('.form-group').find('.data').val( d+ "/" +m+ "/" +y+" "+h+":"+min+":"+s);
        // $(".saida").val(d + "/" + m + "/" + y);

    });
}

const selecionaTipo = function() {
    $('.tipo').change( function(callback) {
        const tipo = $(this).val();

        exibeForms(tipo);
    });
};


function atualizaListagem(data) {
    
    var html = '<tr>'/

                        '<td>'/
                            '{% if row.tipo == 1 %}'/
                                '{{row.nome}}'/
                            '{% else %}'/
                                '{{row.pessoa}}'  /
                            '{% endif %}'/
                        '</td>'/
                        '<td>'/
                            '{% if row.entrada is not null %}'/
                                '{{row.entrada|date("d/m/Y H:i:s")}}</td>'/
                            '{% endif %}'/
                        '<td>'/
                            '{% if row.saida is not null %}'/
                                '{{row.saida|date("d/m/Y H:i:s")}}</td>'/
                            '{% endif %}'/
                        '<td>'/
                            '{% if row.tipo == "1" %}'/
                                '<span class="label label-success" title="Veículo Resfriar">Veículo Resfriar</span>'/
                            '{% else %}'/
                                '<span class="label label-warning"' /'title="Terceiro">Terceiro</span>'/
                            '{% endif %}'/
                        '</td>'/
                        '<td class="text-nowrap">'/
                           '{% if data.permissao.permissao > 2 %}'/
                            '<a href="" data-toggle="tooltip" data-original-title="Editar"' /
                            'class="editar btn" '/
                            'data-id="{{row.id}}"'/
                            'data-entrada="{{row.entrada}}"'/
                            'data-saida="{{row.saida}}"'/
                            'data-pessoa="{{row.pessoa}}"'/
                            'data-veiculo="{{row.veiculo}}"'/
                            'data-destino="{{row.destino}}"'/
                            'data-km-saida="{{row.km_saida}}"'/
                            'data-km-entrada="{{row.km_entrada}}"'/
                            'data-empresa="{{row.empresa}}"'/
                            'data-placa="{{row.placa}}"'/
                            'data-tipo="{{row.tipo}}"'/
                            'data-tfuncionario-id="{{row.tfuncionario_id}}"  >'/
                                '<i class="fa fa-pencil text-inverse m-r-10" data-name="{{row.id}}" ></i>'/
                                '{% if row.saida is null %}'/
                                    'Saída'/
                                '{% elseif row.entrada is null %}'/
                                    'Entrada'/
                                '{% else %}'/
                                    'Editar'/
                                '{% endif %}'/
                            '</a>'/
                            '{% endif %}'/
                            '{% if data.permissao.permissao > 2 %}'/
                                '<a href="javascript:void(0);"'/
                                   'class="btn-delete-modal"'/
                                   'data-id="{{row.id}}"'/
                                   'data-name="{{row.usuario}}"'/
                                   'data-toggle="tooltip"'/
                                   'data-original-title="Excluir">'/
                                   '<i class="fa fa-close text-danger m-r-10"></i>'/
                                '</a>'/
                            '{% endif %}'/
                        '</td>'/
                    '</tr>';

}

const salvarForms = function () {
        
    $('.btn-salvar').on('click',function(e){
        var btn = $(this);
        btn.prop('disabled', true);
   
        const tipo = $('.tipo').val();
        const cadastro_id = $('.cadastro_id').val();

        if (cadastro_id != '') {
            urlAjax = '/portaria/editAjax';
            typeAjax = 'PUT';
        } else {
            urlAjax = '/portaria/addAjax';
            typeAjax = 'POST';
        }

        const pessoa = $('.pessoa-'+tipo).val();
        const entrada = $('.entrada-'+tipo).val();
        const saida = $('.saida-'+tipo).val();
        const veiculo = $('.veiculo-'+tipo).val();
        const destino = $('.destino-'+tipo).val();
        const km_saida = $('.km_saida-'+tipo).val();
        const km_entrada = $('.km_entrada-'+tipo).val();
        const empresa = $('.empresa-'+tipo).val();
        const placa = $('.placa-'+tipo).val();
        const veiculo_id = $('.veiculo-id-'+tipo).val();
        const assunto_id = $('.assunto-id-'+tipo).val();
        const funcionario_id = $('.funcionario-id-'+tipo).val();

        var v_nao_executa = '0';

        if (cadastro_id == '') {
            if(tipo == '1'){
                if($('.funcionario-id-1').val() == null){
                   $.alert('Informe o Funcionário!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.veiculo-id-1').val() == null){
                   $.alert('Informe o Veículo!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.destino-1').val() == ''){
                   $.alert('Informe o Destino!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.saida-1').val() == ''){
                   $.alert('Informe a data e hora de saída!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }
                if($('.km_saida-1').is(':visible')){

                    if($('.km_saida-1').val() == ''){
                       $.alert('Informe a KM de Saída!');   
                       v_nao_executa = '1';
                       btn.prop('disabled', false);
                    }
                }
            }else{


                if($('.placa-0').val() == ''){
                   $.alert('Informe a placa!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.pessoa-0').val() == ''){
                   $.alert('Informe a pessoa!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.empresa-0').val() == ''){
                   $.alert('Informe a empresa!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.veiculo-0').val() == ''){
                   $.alert('Informe o veiculo!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.assunto-id-0').val() == null){
                   $.alert('Informe o Assunto!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                 if($('.entrada-0').val() == ''){
                   $.alert('Informe a data e hora de entrada!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }


            }
        }else{
            if(tipo == '1'){
                if($('.entrada-1').val() == ''){
                   $.alert('Informe a data e hora de entrada!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

                if($('.km_entrada-1').is(':visible')){
                    if($('.km_entrada-1').val() == ''){
                       $.alert('Informe a KM de entrada!');   
                       v_nao_executa = '1';
                       btn.prop('disabled', false);
                    }
                }

            }else{


                if($('.saida-0').val() == ''){
                   $.alert('Informe a data e hora de saída!');   
                   v_nao_executa = '1';
                   btn.prop('disabled', false);
                }

            }
        }    

        if(v_nao_executa == '0'){
            $.ajax({
                type: typeAjax,
                url: urlAjax,
                data: { 'pessoa': pessoa, 
                        'tipo': tipo, 
                        'entrada': entrada, 
                        'saida': saida, 
                        'veiculo': veiculo, 
                        'destino': destino, 
                        'km_saida': km_saida, 
                        'km_entrada': km_entrada, 
                        'empresa': empresa, 
                        'placa': placa,
                        'id': cadastro_id,
                        'veiculo_id': veiculo_id,
                        'assunto_id': assunto_id,
                        'funcionario_id': funcionario_id
                    },
                dataType: 'json',
                success: function (data, textStatus, jqXHR, mensagem) { 
                    if(data){
                        location.reload();
                    }else{
                        $.alert('Ocorreu um erro ao salvar, tente novamente!'); 
                    }
                },
                error:function (data, textStatus, jqXHR, mensagem) { 
                    $.alert('Ocorreu um erro ao salvar, tente novamente!');
                    //location.reload();
                },
            });
        }
    });
}


var retornaKmVeiculo = function() {
    $(document).on('change','.veiculo-id-1', function() {

        var veiculo_id = $(this).val();
        var id = $('.cadastro_id').val();

        console.log(id);

        $.ajax({
            type: 'GET',
            url: '/portaria/veiculos/retorna-km',
            data: { 'veiculo_id':veiculo_id },
            dataType: 'json',
            success: function (data, textStatus, jqXHR, mensagem) { 
                if(data.veiculo){
                    if(data.veiculo.controle_km == '1'){
                        $('.div-km-sai').show();
                        $('.km_saida-1').attr('required');
                        if(id == ''){
                            $('.km_saida-1').val(data.veiculo.km_atual);
                        }
                        if(id != ''){
                            $('.km_entrada-1').attr('required');
                            $('.div-km-ent').show();
                        }
                    }else{
                        $('.km_saida-1').removeAttr('required');
                        $('.div-km-sai').hide();
                        if(id != ''){
                            $('.km_entrada-1').removeAttr('required');
                            $('.km-retorno').hide();
                        }
                    }
                }
            },
            error:function (data, textStatus, jqXHR, mensagem) { 
                $.alert('Ocorreu um erro ao consultar o veículo, tente novamente!');
             //   location.reload();
            },
        });

    });
};



var retornaPlacaAnterior = function() {
    $(document).on('blur','.placa-0', function() {

        var placa = $(this).val();
        var id = $('.cadastro_id').val();

        console.log(id);

        $.ajax({
            type: 'GET',
            url: '/portaria/controle/retorna-placa-anterior',
            data: { 'placa':placa },
            dataType: 'json',
            success: function (data, textStatus, jqXHR, mensagem) { 
                if(data.controle){
                    $('.pessoa-0').val(data.controle.pessoa);  
                    $('.empresa-0').val(data.controle.empresa);  
                    $('.veiculo-0').val(data.controle.veiculo);  
                }
            }
        });

    });
};

$(document).ready(function(){
    controles.onReady();
});
