function checkPasswordMatch() {
    var password = $("#txtNewPassword").val();
    var confirmPassword = $("#txtConfirmPassword").val();

    if (password != confirmPassword) {
        $("#divCheckPasswordMatch").show();
        $("#buttonok").prop( "disabled", true );
    }
    else{
        $("#divCheckPasswordMatch").hide();
        $("#buttonok").prop( "disabled", false );
    }
}

function verifyPassword() {
    var password = $("#txtNewPassword").val();
    var confirmPassword = $("#txtConfirmPassword").val();

    if (password != confirmPassword) {
        $("#divCheckPasswordMatch").show();
        $('.form').on('submit', function(e){
         	e.preventDefault(); // Cancel the submit
        });
    }else{
    	$('.form').unbind().submit();
    }
}

$(document).ready(function () {
   $("#txtConfirmPassword").keyup(checkPasswordMatch);
   $("#buttonok").on("click", verifyPassword);
});