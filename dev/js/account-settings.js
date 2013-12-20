$(document).ready(function(){
    $("#accountSettingsSaveButton").on('click', saveUserInfo)

    prepopulateForm();
});

function prepopulateForm()
{
    var fields = Array('Email', 'Address1', 'Address2', 'City', 'State', 'Phone');
   
    for( var i=0; i<fields.length; i++)
    {
        $("#accountSettings" + fields[i]).val(brittlebarn.user.attrs[fields[i].toLowerCase()]);
    }

    //exceptions, should have coded this to be consistent :(
    $("#accountSettingsFirstName").val(brittlebarn.user.attrs['fname']);
    $("#accountSettingsLastName").val(brittlebarn.user.attrs['lname']);
    $("#accountSettingsZipcode").val(brittlebarn.user.attrs['zip']);

    if(brittlebarn.user.attrs['stripe_customer_id'])
    {
        $("#accountSettingsExpYear").val(brittlebarn.user.attrs['exp_year']);
        $("#accountSettingsExpMonth").val(brittlebarn.user.attrs['exp_month']);
        $("#accountSettingsCVC").val("****");
        $("#accountSettingsCreditNumber").val("************"+brittlebarn.user.attrs['last_four']); //twelve stars, for amex should be 11 but who is counting
    }
}

function saveUserInfo()
{
    //validate form

    if($("#accountSettingsCreditNumber").val().indexOf('*') == -1)
    {
        validate($("#accountSettingsDiv :input"), 'getUserToken');
    }
    else
    {
        validate($("#accountSettingsDiv :input"), 'updateUserInfo');
    }
}

function updateUserInfo()
{
    var ajax_params = getAjaxParams();

    $.post("ajax/update-user.php", ajax_params, function(response) {
        if(response['success'] == true)
        {
            alert("Update successfully saved!");
            //update cookie
            var user = new brittleUser(), referer = $("#referer").val();
            user.attrs = response['user'];
            user.saveUser();
        }
        else
        {
            //show error message
            $("#accountSettingsError").html(response['error']);
            $("#accountSettingsError").fadeIn('slow');
        }

        //wipe password fields
        $("#accountSettingsCreatePassword").val('');
        $("#accountSettingsCreatePasswordConfirm").val('');
        $("#accountSettingsCurrentPassword").val('');

    }, 'json');
}

function getUserCardToken()
{
        var $form = $('#payment-form');
        Stripe.card.createToken($form, saveCCInfo); 

        // Prevent the form from submitting with the default action
       return false;
}

function saveCCInfo(status, response)
{
    if (response.error) {
        alert("There was a problem processing your card, please try again.\n" + response.error.message);
        return;
    }
    
    var token = response.id;
    var ajax_params = getAjaxParams();

    ajax_params['token'] = token;
    
    $.post("ajax/update-user.php", ajax_params, function(response) {
        if(response['success'] == true)
        {
            alert("Update successfully saved!");
        }
    }, 'json');
}

function getAjaxParams()
{
    var ajax_params = {}, user = new brittleUser();
    var fields = Array('Email', 'FirstName', 'LastName', 'Address1', 'Address2', 'City', 'State', 'Zipcode', 'Phone');

    for( var i=0; i<fields.length; i++)
    {
        ajax_params[fields[i]] = $("#accountSettings" + fields[i]).val();
    }

    //last four is kind of special
    ajax_params['LastFour'] = $("#accountSettingsCreditNumber").val().slice(-4);
    ajax_params['Id'] = user.attrs['id'];


    if($("#accountSettingsCreatePassword").val().length > 0 )
    {
        if($("#accountSettingsCreatePassword").val() != $("#accountSettingsCreatePasswordConfirm").val())
        {
            alert("Your passwords don't match, please try again.");
            $("#accountSettingsCreatePassword").val("");
            $("#accountSettingsCreatePasswordConfirm").val("");
            return;
        }
        else
        {
            ajax_params['NewPassword'] = $("#accountSettingsCreatePassword").val();
        }
    }

    ajax_params['Password'] = $("#accountSettingsCurrentPassword").val();
    
    return ajax_params;
}

