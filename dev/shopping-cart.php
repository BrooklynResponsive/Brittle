<?
require("includes/config.php");

?>
<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Brittle Barn | Shopping Cart</title>

  
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
	<meta property="og:title" content="Brittle Barn Chopping Cart" />
	<meta property="og:url" content="<?=WEBSITE;?>/shopping-cart.php" />
	
    <script src="js/vendor/custom.modernizr.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

    <script type="text/javascript">
      // This identifies your website in the createToken call below
        Stripe.setPublishableKey('<?php echo $STRIPE_PUBLISHABLE_KEY;?>');
        tax_rate = <?php echo $SALES_TAX_RATE; ?>;
    </script>

</head>
<body>

	<? include("includes/header.inc"); ?> 
	


	<div class="row">
		
		<div class="large-8 columns" id="shoppingCartDiv">
            <h1>Shopping Cart</h1>

            <div id="shoppingCartInfo">
            </div>

			<h1>Delivery Info</h1>

            <div class="row">
                <div class="large-6 columns">
                    <label for="shoppingCartFirstName">First Name</label>
                    <input type="text" id="shoppingCartFirstName" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label forid="shoppingCartLastName">Last Name</label>
                    <input type="text" id="shoppingCartLastName" placeholder="" />
                </div>
            </div>
            <div class="row">
                <div class="large-12 columns">
                    <label for="shoppingCartAddress1">Address</label>
                    <input type="text" id="shoppingCartAddress1" data-validate="nonempty" placeholder="" />
                </div>
            </div>
            <div class="row">
                <div class="large-12 columns">
                    <label for="shoppingCartAddress2">Address</label>
                    <input type="text" id="shoppingCartAddress2" placeholder="" />
                </div>
            </div>
                <div class="row">
                    <div class="large-7 columns">
                        <label for="shoppingCartCity">City</label>
                        <input type="text" id="shoppingCartCity" data-validate="nonempty" placeholder="" />
                    </div>
                    <div class="large-2 columns">
                        <label for="shoppingCartState">State</label>
                        <input type="text" id="shoppingCartState" data-validate="nonempty,length:2" placeholder="" />
                    </div>
                    <div class="large-3 columns">
                        <label for="shoppingCartZipcode">Zipcode</label>
                        <input type="text" id="shoppingCartZipcode" data-validate="nonempty" />
                    </div>
                </div>
                <div class="row">
                    <div class="large-6 columns">
                        <label for="shoppingCartEmail">Email</label>
                        <input type="text" id="shoppingCartEmail" data-validate="nonempty" placeholder="" />
                    </div>
                    <div class="large-6 columns">
                        <label for="shoppingCartPhone">Phone</label>
                        <input type="text" id="shoppingCartPhone" data-validate="nonempty,min-length:10" placeholder="" />
                    </div>
                </div>
            <h1 id="shoppingCartAccountHeading">Create an Account (Optional)</h1>
            <div class="row" id="shoppingCartLoginPane" style="display:none;">
                <div class="large-6 columns">
                    <label for="shoppingCartLoginEmail">Email</label>
                    <input type="text" id="shoppingCartLoginEmail" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label for="shoppingCartLoginPassword">Password</label>
                    <input type="password" id="shoppingCartLoginPassword" placeholder="" />
                </div>
            </div>
            <div class="row" id="shoppingCartRegisterPane">
                <div class="large-6 columns">
                    <label for="shoppingCartCreatePassword">Password</label>
                    <input type="password" id="shoppingCartCreatePassword" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label for="shoppingCartCreatePasswordConfirm">Confirm Password</label>
                    <input type="password" id="shoppingCartCreatePasswordConfirm" placeholder="" />
                </div>
            </div>
            <form action="" method="POST" id="payment-form">
            <h1>Credit Card Info</h1>
                <div class="row">
                    <div class="large-6 columns">
                        <label for="shoppingCartCreditNumber">Credit Card Number</label>
                        <input type="text" data-stripe="number"  id="shoppingCartCreditNumber" data-validate="nonempty" placeholder="" />
                    </div>
                    <div class="large-2 columns">
                        <label for="shoppingCartCVC">CVC</label>
                        <input type="text" size="4" data-stripe="cvc" id="shoppingCartCVC" data-validate="nonempty" placeholder="" />
                    </div>
                    <div class="large-2 columns">
                        <label for="shoppingCartExpMonth">Month</label>
                        <input type="text" data-stripe="exp-month" id="shoppingCartExpMonth" data-validate="nonempty,length:2" placeholder="MM" />
                    </div>
                    <div class="large-2 columns">
                        <label for="shoppingCartExpYear">Year</label>
                        <input type="text" data-stripe="exp-year" id="shoppingCartExpYear" data-validate="nonempty,length:2" placeholder="YY" />
                    </div>
                </div>
                <a href="#" id="shoppingCartCheckoutButton" class="button radius right">Checkout</a>

            </form>
		</div>
	</div>
<? require("includes/before_body_end.inc"); ?>
<script src="js/shopping-cart.js"></script>    

</body>
</html>
