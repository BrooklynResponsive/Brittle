<?

session_start();

mb_internal_encoding("UTF-8");
header('Content-Type: text/html; charset=UTF-8');

define("DOCUMENT_ROOT",str_replace("/includes/config.php","", __FILE__));
$SALES_TAX_RATE = 0.08875;
$ADMIN_USER_EMAIL = 'hello@brittlebarn.com';
$ADMIN_USER_PASS = 'raeparthlives';

if( strpos( dirname(__FILE__), "/dev") !== false || true ) {  //franks dev server
			
			 ini_set("display_errors", "On");
			 ini_set("error_reporting",E_ALL);
			define("WEBSITE","http://localhost");
			define("SSLWEBSITE","http://localhost");
			define("BB_DB_NAME", "multil4_barn_dev");	
			define("BB_DB_SERVER", "localhost");
			define("BB_DB_USER", "multil4_barnuser");	
			define("BB_DB_PASSWORD", "br1tt13");
			define("BB_DEBUG",true);
			define("BB_ERROR_LOGGING",false);
			define("EMAIL_DIVERT", true);
            $STRIPE_PUBLISHABLE_KEY = 'pk_test_q8zjGPpxIJYOpiLAW1gALpHm';
            $STRIPE_SECRET_KEY = 'sk_test_gzsGZw7cdsR8elEY6DDcCdqh';

			$MEMCACHED_SERVER = array("localhost");
		}else{
			if(@$_GET['err']==1){ ini_set('display_errors','On'); ini_set("error_reporting",E_ALL); }
			else ini_set('display_errors','Off');
			define("WEBSITE","http://www.brittlebarn.com/");
			define("SSLWEBSITE","https://www.brittlebarn.com/");
			define("BB_DB_NAME", "multil4_barn");	
			define("BB_DB_SERVER", "localhost");
			define("BB_DB_USER", "multil4_barnuser");	
			define("BB_DB_PASSWORD", "br1tt13");
			define("BB_DEBUG",false);
			define("BB_ERROR_LOGGING",false);
			define("EMAIL_DIVERT", false);
            $STRIPE_PUBLISHABLE_KEY = 'ENTER KEY HERE';
            $STRIPE_SECRET_KEY = 'ENTER KEY HERE';
            $MEMCACHED_SERVER = array("localhost");
		}


require(DOCUMENT_ROOT."/includes/db.php");
require(DOCUMENT_ROOT."/includes/BB_Error.class.php");
require(DOCUMENT_ROOT."/includes/fake_db.inc");
require(DOCUMENT_ROOT."/includes/BB_Image.class.php");
require(DOCUMENT_ROOT."/includes/BB_User.class.php");
require(DOCUMENT_ROOT."/includes/BB_Order.class.php");
require(DOCUMENT_ROOT."/includes/Stripe.php");



$DB=new BB_DB();

function outForCode($text){
	return(htmlentities($text, ENT_QUOTES, "UTF-8"));
}

function hash_password($pass, $email)
{
    return crypt($pass . $email, $email);
}

?>
