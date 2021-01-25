pagamentoEfetuado = {
    onReady: function() {
        adicionaEventoClick();
        adicionaSelect2ClienteInterno();
        adicionaSelect2Usuarios();
        selecionaTodosCheckbox();
        adicionaAnexos();
        openModal();
        openModalAnexos();
    },
    formatRepoUsuarios: function (repo) {
        if (repo.loading) return 'Buscando...';
        var markup = '<div>' +
            '<div>' + repo.nome + ' - ' + repo.usuario + '</div>' +
            '</div>';
        markup += '</div></div>';
        return markup;
    },

    formatRepoUsuariosSelection: function (repo) {
        if ( repo.nome ) {
            return repo.nome + ' - ' + repo.usuario;
        } else if (repo.usuario) {
            return repo.usuario;
        } else {
            return repo.text;
        }
    },
    formatRepo: function(repo) {
        if (repo.loading) return "Buscando...";
        var markup =
            "<div>" +
            "<div>" +
            repo.codigo +
            " - " +
            repo.descricao +
            "</div>" +
            "</div>";
        markup += "</div></div>";
        return markup;
    },

    formatRepoSelection: function(repo) {
        if (repo.codigo) {
            return repo.codigo + " - " + repo.descricao;
        } else if (repo.descricao) {
            return repo.descricao;
        } else {
            return repo.text;
        }
    }
};

const adicionaSelect2ClienteInterno = function() {
    $("#select-cliente-interno").select2({
        width: "100%",
        language: "pt-BR",
        allowClear: true,
        placeholder: "Digite a descrição para pesquisar",
        ajax: {
            url:
                "/assistencia-tecnica/atendimento/ajax/retorna-clientes-consulta",
            dataType: "json",
            type: "GET",
            delay: 250,
            data: function(params) {
                return {
                    codigoOuDescricao: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.items
                };
            },
            cache: true
        },
        escapeMarkup: function(markup) {
            return markup;
        },
        minimumInputLength: 2,
        templateResult: pagamentoEfetuado.formatRepo,
        templateSelection: pagamentoEfetuado.formatRepoSelection
    });
};

const adicionaSelect2Usuarios = function() {

    $('#select-criado-por').select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite o usuário ou nome do usuário para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-usuarios-consulta-select2',
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
        templateResult: pagamentoEfetuado.formatRepoUsuarios,
        templateSelection: pagamentoEfetuado.formatRepoUsuariosSelection
    });
};


const adicionaEventoClick = () => {

    $(".btn-autorizar").on("click", function() {

        var pagamento_id = $(this).data('id');

        $.confirm({
            title: "Autorizar Pagamento",
            columnClass: "col-md-8 col-md-offset-2",
            content: 'Você tem certeza que deseja Autorizar o Pagamento?' ,
            buttons: {
                formSubmit: {
                    text: "Confirmar",
                    btnClass: "btn-green",
                    action: function() {
                        $.ajax({
                            type: "POST",
                            url: '/assistencia-externa/pagamento-efetuado/autorizar',
                            data: {pagamento_id},
                            dataType: "json",
                            success: function(data, textStatus, jqXHR) {
                                if (data.retorno) {
                                   $.alert({
                                        title: 'Autorizar Pagamento!',
                                        content: 'Pagamento Autorizado com Sucesso.',
                                    });
                                    window.location.reload();
                                } else {
                                    $.dialog({
                                        title: "Atenção",
                                        content:
                                            "Ocorreu um Erro! Tente Novamente Mais Tarde!"
                                    });
                                   // window.location.reload();
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

const selecionaTodosCheckbox = function() {
    $(".check_all_sel_print").on("click", function() {
        if ($(this).is(":checked")) {
            $('.checkboxes').prop("checked", true);
        } else  {
            $('.checkboxes').prop("checked", false);
        }
    })
};

const adicionaAnexos = function() {

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


const openModal = function() {
    $(document).on("click", ".open-modal", function () {
        console.log('aaa');
         var pagamento_id = $(this).data('id');
         $("#pagamento_id").val( pagamento_id );
    });
};


const openModalAnexos = function() {
    $(document).on("click", ".open-modal-anexos", function () {
        var pagamento_id = $(this).data('id');

        $.ajax({
            type: "GET",
            url: '/assistencia-externa/pagamento-efetuado/retorna-anexos',
            data: {'pagamento_id':pagamento_id},
            dataType: "json",
            success: function(data, textStatus, jqXHR) {
                var html = '';
                if (data.arquivos) {
                   $.each(data.arquivos, function(k, v) {
                       html += '<tr>';
                       html += '<td>'+v.arquivo+'</td>';
                       html += '<td>';
                       html += '<a class="text-inverse"';
                       html += 'href="/assistencia-externa/pagamento-efetuado/download/'+v.link+'"';
                       html += 'target="_blank" data-original-title="Download">';
                       html += '<i class="ti-download"></i>';
                       html += '</a>';
                       html += '</td>';
                       html += '</tr>';
                    });

                    $('#body-arquivos').html(html);

                } else {
                    $.dialog({
                        title: "Atenção",
                        content:
                            "Ocorreu um Erro! Tente Novamente Mais Tarde!"
                    });
                }
            }
        });


    });
};




$(document).ready(function() {
    pagamentoEfetuado.onReady();
});
