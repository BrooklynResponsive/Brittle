<?

require("includes/config.php");


$ml = $DB->q("SELECT * FROM mlist");

while($t=mysql_fetch_object($ml)){

echo("$t->email\n");	
	
}


?>