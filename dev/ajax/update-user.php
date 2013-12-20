<?php
require('../includes/config.php');
error_reporting(0);
$fields = Array('Email', 'FirstName', 'LastName', 'Address1', 'Address2', 'City', 'State', 'Zipcode', 'Phone', 'ExpMonth', 'ExpYear', 'LastFour');
$db_fields = Array('email', 'fname', 'lname', 'address1', 'address2', 'city', 'state', 'zip', 'phone', 'exp_month', 'exp_year', 'last_four');

$user = new BB_User(mysql_real_escape_string($_POST['Id']));
$user->readMe(mysql_real_escape_string($_POST['Id']));
$success = true;
$user_array = Array();  //this is to hand back to update the cookie

$Email = mysql_real_escape_string($_POST['Email']);
$Password = mysql_real_escape_string($_POST['Password']);

if(BB_User::hash_password($Password, $Email) != $user->val('password'))
{
    echo json_encode(Array("success"=>false, "error"=>"Incorrect password"));
    return;
}

if(isset($_POST['token']))
{
    $stripe_customer_id = $user->stripe_subscribe(isset($_POST['token'], $error));
}

// doing this now so it overwrites the values in the db
for($i=0; $i < count($db_fields); $i++)
{
    $user->val($db_fields[$i], mysql_real_escape_string($_POST[$fields[$i]]));
    $user_array[$db_fields[$i]] = $_POST[$fields[$i]];
}

if(isset($_POST['NewPassword']))
{
    $user->val('password', BB_User::hash_password(mysql_real_escape_string($_POST['NewPassword']), mysql_real_escape_string($_POST['Email'])));
}

if(!$user->writeMe())
{
    $success = false;
    $user_array = Array();  //dont hand any info back
}
else {
    $success = true;
}

$user_array['id'] = $user->id;

echo json_encode(Array('success' => $success, 'cc_update' => $stripe_customer_id, 'user_id'=> $user->id, 'user' => $user_array, 'error' => $error));
?>
