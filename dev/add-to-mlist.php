<?

require("includes/config.php");

$email=$_POST['email'];

if($email==""){

echo("0|no address entered.");
exit();
	
}elseif(!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$/i", $email)){
	
echo("0|address appears to be invalid");
exit();
	
}else{

$DB->q("REPLACE INTO mlist (email, added) values ( '".mysql_real_escape_string($email)."' ,DEFAULT)");
echo("1|Thanks! We'll be in touch.");
	
}

?>