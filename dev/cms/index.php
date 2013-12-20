<?
require("../includes/config.php");

?>
<!DOCTYPE html>
<!--[if IE 8]> 				 <html class="no-js lt-ie9" lang="en" > <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en" > <!--<![endif]-->

<head>
	<meta charset="utf-8">
  <meta name="viewport" content="width=device-width">
  <title>Brittle Barn | CMS</title>

  
  <link rel="stylesheet" href="../css/foundation.css">
  <link rel="stylesheet" href="../css/social_foundicons.css">
  <link rel="stylesheet" href="../css/general_foundicons.css">
  <link rel="stylesheet" href="../css/app.css">

  
  <!--[if lt IE 8]>
    <link rel="stylesheet" href="stylesheets/social_foundicons_ie7.css">
    <link rel="stylesheet" href="stylesheets/general_foundicons_ie7.css">
  <![endif]-->
  

  	<meta property="og:site_name" content="Brittle Barn"/>
	<meta property="og:type" content="website" />
	
</head>
<body>
<div class="row">
    <div class="large-12 columns">
        <div class="large-2 columns">
            <a href="<?=WEBSITE;?>"><img src="../img/brittle-barn-logo.gif"></a>
        </div>
        <div class="large-10 columns">
            <nav class="top-bar" data-topbar>
              <ul class="title-area">
                <li class="name">
                  <h1><a href="#">BB CMS</a></h1>
                </li>
                <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
              </ul>

              <section class="top-bar-section">
                <!-- Left Nav Section -->
                <ul class="left">
                  <li><a href="#" id="cmsCustomersLink" class="cmsMenuOption">Customers</a></li>
                  <li><a href="#" id="cmsOrdersLink" class="cmsMenuOption">Orders</a></li>
                </ul>
              </section>
            </nav>
        </div>
    </div>
</div>
<div class="row">
    <div id="cmsLanding" class="large-12 columns cmsMenuItem">
        <div class="large-12 columns" id="cmsLogin">
            <div class="large-8 columns">
                <h3 class="red" style="color:red; display: none;" id="cms-login-error"></h3>	
                <h1>Log In</h1>
                <div class="large-6 columns">
                        <label>Email</label>
                        <input type="text" id="brittleCMSLoginEmail" placeholder="" />
                </div>
                <div class="large-6 columns">
                    <label>Password</label>
                    <input type="password" id="brittleCMSLoginPassword" placeholder="" />
                </div>
                <a href="#" class="button radius right" id="brittleCMSLoginButton">Log Me In</a>
            </div>
        </div>
        <div class="large-12 columns" id="cmsWelcome">
            <h1>Welcome. You are now logged in.</h1>
        </div>
    </div>
</div>
<div class="row">
    <div id="cmsCustomers" class="large-12 columns cmsMenuItem">
        <h1>Customers</h1>
        <table>
          <thead>
            <tr>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Address</th>
              <th>City</th>
              <th>State</th>
              <th>Zip</th>
              <th>Phone</th>
              <th>Reset Passwd?</th>
            </tr>
          </thead>
          <tbody>
        <?php
        $res = $DB->q("select * from customers order by fname, lname");
        while($ary = mysql_fetch_assoc($res))
        {
              $row = '<tr>' . ' '
                       . "<td>" . $ary['fname'] . "</td> "
                       . "<td>" . $ary['lname'] . "</td> "
                       . "<td>" . $ary['address1'] . " " . $ary['address2'] . "</td> "
                       . "<td>" . $ary['city']. " </td> "
                       . "<td>" . $ary['state']. " </td> "
                       . "<td>" . $ary['zip'] . "</td> "
                       . "<td>" . $ary['phone'] . "</td> "
                       . "<td><input type='button' value='reset' class='resetUserPasswd' id='" . $ary['id'] . "'></td>"
                    . "</tr>";

                    echo $row;
        }
        ?>
         </tbody>
        </table>
    </div>
</div>
<div class="row">
    <div id="cmsOrders" class="large-12 columns cmsMenuItem">
       <h1>Orders</h1>
        <table>
            <thead>
                <tr>
                  <th>Order Id</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Paid</th>
                  <th>Shipping</th>
                  <th>Tax</th>
                  <th>Total</th>
                  <th>Contents</th>
                  <th>Tracking Number</th>
                </tr>
              </thead>
              <tbody>
            <?php
            $sql = "select   CONCAT(c.fname, ' ', c.lname, ' (customer#', c.id, ')') as customer_name," 
                                 ."   o.id as order_id, "
                                 ."   o.datePlaced, "
                                 ."   o.status,"
                                 ."   o.paid,"
                                 ."   o.shipping,"
                                 ."   o.tax,"
                                 ."   o.total,"
                                 ."   GROUP_CONCAT(CONCAT(oi.quantity, ' x ', p.name)) as contents,"
                                 ."   o.tracking_number"
                                 ."   from customers c "
                                 ."   join orders o "
                                 ."   on o.customer_id=c.id "
                                 ."   join order_items oi "
                                 ."   on oi.order_id = o.id "
                                 ."   join products p "
                                 ."   on p.id=oi.product_id "
                                 ."   group by o.id "
                                 ."   order by datePlaced";
            $res = $DB->q($sql);
            while($ary = mysql_fetch_assoc($res))
            {
                //construct contents
                  $row ="<tr>"
                           ." <td>" . $ary['order_id'] . "</td>"
                           ." <td>" . $ary['customer_name'] . "</td>"
                           ." <td>" . $ary['datePlaced'] . "</td>"
                           ." <td>" . $ary['status'] . "</td>"
                           ." <td>" . $ary['paid'] . "</td>"
                           ." <td>" . $ary['shipping'] . "</td>"
                           ." <td>" . $ary['tax'] . "</td>"
                           ." <td>" . $ary['total'] . "</td>"
                           ." <td>" . $ary['contents'] . "</td>"
                           ." <td><input type='text' value='" . $ary['tracking_number'] . "' id='tracking" . $ary['order_id'] . "' class='orderTrackingNumber' /></td>"
                        ."</tr>";

                        echo $row;
            }
            ?>
         </tbody>
        </table>
    </div>
</div>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>

<script src="../js/foundation.min.js"></script>
<!--

<script src="../js/foundation/foundation.js"></script>

<script src="../js/foundation/foundation.interchange.js"></script>

<script src="../js/foundation/foundation.abide.js"></script>

<script src="../js/foundation/foundation.dropdown.js"></script>

<script src="../js/foundation/foundation.placeholder.js"></script>

<script src="../js/foundation/foundation.forms.js"></script>

<script src="../js/foundation/foundation.alerts.js"></script>

<script src="../js/foundation/foundation.magellan.js"></script>
  
  <script src="../js/foundation/foundation.reveal.js"></script>
  
  <script src="../js/foundation/foundation.tooltips.js"></script>
  
  <script src="../js/foundation/foundation.clearing.js"></script>
  
  <script src="../js/foundation/foundation.cookie.js"></script>
  
  <script src="../js/foundation/foundation.joyride.js"></script>
  
  <script src="../js/foundation/foundation.orbit.js"></script>
  
  <script src="../js/foundation/foundation.section.js"></script>
  
  <script src="../js/foundation/foundation.topbar.js"></script>
  
  -->
  
  <script src="../js/classes/brittle-cart.class.js"></script>

  <script src="../js/classes/brittle-user.class.js"></script>

  <script src="../js/brittle-barn.js"></script>
  
  <? if(!BB_DEBUG){ ?>
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-45380388-1', 'brittlebarn.com');
  ga('send', 'pageview');

</script>

<? } ?>
<script src="../js/cms.js"></script>    

</body>
</html>
