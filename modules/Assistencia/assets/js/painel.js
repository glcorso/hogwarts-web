painelJs = {
    onReady: function() {
     /*   window.scrollTo(0, 0);
        divScroll();
        setTimeout(function() {
       //     window.location.reload(1);
        }, 30000);
    */
    }
};

var divScroll = function() {
   /* time = 30000;
    $("html, body").animate({ scrollTop: $(document).height() }, 45000, function() { 
           // setTimeout(function() {
                location.reload();
           // }, time)
    });*/

    $("html, body").animate({ scrollTop: $(document).height() }, 10000);
    setTimeout(function() {
       $('html, body').animate({scrollTop:0}, 10000); 
    },10000);
    setInterval(function(){
         // 10000 - it will take 4 secound in total from the top of the page to the bottom
    $("html, body").animate({ scrollTop: $(document).height() }, 10000);
    setTimeout(function() {
       $('html, body').animate({scrollTop:0}, 10000); 
    },10000);
        
    },8000);


};


$(document).ready(function() {
    painelJs.onReady();
});

