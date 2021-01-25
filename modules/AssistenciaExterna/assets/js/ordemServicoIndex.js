ordemServicoIndexJs = {
    onReady: function() {
        adicionaSelect2ClienteInterno();
        adicionaSelect2Usuarios();
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

var adicionaSelect2ClienteInterno = function() {

    $('#select-cliente-interno').select2({
        width: '100%',
        language: 'pt-BR',
        allowClear: true,
        placeholder: 'Digite a descrição para pesquisar',
        ajax: {
            url: '/assistencia-tecnica/atendimento/ajax/retorna-clientes-consulta',
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
        templateResult: ordemServicoIndexJs.formatRepo,
        templateSelection: ordemServicoIndexJs.formatRepoSelection

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
        templateResult: ordemServicoIndexJs.formatRepoUsuarios,
        templateSelection: ordemServicoIndexJs.formatRepoUsuariosSelection
    });
};

$(document).ready(function(){
    ordemServicoIndexJs.onReady();
});
