<?php
require('../includes/config.php');

$id = mysql_real_escape_string($_POST['id']);
$tracking_number = mysql_real_escape_string($_POST['tracking']);

$DB->q("update orders set tracking_number='$tracking_number' where id=$id");

echo json_encode(Array("success" => true));
?>
