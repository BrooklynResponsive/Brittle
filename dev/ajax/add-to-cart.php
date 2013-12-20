<?

require('../includes/config.php');

$msg=false;
$id=(int)$_POST['product'];
$quantity =  $_POST['quantity'];
$items = $_POST['items'];

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
error_log(print_r($_POST, true));
?>
<div class="large-24 columns">

        <div class="large-24 columns">
                <h2>Item<?php if($quantity > 1){ echo 's'; }?> added to your cart: <strong><?php echo $prod->name; ?></strong></h2>

                <div class="panel cart-summary">

                    <p><strong>Cart summary	<span></span></strong></p>

                    <hr>
                    <div id="shopping-cart-items">
                        <?php

                            $running_total=0;

                            foreach($items as $id => $item_group)
                            {
                                $item = $item_group['item'];
                                $row_total = $item_group['count'] * $item['price'];
                                $running_total += $row_total;
                                echo "<p class='shopping-cart-row'>" . $item['name'] . '  (<input type="text" class="shopping-cart-item-quantity" id="shopping-cart-quantity-' . $id . '" value="' . $item_group['count'] . '" data-price="' . $item['price'] . '"> @ $' . $item['price'] . ') <i class="shopping-cart-remove-item" data-id="' . $id . '">remove</i><span class="shopping-cart-row-total" id="shopping-cart-row-total-' . $id . '">' . $row_total . '</span></p>';

                            }
                        ?>
                    </div>
                    <hr>

                    <p><strong>Subotal <span class="green" id="shopping-cart-subtotal">$<?php echo $running_total;?></span></strong></p>

                </div>

        </div>


        <div class="large-6 columns">

                <a href="index.php" class="button round btn-continue-shopping expand">Continue Shopping</a>

        </div>


        <div class="large-6 columns">

                <a href="shopping-cart.php" class="button round expand">Go to Cart</a>

        </div>


  </div>
