ordemServicoImprimir = {
    signaturePad: '', 
    onReady: function() {    

        /*$('.div-assinatura-1').css('visibility','hidden');
        $('.div-assinatura').css('visibility','hidden');
        $('.signature-pad').css('max-height','70px');
        $('#assinar').on('click', function() {
            $('.div-assinatura-1').css('visibility','visible');
            $('.div-assinatura').css('visibility','visible');
            $('.signature-pad').css('max-height','450px');
            signaturePad.clear();
        });


        $('#btn-limpar').on('click', function(){
            signaturePad.clear();
        });*/

     
         
       /* $("#btn-confirmar").on('click', function () {
            var element = $("#relatorio"); // global variable
            var getCanvas; // global variable
            var ordem_id = $("#ordem_id").val();



            $.ajax({
                type: 'POST',
                url:  '/assistencia-externa/anexa-imagem-assinada',
                data: {'ordem_id':ordem_id, 'imagem' : $('#img').val(), 'tipo_anexo': $('#tipo_anexo').val()  },
                dataType: 'json',
                success: function (data, textStatus, jqXHR) {
                    if(data){
                        $.alert('Relatório anexado com sucesso! Clique em voltar para continuar!');
                    } else {
                        $.alert('Ocorreu um Erro! Tente Novamente Mais Tarde!');
                    }

                }
            });



            /*html2canvas( $("#relatorio"), { letterRendering: 1, allowTaint : true, onrendered : function (canvas) { 
               
                var imgageData = canvas.toDataURL("image/png");


                console.log(imgageData);
                    // Now browser starts downloading it instead of just showing it
                var newData = imgageData.replace(/^data:image\/png/, "data:application/octet-stream");

                console.log(newData);


                } 
            });*/
               /* window.scrollTo(0,0); 
                html2canvas($('#relatorio').get(0)).then( function (canvas) {
                    
                    var imgageData = canvas.toDataURL("image/png");

                    $('#img').val(imgageData);
                
                });

                setTimeout(function()
                { 
                    $.ajax({
                        type: 'POST',
                        url:  '/assistencia-externa/anexa-imagem-assinada',
                        data: {'ordem_id':ordem_id, 'imagem' : $('#img').val(), 'tipo_anexo': $('#tipo_anexo').val()  },
                        dataType: 'json',
                        success: function (data, textStatus, jqXHR) {
                            if(data){
                                $.alert('Relatório anexado com sucesso! Clique em voltar para continuar!');
                            } else {
                                $.alert('Ocorreu um Erro! Tente Novamente Mais Tarde!');
                            }

                        }
                    });
                }, 500); */

             /*html2canvas(element, {
             onrendered: function (canvas) {
                    getCanvas = canvas;

                    $("#previewImage").append(canvas);
                    var imgageData = getCanvas.toDataURL("image/png");
                    // Now browser starts downloading it instead of just showing it
                    var newData = imgageData.replace(/^data:image\/png/, "data:application/octet-stream");
                    
                    console.log(newData);

                 }
             });*/
      /*  });*/

       /* $("#btn-Convert-Html2Image").on('click', function () {
        var imgageData = getCanvas.toDataURL("image/png");
        // Now browser starts downloading it instead of just showing it
        var newData = imgageData.replace(/^data:image\/png/, "data:application/octet-stream");
        $("#btn-Convert-Html2Image").attr("download", "your_pic_name.png").attr("href", newData);
        });*/


        $("#assinar").on("click", () => {
            $.confirm({
                columnClass: 'col-md-8 col-md-offset-4',
                title: "Assinar Documento",
                content:
                  ''+  
                    '<div class="div-assinatura ">' +
                    '   <div id="signature-pad" class="signature-pad">' +
                    '        <div class="signature-pad--body">' +
                    '          <canvas></canvas>'+
                    '        </div>'+
                    '    </div>'+
                    '</div>',
                onContentReady: function () {
                    var canvas = document.querySelector("canvas");

                    signaturePad = new SignaturePad(canvas, {
                        backgroundColor: "rgb(255,255,255)"
                    });
                     
                    function resizeCanvas() {
                        var ratio =  Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext("2d").scale(ratio, ratio);
                        signaturePad.clear(); // otherwise isEmpty() might return incorrect value
                    }

                    resizeCanvas();



                   /* var signaturePad = new SignaturePad(canvas, {
                      backgroundColor: 'rgb(255, 255, 255)' 
                    });

                    console.log(signaturePad);

                    $('.div-assinatura').css('visibility','visible');
                    $('.signature-pad').css('max-height','450px');
                    signaturePad.clear(); */
                },
                buttons: {
                    formSubmit: {
                        text: "Salvar",
                        btnClass: "btn-green",
                        action: function(res) {
                       

                            var img = signaturePad.toDataURL("image/jpeg");
                            console.log(img);
                             $.ajax({
                                type: 'POST',
                                url:  '/assistencia-externa/anexa-imagem-assinada',
                                data: {'ordem_id':$("#ordem_id").val(), 'imagem' : img, 'tipo_anexo': $('#tipo_anexo').val()  },
                                dataType: 'json',
                                success: function (data, textStatus, jqXHR) {
                                    if(data){
                                        $.alert('Assinatura anexada com sucesso! Clique em voltar para continuar!');
                                        window.location.reload();
                                    } else {
                                        $.alert('Ocorreu um Erro! Tente Novamente Mais Tarde!');
                                    }

                                }
                            });
                           
                        }
                    },
                    Limpar: function() {
                        signaturePad.clear();
                        return false;
                    },
                    Cancelar: function() {

                    }
                }
            });
        });

    },

};


$(document).ready(function(){
    ordemServicoImprimir.onReady();
});