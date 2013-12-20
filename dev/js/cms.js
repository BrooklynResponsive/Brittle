var logged_in = false;

$(document).ready(function(){
   $("#brittleCMSLoginButton").on('click', function()
   {
        var login = $("#brittleCMSLoginEmail").val(), passwd = $("#brittleCMSLoginPassword").val();
        $("#cms-login-error").fadeOut();

        $.post('../ajax/login-user.php', { login : login, passwd : passwd, admin: 1 }, function(response) {
            if(response['success'] && response['admin'])
            {
                logged_in = true; 
                $("#cmsLogin").hide();
                $("#cmsWelcome").show();
            }
            else {
                $("#cms-login-error").html(response['error']);
                $("#cms-login-error").fadeIn("slow");
            }
        }, 'json');
    });

    console.log("hiding everything");
    $("#cmsWelcome").hide();
    $("#cmsCustomers").hide();
    $("#cmsOrders").hide();
    $("#cmsLogin").show();

    $(".cmsMenuOption").on('click', function() {
        if(logged_in)
        {
            $(".cmsMenuItem").hide();
            $("#" + $(this).attr('id').slice(0,-4)).show();
        }
    });

    $(".resetUserPasswd").on('click', function() {
        $.post('../ajax/reset-password.php', { id : $(this).attr('id') }, function(response) {
            if(response['success'])
            {
                alert("New password created and sent to user.");
            }
            else
            {
                alert("There's a problem, time to hire frank again?");
            }
        }, 'json');
    })
    
    $(".orderTrackingNumber").on('blur', function() {
        $.post('../ajax/update-tracking.php', { id : $(this).attr('id').slice(8), tracking : $(this).val() }, function(response) {
            if(response['success'])
            {
                console.log("tracking number updated");
            }
            else
            {
                alert("There's a problem, time to hire frank again?");
            }
        }, 'json');
    })
});
