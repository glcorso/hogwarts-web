cadastroJs = {
    onReady: function() {

        $('.btn-delete-modal').on('click', function() {
            var element = $(this);
            mainJs.prepareModalDelete(element);
        });

        uploadFiles();
        deletaArquivos();

        $('.setor').select2({'width':'100%'});
    },

};

var uploadFiles = function () {
    $("#upload").fileinput({
        language: 'pt-BR',
        uploadUrl: '#', // não informar a url pois é via submit
        overwriteInitial: false,
        maxFileSize: 10000,
        maxFilesNum: 1,
        showUpload: false,
        dropZoneEnabled: false,
        slugCallback: function(filename) {
            $('.kv-file-upload').remove();
            $('.kv-file-remove').html('<i class="fa fa-close text-danger m-r-10"></i>');
            return filename.replace('(', '_').replace(']', '_');
        },

    });
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




$(document).ready(function(){
   cadastroJs.onReady();
});

$(function(){
    $('.redactor-avisos').redactor({
        buttons: ['bold', 'italic', 'deleted', 'unorderedlist', 'orderedlist', 'outdent', 'indent', 'horizontalrule'],
        minHeight: 240,
    });
});