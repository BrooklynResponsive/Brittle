<?

require('../includes/config.php');

if(isset($_POST['summary']))
{
    $summary = true;        //this means we arent adding anything to the cart
}
else
{
    $summary = false;
    $msg=false;
    $id=(int)$_POST['product'];
    $quantity =  $_POST['quantity'];
    $items = $_POST['items'];

    $prod = mysql_fetch_object($DB->q("select * from products where id = $id"));
    if(!$prod){
        
        echo("This page does not exist.");  //Ceci n'est pas une pipe
        exit();
        
    }
    $iq = $DB->q("select i.* from Image i join rel_PRODUCT_IMAGES r on r.targetRow=i.id where r.sourceRow=$prod->id order by r.ordinal");
    $imgs=array();
    while($iqq=mysql_fetch_object($iq)){
        $imgs[]=$iqq;
    }
    $mainImage = $imgs[0];
    $imgs[]=array_shift($imgs);
}
?>
<div class="large-24 columns">

        <div class="large-24 columns">
                <?php if($summary == false)
                {
                ?>
                <h2>Item<?php if($quantity > 1){ echo 's'; }?> added to your cart: <strong><?php echo $prod->name; ?></strong></h2>
                <?php
                }
                ?>
                <div id="addToCartSummary">
                </div>
        </div>


        <div class="large-6 columns">

                <a href="index.php" class="button round btn-continue-shopping expand">Continue Shopping</a>

        </div>


        <div class="large-6 columns">

                <a href="shopping-cart.php" class="button round expand">Checkout</a>

        </div>


  </div>
