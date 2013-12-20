<?php
class BB_User extends BB_DB_Obj{
    
	public function BB_User($initArray=false){
        $this->doNotCache = true;
		$fieldList=array('fname','lname','email','password','address1', 'address2', 'city', 'state', 'zip', 'phone', 'exp_month', 'exp_year', 'last_four', 'stripe_customer_id');
		parent::__construct('customers', $fieldList, $initArray);
	} 

    static function hash_password($pass, $email)
    {
        return crypt($pass . $email, $email);
    }

    public function readMe($id)
    {
        if(!isset($id))
        {
            return $this->populateByEmail();
        }
        else
        {
            return parent::readMe($id);
        }
    }

    private function populateByEmail()
    {
        if(!isset($this->email))
        {
            return false;
        }
        else
        {
            $res= $this->q("SELECT * FROM customers WHERE email='" . mysql_real_escape_string($this->email) . "'");

            if(mysql_num_rows($res) == 1)
            {
                $arry = mysql_fetch_assoc($res);
                foreach($arry as $field_name => $field_value)
                {
                    $this->{$field_name} = $field_value;
                }

                return true;   
            }
            else
            {
                return false;
            }
        }
    }

    public function vals($attr_array)
    {
        foreach( $attr_array as $key => $value )
        {
            $this->val($key, $value);
        }
    }

    public function get_order_history()
    {
        $order_history = Array();
        $res = $this->q("SELECT * FROM orders WHERE status='ACCEPTED' and customer_id=" . $this->id);
        $count = 0;

        while($order_array = mysql_fetch_assoc($res))
        {
            $order_history[$count] = $order_array;

            //now get the items
            $order_history[$count]['items'] = Array();
            $item_res = $this->q("select oi.quantity, p.name, p.price*100 as price, p.size from order_items oi join products p on p.id=oi.product_id where order_id=" . $order_array['id']);

            while($item_array = mysql_fetch_assoc($item_res))
            {
                $order_history[$count]['items'][] = $item_array;
            }

            $count++;
        }
        
        return $order_history;
    }

    public function stripe_subscribe($token, &$error)
    {
        //now charge the card
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here https://manage.stripe.com/account
        global $STRIPE_SECRET_KEY;
        Stripe::setApiKey($STRIPE_SECRET_KEY);

        $success = true;
        $error = "";

        // Create the charge on Stripe's servers - this will charge the user's card
        try {

            if($this->id >= 0)
            {
                // Create a Customer
                $customer = Stripe_Customer::create(array(
                    "card" => $token,
                    "description" => "BrittleBarn.com Customer" . $this->id)
                );

                $sql = "update customers set stripe_customer_id='" . $customer->id . "' where id=" . $this->id;
                $error = $sql;
                $this->q($sql);
                $stripe_customer_id = $customer->id;
            }
            else
            {
                throw new Exception("No Customer Id");
            }
        } catch(Stripe_CardError $e) {
            // The card has been declined
            $error = "Card Declined";
            $stripe_customer_id = false;
        }

        return $stripe_customer_id;
    }
}

?>
