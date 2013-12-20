
function brittleCart()
{
    var items = {};
    var loadedItems = false;
    var order_id = false;
    var shipping = 'TBD';
    var tax = 'TBD';
    var subtotal = 'TBD';
    var total = 'TBD';

    this.clearCart = function() 
    {
        items = {};
        saveCart();
    }

    function loadCart() {
        var c_value = document.cookie;
        var c_start = c_value.indexOf("brittleCartContents=");

        if (c_start == -1)
        {
            return
        }
        else
        {
            c_start = c_value.indexOf("=", c_start) + 1;
            var c_end = c_value.indexOf(";", c_start);

            if (c_end == -1)
            {
                c_end = c_value.length;
            }

            var itemString = getCookie("brittleCartContents");
            items = JSON.parse(itemString);
       }
    }

    this.verifyPrices = function( ajax_destination, callback )
    {
        loadCart();
        var item_ids = new Array();

        for( var key in items )
        {
            item_ids[item_ids.length] = key;
        }

        $.get(ajax_destination, { "ids" : item_ids }, function( response )
        {
            var return_items = items;

            for( key in return_items )
            {
                return_items[key]['price'] = Number(response[key]['price']).toFixed(2);
                return_items[key]['total'] = Number(response[key]['price']*return_items[key]['count']).toFixed(2);
            }

            //callback
            window[callback](return_items);
        }, 'json');
    }

    this.getItems = function()
    {
        loadCart();
        return items;
    }

    function saveCart() {
        var seconds = (60*6*24*7);
        var c_value="brittleCartContents=" + escape(JSON.stringify(items)) + "; max-age=" + String(seconds) +  ";";
        document.cookie = c_value;
    }

   function getCookie(c_name) {
        var i, x, y, ARRcookies = document.cookie.split(";");
        for (i = 0; i < ARRcookies.length; i++) {
            x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
            y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
            x = x.replace(/^\s+|\s+$/g, "");
            if (x == c_name) {
                return unescape(y);
            }
    }
    } 

    this.getOrderId = function() {
        return order_id;
    };

    this.setOrderId = function(new_order_id) {
        order_id = new_order_id;
    };

    this.addItem =  function( item, quantity ) {
        if(quantity === undefined)
        {
            //assume it's one
            quantity = 1;
        }

        if(!loadedItems)
        {
            loadCart();
            loadedItems = true;
        }

        if(items[item.id] === undefined)
        {
            items[item.id] = { 'item' : item, 'count' : quantity };
        }
        else
        {
            items[item.id]['count'] = Number(items[item.id]['count'])+Number(quantity);
        }

        saveCart();
    };

    this.estimateWeight = function() {
        loadCart();
        //right now, we'll just call it 6 oz per item
        var ounces = (items.length * 6);
        var pounds = Math.floor(ounces/16);
        ounces = (ounces%16);

        return { 'pounds' : pounds, 'ounces' : ounces };
    };


    //setter & getters
    this.setTax = function(tax_cost) {
        tax = tax_cost;
    }

    this.getTax = function() {
        if(tax != 'TBD')
        {
            return Number(tax);
        }
        else
        {
            return tax;
        }
    }

    this.setSubtotal = function(subtotal_cost) {
        subtotal = subtotal_cost;
    }

    this.getSubtotal = function() {

        if(subtotal!='TBD')
        {
            return Number(subtotal);
        }
        else
        {
            return subtotal;
        }
    }

    this.setTotal = function(total_cost) {
        total = total_cost;
    }

    this.getTotal = function() {
        
        if(total != 'TBD')
        {
            return Number(total);
        }
        else
        {
            return total;
        }
    }

    this.setShipping = function(shipping_cost) {
        shipping = shipping_cost
    }

    this.getShipping = function() {

        if(shipping != 'TBD')
        {
            return parseFloat(shipping).toFixed(2);
        }
        else
        {
            return shipping;
        }
    }
}

function brittleCartItem(id, price, name) {
    this.price = price.toFixed(2);
    this.id = id;
    this.name = name;
}

