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
  

  <script src="js/vendor/custom.modernizr.js"></script>

</head>
<body>

	<header>

			<div class="row">
				
				<div class="large-2 columns">
					<a href="#"><img src="img/brittle-barn-logo.gif"></a>
				</div>

				<div class="large-7 columns top-padding">
					
					<div data-magellan-expedition="fixed">
					  <dl class="sub-nav">
					    <dd data-magellan-arrival="brittle"><a href="#brittle">Brittle</a></dd>
					    <dd data-magellan-arrival="sweets"><a href="#sweets">Sweets</a></dd>
					    <dd data-magellan-arrival="syrups"><a href="#syrups">Syrups</a></dd>
					    <dd data-magellan-arrival="special"><a href="#special">Special Orders</a></dd>
					    <dd data-magellan-arrival="contact"><a href="#contact">Contact Us</a></dd>
					  </dl>
					</div>
					    

				</div>

				<div class="large-3 columns text-right">
					
					<i class="general foundicon-cart shop-icon"></i>

					<span class="connect">connect</span>

					<ul class="icon-grid">
					  <li><i class="social foundicon-twitter"></i></li>
				      <li><i class="social foundicon-facebook"></i></li>
				      <li><i class="social foundicon-instagram"></i></li>
				    </ul>

				</div>
			
			</div>

	</header>
	

	<div class="row">
		<div class="large-12 columns">
			
			<ul data-orbit  data-options='timer_speed:7000; bullets:true; navigation_arrows: true; slide_number: true; timer: true;' >
			<?
			 foreach($mainOrbitContent as $img){ 
			?>
	        	<li>        
			        <figure class='figure-orbit'>
			       			<img src='<?=WEBSITE.$img['path'];?>' alt="<?=htmlentities(strip_tags($img['caption']));?>"  />

						    

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
		<? foreach($products['brittle'] as $prod){ ?>
			<li>		
			<figure class="product-box">
				<a href="product.php?id=<?=$prod->id;?>"><img src="<?=WEBSITE.$prod->img[0];?>"></a>
				<a href="product.php?id=<?=$prod->id;?>" class="add-cart">add to cart <i class="general foundicon-cart"></i></a>
				<figcaption> 
	                <h2><?=$prod->name;?></h2>
	                <h3>$<?=$prod->price;?> • <span class='product-size'><?=$prod->size;?></span></h3>
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
		<? foreach($products['sweets'] as $prod){ ?>
			<li>		
			<figure class="product-box">
				<a href="product.php?id=<?=$prod->id;?>"><img src="<?=WEBSITE.$prod->img[0];?>"></a>
				<a href="product.php?id=<?=$prod->id;?>" class="add-cart">add to cart <i class="general foundicon-cart"></i></a>
				<figcaption> 
	                <h2><?=$prod->name;?></h2>
	                <h3>$<?=$prod->price;?> • <span class='product-size'><?=$prod->size;?></span></h3>
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
		<? foreach($products['syrups'] as $prod){ ?>
			<li>		
			<figure class="product-box">
				<a href="product.php?id=<?=$prod->id;?>"><img src="<?=WEBSITE.$prod->img[0];?>"></a>
				<a href="product.php?id=<?=$prod->id;?>" class="add-cart">add to cart <i class="general foundicon-cart"></i></a>
				<figcaption> 
	                <h2><?=$prod->name;?></h2>
	                <h3>$<?=$prod->price;?> • <span class='product-size'><?=$prod->size;?></span></h3>
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
			<p>You can reach us at <a href="mailto:email@example.com">email@example.com</a></p>
		</div>

	</div>



	<div class="row">

		<div class="large-12 columns">
			<h1>Join the mailing list</h1>
			
			<form action="#" class="custom">

			  <div class="row">
			    	<div class="large-6 small-8 columns">
			          	<input type="text" placeholder="Hex Value">
			    	</div>
			    	<div class="large-2 small-4 columns">
			          	<a href="#" class="button prefix">Action</a>
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


  <script>
  document.write('<script src=' +
  ('__proto__' in {} ? 'js/vendor/zepto' : 'js/vendor/jquery') +
  '.js><\/script>')
  </script>
  
  <script src="js/foundation.min.js"></script>
  <!--
  
  <script src="js/foundation/foundation.js"></script>
  
  <script src="js/foundation/foundation.interchange.js"></script>
  
  <script src="js/foundation/foundation.abide.js"></script>
  
  <script src="js/foundation/foundation.dropdown.js"></script>
  
  <script src="js/foundation/foundation.placeholder.js"></script>
  
  <script src="js/foundation/foundation.forms.js"></script>
  
  <script src="js/foundation/foundation.alerts.js"></script>
  
  <script src="js/foundation/foundation.magellan.js"></script>
  
  <script src="js/foundation/foundation.reveal.js"></script>
  
  <script src="js/foundation/foundation.tooltips.js"></script>
  
  <script src="js/foundation/foundation.clearing.js"></script>
  
  <script src="js/foundation/foundation.cookie.js"></script>
  
  <script src="js/foundation/foundation.joyride.js"></script>
  
  <script src="js/foundation/foundation.orbit.js"></script>
  
  <script src="js/foundation/foundation.section.js"></script>
  
  <script src="js/foundation/foundation.topbar.js"></script>
  
  -->
  
  <script>
    $(document).foundation();
  </script>
</body>
</html>
