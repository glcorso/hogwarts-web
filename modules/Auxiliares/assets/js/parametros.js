parametrosJs = {
    onReady: function() {
        $('.select2').select2({
            tags: true,
            width: '100%'
        });
    }
};

$(document).ready(function(){
    parametrosJs.onReady();
});
