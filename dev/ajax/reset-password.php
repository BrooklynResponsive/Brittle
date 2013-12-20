<?php
require('../includes/config.php');

$id = mysql_real_escape_string($_POST['id']);

//generate new passwd
$new_pass = BB_User::generate_random_password();

//get user email
$user = new BB_User($id);
$user->readMe($id);
$email = $user->email;

//email user here

// update db


$DB->q("update customers set password='" . BB_User::hash_password($new_pass, $email) . "' where id=$id");

echo json_encode(Array("success" => true, "email" => $email, "passwd" => $new_pass));
?>
