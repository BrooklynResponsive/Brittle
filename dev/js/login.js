
$(document).ready(function(){
    
   $("#brittleLoginButton").on('click', function()
   {
        var login = $("#brittleLoginEmail").val(), passwd = $("#brittleLoginPassword").val();
        $("#login-error").fadeOut();

        $.post('ajax/login-user.php', { login : login, passwd : passwd }, function(response) {
            if(response['success'])
            {
                var user = new brittleUser(), referer = $("#referer").val();
                user.attrs = response['user'];
                user.saveUser();
                document.location = '/' + referer;
            }
            else {
                    $("#login-error").html(response['error']);
                    $("#login-error").fadeIn("slow");
            }
        }, 'json');
   });
});
