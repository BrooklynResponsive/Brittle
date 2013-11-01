<?

define("DOCUMENT_ROOT",str_replace("/includes/config.php","", __FILE__));

if( strpos( dirname(__FILE__), "/dev") !== false) {
			define("WEBSITE","http://www.brittlebarn.com/dev");
			define("BB_DB_NAME", "multi4_barn_dev");	
			define("BB_DB_SERVER", "localhost");
			define("BB_DB_USER", "multi4_barnuser");	
			define("BB_DB_PASSWORD", "br1tt13");
			define("BB_DEBUG",true);
			define("BB_ERROR_LOGGING",false);
			define("EMAIL_DIVERT", true);
			$MEMCACHED_SERVER = array("localhost");
		}else{
			define("WEBSITE","http://www.brittlebarn.com/");
			define("BB_DB_NAME", "multi4_barn");	
			define("BB_DB_SERVER", "localhost");
			define("BB_DB_USER", "multi4_barnuser");	
			define("BB_DB_PASSWORD", "br1tt13");
			define("BB_DEBUG",false);
			define("BB_ERROR_LOGGING",false);
			define("EMAIL_DIVERT", false);
			$MEMCACHED_SERVER = array("localhost");
		}


require(DOCUMENT_ROOT."/includes/db.php");
require(DOCUMENT_ROOT."/includes/fake_db.inc");
require(DOCUMENT_ROOT."/includes/cart.class.php");


/*
$DB=new BB_DB();
print_r($DB);
*/
		
?>