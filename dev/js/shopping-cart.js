var cart;
var order_id;
var customer_id;

$(document).ready(function(){
    $("#shoppingCartCheckoutButton").on('click', Checkout)

    cart = new brittleCart();

    cart.verifyPrices('ajax/verify-product-price.php', 'PopulateCheckout')
    $("#shoppingCartZipcode").on('keyup', CheckZipcode); 

    //if we have a user, prepopulate form
    brittlebarn.user = new brittleUser();
    if(brittlebarn.user.attrs['id'])
    {
        PrepopulateForm();
    }
});

function PrepopulateForm()
{
    var fields = Array('Email', 'Address1', 'Address2', 'City', 'State', 'Phone');
   
    for( var i=0; i<fields.length; i++)
    {
        console.log(fields[i] +"=  "+brittlebarn.user.attrs[fields[i].toLowerCase()]);
        $("#shoppingCart" + fields[i]).val(brittlebarn.user.attrs[fields[i].toLowerCase()]);
    }

    //exceptions, should have coded this to be consistent :(
    $("#shoppingCartFirstName").val(brittlebarn.user.attrs['fname']);
    $("#shoppingCartLastName").val(brittlebarn.user.attrs['lname']);
    $("#shoppingCartLoginEmail").val(brittlebarn.user.attrs['email']);

    if(brittlebarn.user.attrs['zip'])
    {
        $("#shoppingCartZipcode").val(brittlebarn.user.attrs['zip']);
        populateShipping(brittlebarn.user.attrs['zip']);
    }

    if(brittlebarn.user.attrs['stripe_customer_id'])
    {
        $("#shoppingCartExpYear").val(brittlebarn.user.attrs['exp_year']);
        $("#shoppingCartExpMonth").val(brittlebarn.user.attrs['exp_month']);
        $("#shoppingCartCreditNumber").val("************"+brittlebarn.user.attrs['last_four']); //twelve stars, for amex should be 11 but who is counting
    }

    $("h1#shoppingCartAccountHeading").text("Login Info");
    $("#shoppingCartRegisterPane").hide();
    $("#shoppingCartLoginPane").show();
}

function CheckZipcode()
{
    //only care about exactly 5 digits
    if($("#shoppingCartZipcode").val().length == 5)
    {
        var zip = $("#shoppingCartZipcode").val();
        PopulateShipping(zip);
    }
}

function PopulateShipping(zip)
{

        var usps_xml = ConstructUSPSXml(zip);
        /*
        $.get('http://production.shippingapis.com/ShippingAPI.dll', { 'API' : 'RateV4', 'XML' : usps_xml }, function(response) {
            //not yet tested as we have to be coming from brittlebarn.com...
            var shipping = response['RateV4Response']['Package']['Postage']['Rate'];
            cart.setShipping(shipping);
            $("#shippingCostTD").html('$' + shipping);
       
        }, 'xml');
        */

        //delete this later
        var shipping = "5.00";
        cart.setShipping(shipping);
        $("#shippingCostTD").html('$' + parseFloat(shipping).toFixed(2));
        $("#shoppingCartTotal").html('$' + OrderTotal());
}

function ConstructUSPSXml(zip)
{
    var weight = cart.estimateWeight();
 
    var xml = '<RateV4Request USERID="760CONRY4803"><Revision/><Package ID="1ST"><Service>PRIORITY</Service><ZipOrigination>11201</ZipOrigination><ZipDestination>' + zip + '</ZipDestination><Pounds>'+weight['pounds']+'</Pounds><Ounces>'+weight['ounces']+'</Ounces><Container>RECTANGULAR</Container><Size>REGULAR</Size><Width>6</Width><Length>12</Length><Height>3</Height></Package></RateV4Request>';

    return xml;
}

function PlaceOrder(status, response)
{
    if (response.error) {
        alert("There was a problem processing your card, please try again.\n" + response.error.message);
        return;
    }
    
    var token = response.id;
   
    $.post("ajax/complete-order.php", { order_id : order_id, customer_id : customer_id, token : token }, function(response) {

        if(response['success'] == true)
        {
            cart = new brittleCart();
            cart.clearCart();
            document.location = '/payment-success.php';
        }
        else
        {
            alert("There was problem with your payment, please try again.");
        }
    }, 'json');

    //for good measure, forget these
    customer_id = "";
    order_id = "";
}

function getCardToken()
{
        var $form = $('#payment-form');
        console.log('calling createToken');
        Stripe.card.createToken($form, PlaceOrder); 

        // Prevent the form from submitting with the default action
       return false;
}

function Checkout()
{

    validate($("#shoppingCartDiv:input"), 'CreateOrder');

}

function CreateOrder()
{
    
    var items = cart.getItems(); 

    if(Object.keys(items).length == 0)
    {
        alert("Your cart is empty!\nPlease add some items to your cart before checking out!");
    }

    var fields = Array('Email', 'FirstName', 'LastName', 'Address1', 'Address2', 'City', 'State', 'Zipcode', 'Phone', 'ExpYear', 'ExpMonth');
    var ajax_params = { 'Subtotal' : cart.getSubtotal(), 'Shipping' : cart.getShipping(), 'Tax' : cart.getTax() };

    for( var i=0; i<fields.length; i++)
    {
        ajax_params[fields[i]] = $("#shoppingCart" + fields[i]).val();
    }

    //last four is kind of special
    ajax_params['LastFour'] = $("#shoppingCartCreditNumber").val().slice(-4);

    var user = new brittleUser();

    //two things we need to know, if it is a new user registration
    ajax_params['New'] = false;     //new user reg
    ajax_params['Stripe'] = false;  //existing user stripe

    if(brittlebarn.user.attrs['id'])
    {
        //existing user check if old credit card used
        if($("#shoppingCartCreditNumber").val().slice(11) == "***********")
        {
            ajax_params['Stripe'] = true;
            ajax_params['User'] = brittlebarn.user.attrs['id'];
            ajax_params['Password'] = $("#shoppingCartLoginPassword").val();
        }
        
    }
    else
    {
        if($("#shoppingCartCreatePassword").val().length > 0 )
        {
            if($("#shoppingCartCreatePassword").val() != $("#shoppingCartCreatePasswordConfirm").val())
            {
                alert("Your passwords don't match, please try again.");
                $("#shoppingCartCreatePassword").val("");
                $("#shoppingCartCreatePasswordConfirm").val("");
                return;
            }
            else
            {
                ajax_params['New'] = true;
                ajax_params['Password'] = $("#shoppingCartCreatePassword").val();
            }
        }
    }
    
    ajax_params['Items'] = items;

    $.post("ajax/process-order.php", ajax_params, function(response) {
        order_id = response['order_id'];
        customer_id = response['user_id'];

        if(response['success'] != true)
        {
            alert("There was a problem processing your order. Please try again.\nIf the problem persists please contact an administrator.");
        }
        else if(!response['charged'])
        {
            //then we need a token
            getCardToken();
        }
        else
        {
            cart = new brittleCart();
            cart.clearCart();
            document.location = '/payment-success.php';
        }
    }, 'json');
}	

function PopulateCheckout(items)
{
    var html = '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="shopping-cart">';
    var subtotal = 0;

    for( var item_id in items )
    {
        subtotal += Number(items[item_id]['total']);
        var item = items[item_id]['item'];
        html += '<tr><td width="5%">' + items[item_id]['count'] + '</td><td width="85%">' + item['name'] + '<td><td width="10%" class="green">$' + items[item_id]['total'] +'</td></tr>';
    }

    var tax = CheckoutTax(subtotal), shipping = cart.getShipping(), total;

    //keep track of this stiuff in the cart
    cart.setSubtotal(subtotal);
    cart.setShipping(shipping);
    cart.setTax(tax);
    
    //now we can get the total
    total = OrderTotal();

    html += '<tr><td width="5%"></td><td width="85%">Subtotal<td><td width="10%" class="green">$' + subtotal.toFixed(2) +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Tax<td><td width="10%" class="green">$' + tax  +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Shipping<td><td width="10%" id="shippingCostTD" class="green">$' + shipping +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Total<td><td width="10%" id="shoppingCartTotal" class="green">$' + total +'</td></tr>';
    html += '</table>';

    $("#shoppingCartInfo").html(html);

}

function OrderTotal()
{
    var subtotal = parseFloat(cart.getSubtotal()), shipping = parseFloat(cart.getShipping()), tax = parseFloat(cart.getTax());

    if(!isNaN(subtotal+shipping+tax))
    {
        total = (subtotal+shipping+tax);
        total.toFixed(2);
        cart.setTotal(total);
    }
    else
    {  
        total = 'TBD';
    }
    
//    total = String(subtotal)+String(shipping)+String(tax);
    return total;
}

function CheckoutTax(subtotal)
{
    var tax = Number(subtotal * tax_rate).toFixed(2);
    cart.setTax(tax);
    return tax;
}

function PopulateCart(items)
{
    var subtotal = 0;

    for( var item_id in items )
    {
        subtotal += Number(items[item_id]['total']);
        var item = items[item_id]['item'];
        html += '<tr><td width="5%">' + items[item_id]['count'] + '</td><td width="85%">' + item['name'] + '<td><td width="10%" class="green">$' + items[item_id]['total'] +'</td></tr>';
    }

    var tax = CheckoutTax(subtotal), shipping = cart.getShipping(), total;

    //keep track of this stiuff in the cart
    cart.setSubtotal(subtotal);
    cart.setShipping(shipping);
    cart.setTax(tax);
    
    //now we can get the total
    total = OrderTotal();

    html += '<tr><td width="5%"></td><td width="85%">Subtotal<td><td width="10%" class="green">$' + subtotal.toFixed(2) +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Tax<td><td width="10%" class="green">$' + tax  +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Shipping<td><td width="10%" id="shippingCostTD" class="green">$' + shipping +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Total<td><td width="10%" id="shoppingCartTotal" class="green">$' + total +'</td></tr>';
    html += '</table>';

    $("#shoppingCartInfo").html(html);

}

