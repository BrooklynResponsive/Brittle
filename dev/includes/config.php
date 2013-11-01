<?

mb_internal_encoding("UTF-8");
header('Content-Type: text/html; charset=UTF-8');

define("DOCUMENT_ROOT",str_replace("/includes/config.php","", __FILE__));

if( strpos( dirname(__FILE__), "/dev") !== false) {
			
			 ini_set("display_errors", "On");
			 ini_set("error_reporting",E_ALL);
			define("WEBSITE","http://www.brittlebarn.com/dev");
			define("BB_DB_NAME", "multil4_barn_dev");	
			define("BB_DB_SERVER", "localhost");
			define("BB_DB_USER", "multil4_barnuser");	
			define("BB_DB_PASSWORD", "br1tt13");
			define("BB_DEBUG",true);
			define("BB_ERROR_LOGGING",false);
			define("EMAIL_DIVERT", true);
			$MEMCACHED_SERVER = array("localhost");
		}else{
			ini_set('display_errors','Off');
			define("WEBSITE","http://www.brittlebarn.com/");
			define("BB_DB_NAME", "multil4_barn");	
			define("BB_DB_SERVER", "localhost");
			define("BB_DB_USER", "multil4_barnuser");	
			define("BB_DB_PASSWORD", "br1tt13");
			define("BB_DEBUG",false);
			define("BB_ERROR_LOGGING",false);
			define("EMAIL_DIVERT", false);
			$MEMCACHED_SERVER = array("localhost");
		}


require(DOCUMENT_ROOT."/includes/db.php");
require(DOCUMENT_ROOT."/includes/BB_Error.class.php");
require(DOCUMENT_ROOT."/includes/fake_db.inc");
require(DOCUMENT_ROOT."/includes/cart.class.php");



$DB=new BB_DB();


		
?>