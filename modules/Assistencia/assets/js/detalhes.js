detalhesJS = {
    onReady: function() {
    	adicionaSelect2Defeitos($('#select-defeito'));
        adicionaSelect2Chamados($('#select-chamados'));
    	adicionaSelect2Fornecedores($('#select-fornecedores'));
        adicionaSelect2Cliente();
    	uploadFiles();
    	adicionarAcoes();
    	mascaras();
    	adicionarAtendimentoProtocolo();
        deletaArquivos();
        editarAtendimentoProtocolo();
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

    formatRepoCod: function (repo) {
        if (repo.loading) return 'Buscando...';
        var markup = '<div>' +
            '<div>' + repo.codigo + '</div>' +
            '</div>';
        markup += '</div></div>';
        return markup;
    },

    formatRepoCodSelection: function (repo) {
        if ( repo.codigo ) {
            return repo.codigo;
        } else {
            return repo.text;
        }
    },

};


var adicionaSelect2Defeitos = function(campo) {
    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-defeitos',
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
        minimumInputLength: 0,
        templateResult: detalhesJS.formatRepo,
        templateSelection: detalhesJS.formatRepoSelection

    });

};


var adicionaSelect2Fornecedores = function(campo) {
    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-fornecedores',
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
        templateResult: detalhesJS.formatRepo,
        templateSelection: detalhesJS.formatRepoSelection

    });

};


var uploadFiles = function () {
    $("#upload").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 40000,
        maxFilesNum: 20,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        }

    }); 

    $("#upload-laudo").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 40000,
        maxFilesNum: 1,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        }

    }); 
};

var adicionarAcoes = function(){

	$('.btn-add-atendimento').on('click', function() { 
        var element = $(this);
        abrirModalAtendimento(element);
    });

    $('.btn-edit-atendimento').on('click', function() { 
        var element = $(this);
        abrirModalEditAtendimento(element);
    });

    $('.btn-del-atendimento').on('click', function() { 
        var element = $(this);
        abrirModalExclusaoAtendimento(element);
    });
};


var abrirModalAtendimento = function(element) {
    var id = element.data('id'),
        protocolo = element.data('protocolo'),
        boxModalCalled = $('body').find('.modal-adicionar-atendimento');

    boxModalCalled.find('input[name="registro_id"]').val(id);
    boxModalCalled.find('.protocolo').html(protocolo);
    boxModalCalled.modal('toggle');
    boxModalCalled.on('shown.bs.modal', function() {
        boxModalCalled.find('input[name="date"]').focus();
    });
};

var adicionarAtendimentoProtocolo = function() {

  	$('.btn-adicionar-atendimento-submit').on('click', function() { // processa o form
    	var btn = $('.btn-adicionar-atendimento-submit');
            btn.button('loading');

            $.ajax({
                type: 'POST',
                url: '/assistencia-tecnica/atendimento/ajax/adicionar-atendimento-protocolo',
                data: $('.form-add-atendimento').serialize(),
                dataType: 'json',
                success: function(data, textStatus, jqXHR) {
                    if (data.error) {
                        $('.form-add-atendimento').find('.success').html('');
                       } else {
                        $('.form-add-atendimento').find('.notification').html('').css('display', 'none');
                     
                        $('body').find('.modal-adicionar-atendimento').on('hide.bs.modal', function(e) {
                            location.reload();
                        });

                        setTimeout(function() {
                            $('.modal-adicionar-atendimento').modal('toggle');
                        }, 500);
                    }
                    btn.button('reset');
                }
            });
    });
};

var abrirModalExclusaoAtendimento = function(element) {
    var id = element.data('id'),
        protocolo = element.data('protocolo'),
        boxModalCalled = $('body').find('.modal-delete-atendimento');

    boxModalCalled.find('input[name="protocolo"]').val(protocolo);
    boxModalCalled.find('input[name="atendimento_id"]').val(id);
    boxModalCalled.modal('toggle');
};


var mascaras = function () {
	$(".data").mask('00/00/0000');
};

var deletaArquivos = function () {

    $('.btn-delete-file-modal').on('click', function () {
        var element = $(this);
        var id = element.data('id'),
            name = element.data('name'),
            boxModal = $('body').find('.modal-delete-arquivo');

        boxModal.find('input[name="id"]').val(id);
        boxModal.find('[name="name"]').val(name);
        boxModal.find('.modal-body strong').html(name);
        boxModal.modal('toggle');
    });
};


var abrirModalEditAtendimento = function(element) {

    var id = element.data('id'),
        texto = element.data('atendimento'),
        dt_atendimento = element.data('dt-atendimento'),
        laudo = element.data('laudo'),
        protocolo = element.data('protocolo'),
        boxModalCalled = $('body').find('.modal-editar-atendimento');

        boxModalCalled.find('input[name="id"]').val(id);
        boxModalCalled.find('textarea[name="atendimento"]').val(texto);
        boxModalCalled.find('input[name="dt_atend"]').val(dt_atendimento);
        boxModalCalled.find('select[name="considerar_laudo"]').val(laudo);
        boxModalCalled.find('.protocolo').html(protocolo);
        boxModalCalled.find('.titulo').html('Editar');
        boxModalCalled.modal('toggle');
        boxModalCalled.on('shown.bs.modal', function() {
            boxModalCalled.find('input[name="date"]').focus();
        });
};


var editarAtendimentoProtocolo = function() {

    $('.btn-editar-atendimento-submit').on('click', function() { // processa o form
        var btn = $('.btn-editar-atendimento-submit');
            btn.button('loading');

            $.ajax({
                type: 'POST',
                url: '/assistencia-tecnica/atendimento/ajax/editar-atendimento-protocolo',
                data: $('.form-edit-atendimento').serialize(),
                dataType: 'json',
                success: function(data, textStatus, jqXHR) {
                    if (data.error) {
                        $('.form-edit-atendimento').find('.success').html('');
                         } else {
                        $('body').find('.modal-editar-atendimento').on('hide.bs.modal', function(e) {
                            location.reload();
                        });

                        setTimeout(function() {
                            $('.modal-editar-atendimento').modal('toggle');
                        }, 500);
                    }
                    btn.button('reset');
                }
            });
    });
};

var adicionaSelect2Chamados = function(campo) {
    campo.select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o número do chamado para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-chamados-erp',
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
        templateResult: detalhesJS.formatRepoCod,
        templateSelection: detalhesJS.formatRepoCodSelection

    });

};

var adicionaSelect2Cliente = function() {

    $('#select-cliente-envio').select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o código ou a descrição do cliente para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-clientes',
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
        templateResult: detalhesJS.formatRepo,
        templateSelection: detalhesJS.formatRepoSelection

    });

};


$(document).ready(function(){
    detalhesJS.onReady();
});
