<?php
require('../includes/config.php');
error_reporting(0);
$order_id = mysql_real_escape_string($_POST['order_id']);
$token = mysql_real_escape_string($_POST['token']);
$customer_id = mysql_real_escape_string($_POST['customer_id']);

$error = "";
$user = new BB_User();
$user->readMe($customer_id);
$stripe_customer_id = $user->stripe_subscribe($token, $error);
$success = false;

if($stripe_customer_id != "false")
{
    $order = new BB_Order($order_id);
    $success = $order->process($stripe_customer_id, $error);
}

echo json_encode(Array("success" => $success, "error" => $error, "stripe_id" => $stripe_customer_id));
?>
