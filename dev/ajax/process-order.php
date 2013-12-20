<?php
require('../includes/config.php');
error_reporting(0);
$fields = Array('Email', 'FirstName', 'LastName', 'Address1', 'Address2', 'City', 'State', 'Zipcode', 'Phone', 'ExpMonth', 'ExpYear', 'LastFour', 'New', 'Stripe', 'Tax', 'Subtotal', 'Shipping');
$db_fields = Array('email', 'fname', 'lname', 'address1', 'address2', 'city', 'state', 'zip', 'phone', 'exp_month', 'exp_year', 'last_four');


if(isset($_POST['id']))
{
    $user = new BB_User(mysql_real_escape_string($_POST['id']));
}
else
{
    $user = new BB_User();
}

$user->val('email', mysql_real_escape_string($_POST['Email']));
$user->readMe();

for($i=0; $i < count($fields); $i++)
{
    $$fields[$i] = mysql_real_escape_string($_POST[$fields[$i]]); 
}

if($New == "true")
{
    //registration
    $Password = mysql_real_escape_string($_POST['Password']);
    $Email = mysql_real_escape_string($_POST['Email']);
    $pass_hash = BB_User::hash_password($Password, $Email);
    if($user->val("password") == null)
    {
        $user->val("password", $pass_hash);
    }
    else {
        $error = "User not created, email already exists";
    }
}
else if($Stripe == "true")
{
    //in this case we care about the password
    $user->readMe($user->id);
    if(BB_User::hash_password($Password, $Email) != $user->val('password'))
    {
        echo json_encode(Array("success"=>false, "error"=>"Incorrect password"));
        return;
    }
}
else
{
    $user->readMe($user->id);
}

// doing this now so it overwrites the values in the db
for($i=0; $i < count($db_fields); $i++)
{
    $user->val($db_fields[$i], mysql_real_escape_string($_POST[$fields[$i]]));
}

// if this not a registrion we need a dummy user, so write, if it is, we need to write, if it's existing, we want to update the info, so write
if(!$user->writeMe())
{
    echo json_encode(Array("success"=>false, "error"=>"Error updating user info"));
    return;
}
else {
    $success = true;
    $user_id = $user->id;
}

//finished with user stuff, now on to order stuff
//everything should be in cents...
$Tax = 100*$Tax;
$Shipping = 100*$Shipping;
$Subtotal = 100*$Subtotal;
$Total = $Shipping + $Tax + $Subtotal;

// create order
$order = new BB_Order();
$order->add_items($_POST['Items']);
$order->val('customer_id', $user->id);
$order->val('status', 'PROCESS');
$order->val('shipping',$Shipping);
$order->val('tax',$Tax);
$order->val('total',$Total);
$order->writeMe();


if($Stripe == "true" && $user->stripe_customer_id != Null)
{
    //now, if they have a strip customer id with us, we can just charge the card
    $charged = $order->process($user->stripe_customer_id, $error);
}
else
{
    $charged = false;
}

echo json_encode(Array('success' => $success, 'charged' => $charged, 'user_id'=> $user_id, 'order_id' => $order->id, 'error' => $error, "new" => $New));
?>
