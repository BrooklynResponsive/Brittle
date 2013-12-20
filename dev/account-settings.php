<?php
require("includes/config.php");
error_reporting(0);

$user_obj = json_decode($_COOKIE['brittleUserContents']);

if(!$user_obj->id)
{
    header('Location:login.php?referer=account-settings');
    exit();
}

$user = new BB_User();
$user->readMe($user_obj->id);
$order_history = $user->get_order_history();

?><!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Brittle Barn | Thank You</title>

  
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
	<meta property="og:title" content="Brittle Barn Payment Success" />
	<meta property="og:url" content="<?=WEBSITE;?>/payment-success.php" />
	
    <script src="js/vendor/custom.modernizr.js"></script>
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>

</head>
<body>

	<? include("includes/header.inc"); ?> 

	<div class="row">
    <h3 class="red" style="color: red;" id="accountSettingsError"></h3>
		<div class="large-8 columns" id="accountSettingsDiv">
			<h1>Address Info</h1>

            <div class="row">
                <div class="large-6 columns">
                    <label for="accountSettingsFirstName">First Name</label>
                    <input type="text" id="accountSettingsFirstName" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label for="accountSettingsLastName">Last Name</label>
                    <input type="text" id="accountSettingsLastName" placeholder="" />
                </div>
            </div>
            <div class="row">
                <div class="large-12 columns">
                    <label for="accountSettingsAddress1">Address</label>
                    <input type="text" id="accountSettingsAddress1" data-validate="nonempty" placeholder="" />
                </div>
            </div>
            <div class="row">
                <div class="large-12 columns">
                    <label for=="accountSettingsAddress2">Address</label>
                    <input type="text" id="accountSettingsAddress2" placeholder="" />
                </div>
            </div>
            <div class="row">
                <div class="large-7 columns">
                    <label for="accountSettingsCity">City</label>
                    <input type="text" id="accountSettingsCity" data-validate="nonempty" placeholder="" />
                </div>
                <div class="large-2 columns">
                    <label for="accountSettingsState">State</label>
                    <input type="text" id="accountSettingsState" data-validate="nonempty,length:2"placeholder="" />
                </div>
                <div class="large-3 columns">
                    <label for="accountSettingsZipcode">Zipcode</label>
                    <input type="text" id="accountSettingsZipcode" data-validate="nonempty,numeric,length:5"/>
                </div>
            </div>
            <div class="row">
                <div class="large-6 columns">
                    <label for="accountSettingsEmail">Email</label>
                    <input type="text" id="accountSettingsEmail" data-validate="nonempty" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label for="accountSettingsPhone">Phone</label>
                    <input type="text" id="accountSettingsPhone" placeholder="" />
                </div>
            </div>
            <h1 id="accountSettingsAccountHeading">Change Password</h1>
            <div class="row">
                <div class="large-6 columns">
                    <label for="accountSettingsCreatePassword">New Password</label>
                    <input type="password" id="accountSettingsCreatePassword" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label for="accountSettingsCreatePasswordConfirm">Confirm Password</label>
                    <input type="password" id="accountSettingsCreatePasswordConfirm" placeholder="" />
                </div>
            </div>
            <form action="" method="POST" id="payment-form">
                <h1>Credit Card Info</h1>
                <div class="row">
                    <div class="large-6 columns">
                        <label for"accountSettingsCreditNumber">Credit Card Number</label>
                        <input type="text" data-stripe="number"  id="accountSettingsCreditNumber" placeholder="" />
                    </div>
                    <div class="large-2 columns">
                        <label for="accountSettingsCVC">CVC</label>
                        <input type="text" size="4" data-stripe="cvc" id="accountSettingsCVC" placeholder="" />
                    </div>
                    <div class="large-2 columns">
                        <label for="accountSettingsExpMonth">Month</label>
                        <input type="text" data-stripe="exp-month" id="accountSettingsExpMonth" placeholder="MM" />
                    </div>
                    <div class="large-2 columns">
                        <label for="accountSettingsExpYear">Year</label>
                        <input type="text" data-stripe="exp-year" id="accountSettingsExpYear" placeholder="YY" />
                    </div>
                </div>
            </form>
            <h1>Validation</h1>
            <div class="row">
                <div class="large-6 columns">
                    <label for="accountSettingsCurrentPassword">Current Password</label>
                    <input type="password" id="accountSettingsCurrentPassword" data-validate="nonempty" />
                </div>
            </div>
            <a href="#" id="accountSettingsSaveButton" class="button radius right">Save Changes</a>

	</div>
	<div class="row">
		<div class="large-8 columns">
            <h1>Order History</h1>
            <table class="order-history" width="100%">
                <tr>
                    <th>Order Id</th>
                    <th>Contents</th>
                    <th>Date</th>
                    <th>Tracking</th>
                </tr>

                <?php
                    for($i=0; $i<count($order_history); $i++)
                    {
                        $order_items = $order_history[$i]['items'];
                ?>
                <tr>
                    <td>#<?php echo $order_history[$i]['id']; ?></td>
                    <td>
                        <ul>
                            <?php for($j=0; $j<count($order_items); $j++)
                            {
                            ?>
                                <li><?php echo $order_items[$j]['quantity']; ?> x <?php echo $order_items[$j]['name']; ?> (<?php echo $order_items[$j]['size']; ?>)</li>
                            <?php
                            }
                            ?>
                        </ul>
                    </td>
                    <td><?php echo $order_history[$i]['datePlaced']; ?></td>
                    <td><?php echo $order_history[$i]['tracking_number']; ?></td>
                </tr>
                <?php
                    }
                ?>
            </table>
        </div>
    </div>
  </div>

<? require("includes/before_body_end.inc"); ?>
<script src="js/account-settings.js"></script>    
    <script type="text/javascript">
      // This identifies your website in the createToken call below
        Stripe.setPublishableKey('<?php echo $STRIPE_PUBLISHABLE_KEY;?>');
    </script>


</body>
</html>
