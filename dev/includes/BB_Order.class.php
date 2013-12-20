<?php

class BB_Order extends BB_DB_Obj{
    private $items;

	public function BB_Order($initArray=false){
		$fieldList=array('customer_id', 'datePlaced', 'status', 'paid', 'shipping', 'tax', 'total','stripe_charge_id');
		parent::__construct('orders', $fieldList, $initArray);
	} 

    function add_item( $item_id, $item_quantity)
    {
        if(isset($this->items[$item_id]))
        {
            $this->items[$item_id] += $item_quantity;
        }
        else
        {
            $this->items[$item_id] = $item_quantity;
        }
    }

    function add_items($item_array)
    {
        foreach($item_array as $id => $item)
        {
            $this->add_item($id, $item['count']);
        }
    }

    function writeMe()
    {
        $success = parent::writeMe();

        foreach($this->items as $id => $qty)
        {
            $success = ($success && $this->q("insert into order_items(order_id, product_id,quantity) VALUES(" . $this->id . ",$id,$qty)"));
        }

        return $success;
    }

    function process($stripe_customer_id, &$error)
    {
        global $STRIPE_SECRET_KEY;
        Stripe::setApiKey($STRIPE_SECRET_KEY);

        $success = true;
        $error = "";

        // Create the charge on Stripe's servers - this will charge the user's card
        try {

            $charge = Stripe_Charge::create(array(
              "amount" => $this->total, // amount in cents, again
              "currency" => "usd",
              "customer" => $stripe_customer_id,
              "description" => "Brittle Barn Brooklyn NYC")
            );

            //if we make it here, no exception thrown
            
            if( $charge->paid == 1)
            {   
                $sql = "update orders set stripe_charge_id='" . $charge->id . "', status = 'ACCEPTED', paid=1 where id=" . $this->id;
                //$error = $sql;
                $this->q($sql);
                return true;
            }
            else
            {
                $this->q("update orders set stripe_charge_id='" . $charge->id . "', status = 'ERROR" . $charge->failure_code . "', paid=0 where id=" . $this->id);
                $error = "Stripe Error #" . $charge->failure_code . ": " . $charge->failure_message;
                return false;
            }

        } catch(Stripe_CardError $e) {
            // The card has been declined
            $success = false;
            $error = "Card Declined";
            $this->q("update orders set status = 'DECLINED' where id=$order_id");
            return false;
        }

    }

	protected function initForFirstWrite(){
		if(!isset($this->datePlaced)) $this->datePlaced=$this->SQLTimeNow();
	}
}
