<?
require("includes/config.php");

if(isset($_GET['referer']))
{
    $referer = $_GET['referer'] . '.php';
}
else
{
    $referer = '';
}
?>
<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Brittle Barn | <?=$prod->name;?>, <?=$prod->size;?></title>

  
  <link rel="stylesheet" href="css/foundation.css">
  <link rel="stylesheet" href="css/social_foundicons.css">
  <link rel="stylesheet" href="css/general_foundicons.css">
  <link rel="stylesheet" href="css/app.css">

  
  <!--[if lt IE 8]>
    <link rel="stylesheet" href="stylesheets/social_foundicons_ie7.css">
    <link rel="stylesheet" href="stylesheets/general_foundicons_ie7.css">
  <![endif]-->
  

  	<meta property="og:site_name" content="Brittle Barn"/>
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Brittle Barn <?=outForCode($prod->name.", ".$prod->size);?>" />
	<meta property="og:url" content="<?=WEBSITE;?>/product.php?id=<?=$prod->id;?>" />
	<meta property="og:image" content="<?=WEBSITE.$mainImage->path;?>" />
	<meta property="og:description" content="<?=outForCode($prod->description);?>" /> 
	
  <script src="js/vendor/custom.modernizr.js"></script>
  <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

<script type="text/javascript">
  // This identifies your website in the createToken call below
    Stripe.setPublishableKey('<?php echo $STRIPE_PUBLISHABLE_KEY;?>');
</script>

</head>
<body>

	<? include("includes/header.inc"); ?> 
	


	<div class="row">
		

		<div class="large-8 columns">
		    <h3 class="red" style="color:red; display: none;"id="login-error"></h3>	
			<h1>Log In</h1>
            <div class="row">
                <div class="large-6 columns">
                    <label>Email</label>
                    <input type="text" id="brittleLoginEmail" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label>Password</label>
                    <input type="password" id="brittleLoginPassword" placeholder="" />
                </div>
            </div>
            <input type="hidden" id="referer" value="<?php echo $referer;?>">
			<a href="#" class="button radius right" id="brittleLoginButton">Log Me In</a>

		</div>
	</div>
<? require("includes/before_body_end.inc"); ?>
<script src="js/login.js"></script>    

</body>
</html>
