<?

require('../includes/config.php');

$msg=false;
$prod =2;
$quantity =2;


$prod = mysql_fetch_object($DB->q("select * from products where id = $id"));
if(!$prod){
	
	echo("That page does not exist.");
	exit();
	
}
// add to cart
$cart = brittle_cart::open();
$cart->addItemQuantity($id, $quantity);

$iq = $DB->q("select i.* from Image i join rel_PRODUCT_IMAGES r on r.targetRow=i.id where r.sourceRow=$prod->id order by r.ordinal");
$imgs=array();
while($iqq=mysql_fetch_object($iq)){
	$imgs[]=$iqq;
}
$mainImage = $imgs[0];
$imgs[]=array_shift($imgs);
error_log(print_r($_POST, true));
?>
<div class="large-12 columns">

<div class="row">

	  		<div class="large-12 columns">
	  			<h2>Item<?php if($quantity > 1){ echo 's'; }?> added to your cart</h2>

	  		</div>

	  		<div class="large-6 columns">

	  				<p><strong><?php echo $prod->name; ?></strong> <span><?php echo $prod->size;?></a></p>

	  				<img src="http://placehold.it/100x100">

	  				<table width="50%" border="0" cellspacing="0" cellpadding="0">
					  <tr>
					    <td><span class="shopping-cart-attribute">Quantity:</span></td>
					    <td align="right"><input type="text" size="3" class="shopping-cart-qty item_Quantity" value="<?php echo $quantity; ?>"></td>
					  </tr>
					  <tr>
					    <td><span class="shopping-cart-attribute">Price:</span></td>
					    <td align="right"><span class="shopping-cart-attribute price item-price">$<?php echo $prod->price;?></span></td>
					  </tr>
					  <tr>
					    <td><span class="shopping-cart-attribute">Shipping:</span></td>
					    <td align="right"><span class="shopping-cart-attribute">TBD</span></td>
					  </tr>
					</table>

	  		</div>

	  		<div class="large-6 columns">

	  				<div class="panel cart-summary">

	  					<p><strong>Cart summary	<span class="simpleCart_quantity"></span></strong></p>

	  					<hr>

	  					<p>Subtotal	<span class="simpleCart_total"></span></p>
	  					<p>Shipping	<span class="simpleCart_shipping"></span></p>
	  					<p>Tax <span class="simpleCart_tax"></span></p>

	  					<hr>

	  					<p><strong>Total <span class="green simpleCart_grandTotal"></span></strong></p>

	  				</div>

	  		</div>


	  		<div class="large-6 columns">

	  				<a href="#" class="button round btn-continue-shopping expand">Continue Shopping</a>

	  		</div>


	  		<div class="large-6 columns">

	  				<a href="#" class="button round expand">Go to Cart</a>

	  		</div>


	  </div>
