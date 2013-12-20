<?php
    
require('../includes/config.php');

$ids = $_GET['ids'];
$id_string = mysql_real_escape_string(implode(',', $ids));

$res = $DB->q("select id, price from products where id in ($id_string)");

$return_obj = array();
while($obj = mysql_fetch_object($res))
{
    $return_obj[$obj->id] = $obj;
}

echo json_encode($return_obj);
?>
