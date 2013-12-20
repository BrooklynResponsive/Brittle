<?php
require('../includes/config.php');

$email = mysql_real_escape_string($_POST['login']);
$pass = mysql_real_escape_string($_POST['passwd']);

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
}
else if(mysql_num_rows($res) >1)
{
    $return_object['success'] = false;
    $return_object['user'] = Array();
    $return_object['error'] = $sql_statement;
    $return_object['error'] = "Database error. Please contact an administrator.";
}
else
{
    $return_object['success'] = false;
    $return_object['user'] = Array();
    $return_object['error'] = $sql_statement;
    $return_object['error'] = "Invalid email/password combination.";
}

$return_object['email'] = $email;


echo json_encode($return_object);

?>
