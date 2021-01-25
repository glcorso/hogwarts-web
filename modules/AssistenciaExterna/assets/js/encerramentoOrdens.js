encerramentoOrdens = {
    onReady: function() {
        adicionaEventoClick();
        adicionaSelect2ClienteInterno();
        adicionaSelect2Usuarios();
    },
    formatRepoUsuarios: function(repo) {
        if (repo.loading) return "Buscando...";
        var markup =
            "<div>" +
            "<div>" +
            repo.nome +
            " - " +
            repo.usuario +
            "</div>" +
            "</div>";
        markup += "</div></div>";
        return markup;
    },

    formatRepoUsuariosSelection: function(repo) {
        if (repo.nome) {
            return repo.nome + " - " + repo.usuario;
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
        templateResult: encerramentoOrdens.formatRepo,
        templateSelection: encerramentoOrdens.formatRepoSelection
    });
};

const adicionaSelect2Usuarios = function() {
    $("#select-criado-por").select2({
        width: "100%",
        language: "pt-BR",
        allowClear: true,
        placeholder: "Digite o usuário ou nome do usuário para pesquisar",
        ajax: {
            url:
                "/assistencia-tecnica/atendimento/ajax/retorna-usuarios-consulta-select2",
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
        templateResult: encerramentoOrdens.formatRepoUsuarios,
        templateSelection: encerramentoOrdens.formatRepoUsuariosSelection
    });
};

const adicionaEventoClick = () => {
    let ids = [];
    $("#btn-finalizar").on("click", function() {
        ids = [];
        $(document)
            .find("tbody > tr")
            .each(function(index, $element) {
                var $checkbox = $($element).find(
                    "input[name='check_sel_print']"
                );

                if ($checkbox.is(":checked")) {
                    ids.push($checkbox.val());
                }
            });

        if (ids.length) {
            $(this).text("Finalizando");
            $.ajax({
                type: "POST",
                url: "/assistencia-externa/encerramento-ordens",
                data: { ids: ids },
                dataType: "json",
                success: function(data, textStatus, jqXHR) {
                    if (data) {
                        $.alert({
                            title: "Sucesso!",
                            content: "Ordens Finalizadas com Sucesso!",
                            onClose: function() {
                                location.reload(true);
                            }
                        });
                        $("#btn-finalizar").reset("Finalizando");
                    } else {
                        $.alert({
                            title: "Erro!",
                            content: "Ocorreram erros, tente novamente!"
                        });
                    }
                }
            });
        }
    });
};

$(document).ready(function() {
    encerramentoOrdens.onReady();
});
