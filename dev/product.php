<?


require("includes/config.php");

$msg=false;
$id=(int)$_GET['id'];
$prod = mysql_fetch_object($DB->q("select * from products where id = $id"));
if(!$prod){
	
	echo("That page does not exist.");
	exit();
	
}

$iq = $DB->q("select i.* from Image i join rel_PRODUCT_IMAGES r on r.targetRow=i.id where r.sourceRow=$prod->id order by r.ordinal");
$imgs=array();
while($iqq=mysql_fetch_object($iq)){
	$imgs[]=$iqq;
}
$mainImage = $imgs[0];
$imgs[]=array_shift($imgs);


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

</head>
<body>

	<? include("includes/header.inc"); ?> 
	


	<div class="row">

		<div class="large-6 columns">
			<ul data-orbit  data-options='timer_speed:7000; bullets:false; navigation_arrows: true; slide_number: true; timer: false;' >
			<?
			 foreach($imgs as $img){ 
			?>
	        	<li>        
			        <figure class='figure-orbit'>
			       			<img src='<?=WEBSITE.$img->path;?>' alt="<?=outForCode(strip_tags($img->caption));?>"  />
							
						   

			        </figure>
				</li>
	        
	        <? } ?>         
                         
          </ul>
		</div>


		<div class="large-6 columns">
			
			<h1><?=$prod->name;?></h1>

			<h2 class="product-price">$<?=$prod->price;?> <span><?=$prod->size;?></span></h2>


			<p><?=$prod->description;?></p>

			<p>Quantity: <input  type="text" size="3" id="quantity" value='1' class="shopping-cart-qty-big"> </p>
			
			<a href="#" class="button radius" rel="add-to-cart">Add to Cart</a>

			<hr>

			<span class="connect">Share</span>

			<ul class="icon-grid addthis_toolbox">
				<li><a href="#" class="addthis_button_twitter"><i class="social foundicon-twitter addthis_button_twitter"></i></a></li>
				<li><a href="#" class="addthis_button_facebook"><i class="social foundicon-facebook"></i></a></li>
				<li><a href="#" class="addthis_button_instagram"><i class="social foundicon-instagram "></i></a></li>
			</ul>

		</div>
		
	</div>



	
	


<? require("includes/before_body_end.inc"); ?>

</body>
</html>
