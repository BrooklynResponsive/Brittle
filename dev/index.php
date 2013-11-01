<?


require("includes/config.php");


?><!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Brittle Barn</title>

  
  <link rel="stylesheet" href="css/foundation.css">
  <link rel="stylesheet" href="css/social_foundicons.css">
  <link rel="stylesheet" href="css/general_foundicons.css">
  <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" href="css/app.css">

  
  <!--[if lt IE 8]>
    <link rel="stylesheet" href="stylesheets/social_foundicons_ie7.css">
    <link rel="stylesheet" href="stylesheets/general_foundicons_ie7.css">
  <![endif]-->
  
	 <meta property="twitter:account_id" content="1918536176" />
	
  	<meta property="og:site_name" content="Brittle Barn"/>
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Brittle Barn" />
	<meta property="og:url" content="<?=WEBSITE;?>/" />
	<meta property="og:image" content="http://brittlebarn.com/img/brittle-barn-logo-square.jpg" />
	<meta property="og:description" content="Brittle, Sweets, Syrups, handmade from premium all-natural ingredients in Brooklyn, NYC." />
	


  <script src="js/vendor/custom.modernizr.js"></script>

</head>
<body>

	<? include("includes/header.inc"); ?>
	

	<div class="row">
		<div class="large-12 columns">
			
			<ul data-orbit  data-options='timer_speed:7000; bullets:true; navigation_arrows: true; slide_number: true; timer: true;' >
			<?
			 foreach($mainOrbitContent as $img){ 
			?>
	        	<li>        
			        <figure class='figure-orbit'>
			       			<img src='<?=WEBSITE.$img['path'];?>' alt="<?=outForCode(strip_tags($img['caption']));?>"  />
							
						    

						    <figcaption class='orbit-caption'>
									<?=$img['caption'];?>
							</figcaption>

			        </figure>
				</li>
	        
	        <? } ?>         
                         
          </ul>

		</div>
	</div>



	<div class="row">

		<div class="large-12 columns">
			<a name="brittle"></a>
			<h1 data-magellan-destination="brittle">Nut brittle handmade from premium ingredients in Brooklyn, NYC.</h1>
		</div>
		
		<ul class="small-block-grid-2 large-block-grid-4 product-list">
		<? 
		$q = $DB->q("SELECT * from products where type='BRITTLE'");
		
		while($prod = mysql_fetch_object($q)){ 
			$img=mysql_fetch_object($DB->q("select i.* from Image i, rel_PRODUCT_IMAGES r where r.targetRow=i.id and r.sourceRow=$prod->id"));
			
		?>
			<li>		
			<figure class="product-box">
				<a href="product.php?id=<?=$prod->id;?>"><img src="<?=WEBSITE.$img->path;?>"></a>
				<a href="product.php?id=<?=$prod->id;?>" class="add-cart">add to cart <i class="general foundicon-cart"></i></a>
				<figcaption> 
	                <h2><?=$prod->name;?></h2>
	                <h3 class="product-price">$<?=$prod->price;?> • <span class='product-size'><?=$prod->size;?></span></h3>
	            </figcaption>
			</figure>
			</li>
		<? } ?> 
		</ul>		


		

	</div>





	<div class="row">

		<div class="large-12 columns">
			<a name="sweets"></a>
			<h1 data-magellan-destination="sweets">Hand-candied fruits, flowers, and condiments</h1>
		</div>

		<ul class="small-block-grid-2 large-block-grid-4 product-list">
		<? 
		$q = $DB->q("SELECT * from products where type='SWEETS'");
		
		while($prod = mysql_fetch_object($q)){ 
			$img=mysql_fetch_object($DB->q("select i.* from Image i, rel_PRODUCT_IMAGES r where r.targetRow=i.id and r.sourceRow=$prod->id"));
			
		?>
			<li>		
			<figure class="product-box">
				<a href="product.php?id=<?=$prod->id;?>"><img src="<?=WEBSITE.$img->path;?>"></a>
				<a href="product.php?id=<?=$prod->id;?>" class="add-cart">add to cart <i class="general foundicon-cart"></i></a>
				<figcaption> 
	                <h2><?=$prod->name;?></h2>
	                <h3 class="product-price">$<?=$prod->price;?> • <span class='product-size'><?=$prod->size;?></span></h3>
	            </figcaption>
			</figure>
			</li>
		<? } ?> 
		</ul>		


	</div>






	<div class="row">

		<div class="large-12 columns">
			<a name="syrups"></a>
			<h1 data-magellan-destination="syrups">Syrups extracted from our candied items. Make extraordinary cocktails and more.</h1>
		</div>

		<ul class="small-block-grid-2 large-block-grid-4 product-list">
		<? 
		$q = $DB->q("SELECT * from products where type='SYRUPS'");
		
		while($prod = mysql_fetch_object($q)){ 
			$img=mysql_fetch_object($DB->q("select i.* from Image i, rel_PRODUCT_IMAGES r where r.targetRow=i.id and r.sourceRow=$prod->id"));
			
		?>
			<li>		
			<figure class="product-box">
				<a href="product.php?id=<?=$prod->id;?>"><img src="<?=WEBSITE.$img->path;?>"></a>
				<a href="product.php?id=<?=$prod->id;?>" class="add-cart">add to cart <i class="general foundicon-cart"></i></a>
				<figcaption> 
	                <h2><?=$prod->name;?></h2>
	                <h3 class="product-price">$<?=$prod->price;?> • <span class='product-size'><?=$prod->size;?></span></h3>
	            </figcaption>
			</figure>
			</li>
		<? } ?> 
		</ul>	
		
	</div>



	<div class="row">

		<div class="large-12 columns">
			<a name="special"></a>
			<h1 data-magellan-destination="special">Special Orders</h1>
			<p>Bulk pricing is available for large orders. We also provide:
			</p>
			<ul>
				<li>Gift bags</li>
				<li>Custom labeling and branding</li>
				<li>Alternative packaging and sizes</li>
				<li>On-site services ("Brittle Bar" dessert table, etc.)</li>
				<li>Custom/themed flavors</li>
			</ul>
		</div>

	</div>




	<div class="row">

		<div class="large-12 columns">
			<a name="contact"></a>
			<h1 data-magellan-destination="contact">Contact Us</h1>
			<p>You can reach us at <a href="mailto:hello@brittlebarn.com">hello@brittlebarn.com</a></p>
			
			<p>Follow us or drop us a line: </p>
			<ul style='list-style:none;'>
			<li><a href="https://facebook.com/brittlebarn"><strong>brittlebarn</strong> on facebook</a>
			<li><a href="http://instagram.com/brittlebarn"><strong>brittlebarn</strong> on instagram</a></li>
			<li><a href="https://twitter.com/brittlebarn"><strong>@brittlebarn</strong></a> on twitter</li>
			</ul>


		</div>

	</div>



	<div class="row">

		<div class="large-12 columns">
			<h1>Join the mailing list</h1>
			<p>Get information about upcoming sale events, new flavors, Brittle Barn stall locations, and more. We will never sell, rent, or give away your e-mail address for any reason. We also won't bombard you with frequent e-mails.</p>
			 <div class="alert-box radius" style="display:none;" id="mlist-response"></div>
			<form action="#" class="custom">

			  <div class="row" id="mlist-signup">
			    	<div class="large-6 small-8 columns">
			          	<input type="text" name='mlist-e' placeholder="your-email@somewhere.com">
			    	</div>
			    	<div class="large-2 small-4 columns">
			          	<a href="#" data-action="add-to-mlist" class="button prefix">Join list</a>
			    	</div>
			    	<div class="large-4 columns">
			          	
			    	</div>

			  </div>

			</form>

		</div>

	</div>
	
	
	<footer class="row">

		<div class="large-12 columns">
			<h1>About Us</h1>
		</div>
		<div class='row'>
			<div class='small-6 large-2 columns'>
				<img src="<?=WEBSITE."/img/square/MattAntonia.jpg";?>" alt="Matt Lima and Antonia Pereira"/>
			</div>
			<div class='small-6 large-10 columns'>
				<p>Matt and Antonia met in 2012 and live in Gowanus, Brooklyn. Brittle Barn is the product of their deep and abiding love... of sweets.</p>
			</div>
		</div>
			
			
		</div>

	</footer>

 <? require("includes/before_body_end.inc"); ?>

  
</body>
</html>
