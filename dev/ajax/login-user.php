<?php
require('../includes/config.php');

$email = mysql_real_escape_string($_POST['login']);
$pass = mysql_real_escape_string($_POST['passwd']);
$admin = mysql_real_escape_string($_POST['admin']);

if( isset($_POST['admin']))
{
    if($_POST['admin'] == 1)
    {
        if($email == $ADMIN_USER_EMAIL && $pass == $ADMIN_USER_PASS)
        {
            echo json_encode(Array("success" => true, "admin" => true));
        }
        else
        {
            echo json_encode(Array("success" => false, "admin" => false, "error" => "Wrong!"));
        }

        exit();
    }
}

$pass = BB_User::hash_password($pass, $email);
$email = mysql_real_escape_string($email);
$sql_statement = "select * from customers where email='" . $email . "' and password='" . $pass . "'";

$res = $DB->q($sql_statement);
$return_object = Array();

if(mysql_num_rows($res) == 1)
{
    $attrs = mysql_fetch_assoc($res);
    $return_object['success'] = true;
    $return_object['user'] = $attrs;

    // black out senstive fields
    $return_object['password'] = "";
    $return_object["stripe_customer_id"] = "";  //this is useless to anyone who doesnt have our secret key but might as well be safe
}
else if(mysql_num_rows($res) >1)
{
    $return_object['success'] = false;
    $return_object['user'] = Array();
    $return_object['error'] = "Database error. Please contact an administrator.";
}
else
{
    $return_object['success'] = false;
    $return_object['user'] = Array();
    $return_object['error'] = "Invalid email/password combination.";
}

$return_object['email'] = $email;
$return_object['admin'] = false;    //just in case some tries to get sneaky

echo json_encode($return_object);

?>
