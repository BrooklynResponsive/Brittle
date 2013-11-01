<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Foundation 4</title>

  
  <link rel="stylesheet" href="css/foundation.css">
  <link rel="stylesheet" href="css/social_foundicons.css">
  <link rel="stylesheet" href="css/general_foundicons.css">
  <link rel="stylesheet" href="css/app.css">

  
  <!--[if lt IE 8]>
    <link rel="stylesheet" href="stylesheets/social_foundicons_ie7.css">
    <link rel="stylesheet" href="stylesheets/general_foundicons_ie7.css">
  <![endif]-->
  

  <script src="js/vendor/custom.modernizr.js"></script>

</head>
<body>

	<? include("includes/header.inc"); ?> 
	


	<div class="row">

		<div class="large-6 columns">
			<img src="http://placehold.it/600x600">
		</div>


		<div class="large-6 columns">
			
			<h1>Bourbon Whiskey Brittle</h1>

			<h2 class="green">$10 <span>7 oz bag</span></h2>


			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam at porttitor sem. Aliquam erat volutpat. Donec placerat nisl magna, et faucibus arcu condimentum sed.</p>

			<a href="#" class="button radius" data-reveal-id="add-to-cart">Add to Cart</a>

			<hr>

			<span class="connect">Share</span>

			<ul class="icon-grid">
				<li><i class="social foundicon-twitter"></i></li>
				<li><i class="social foundicon-facebook"></i></li>
				<li><i class="social foundicon-instagram"></i></li>
			</ul>

		</div>
		
	</div>



	<!-- Reveal Shopping Cart -->
	<div id="add-to-cart" class="reveal-modal small">

	  <div class="row">

	  		<div class="large-12 columns">
	  			<h2>Item added to your cart</h2>
	  		</div>

	  		<div class="large-6 columns">

	  				<p><strong>Veracruz Brittle</strong> <span>7oz bag</a></p>

	  				<img src="http://placehold.it/100x100">

	  				<table width="50%" border="0" cellspacing="0" cellpadding="0">
					  <tr>
					    <td><span class="shopping-cart-attribute">Quantity:</span></td>
					    <td align="right"><input type="text" size="3" class="shopping-cart-qty"></td>
					  </tr>
					  <tr>
					    <td><span class="shopping-cart-attribute">Price:</span></td>
					    <td align="right"><span class="shopping-cart-attribute price">$9.00</span></td>
					  </tr>
					  <tr>
					    <td><span class="shopping-cart-attribute">Shipping:</span></td>
					    <td align="right"><span class="shopping-cart-attribute">$2.00</span></td>
					  </tr>
					</table>

	  		</div>

	  		<div class="large-6 columns">

	  				<div class="panel cart-summary">

	  					<p><strong>Cart summary	<span>1 Item</span></strong></p>

	  					<hr>

	  					<p>Subtotal	<span>$9.00</span></p>
	  					<p>Shipping	<span>$6.00</span></p>
	  					<p>Tax <i>Applied during checkout</i></p>

	  					<hr>

	  					<p><strong>Total <span class="green">$15.00</span></strong></p>

	  				</div>

	  		</div>


	  		<div class="large-6 columns">

	  				<a href="#" class="button round btn-continue-shopping expand">Continue Shopping</a>

	  		</div>


	  		<div class="large-6 columns">

	  				<a href="#" class="button round expand">Go to Cart</a>

	  		</div>


	  </div>


	  <a class="close-reveal-modal">&#215;</a>
	</div>
	<!-- Reveal Shopping Cart -->
	


<? require("includes/before_body_end.inc"); ?>

</body>
</html>
