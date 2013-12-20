function addToCartOnReady()
{
    PopulateCartSummary();
}

function UpdateCartSummary(id) {
    that = $("#addToCartItemCount" + id);
    var price = that.data('price');
    var quantity = that.val();

    var new_total = (price*quantity);
    $("#addToCartRowTotal" + id).html('$' + new_total.toFixed(2));

    //now update shopping cart
    var cart = new brittleCart();
    var subtotal=0, items = cart.getItems();

    for( var item_id in items )
    {
        var item = items[item_id]['item'];
        var rowTotal = Number(item['price']*items[item_id]['count']);
        subtotal += rowTotal
    }

    $("#addToCartSubtotalCell").html('$' + subtotal.toFixed(2));
}

function RemoveCartItem(id)
{
    var cart = new brittleCart();
    cart.removeItem(id);
    PopulateCartSummary();
}

function PopulateCartSummary()
{
    var cart = new brittleCart();
    var items = cart.getItems();

    var html = '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="cart-summary">';
    var subtotal = 0;

    for( var item_id in items )
    {
        var item = items[item_id]['item'];
        var rowTotal = Number(item['price']*items[item_id]['count']);
        subtotal += rowTotal
        html += '<tr><td width="10%"><input type="text" size="4" value="' + items[item_id]['count'] + '" id="addToCartItemCount'+item_id+'" class="addToCartItemCount" onChange="UpdateCartSummary('+item_id+');" data-price="'+item['price']+'"></td><td width="75%">' + item['name'] + '</td><td width="5%"><a href="#" style="color:red;" onClick="RemoveCartItem('+item_id+')">remove</a></td><td width="10%" class="green" id="addToCartRowTotal'+item_id+'">$' + rowTotal.toFixed(2) +'</td></tr>';
    }

    html += '<tr><td width="5%"></td><td width="85%">Subtotal<td><td width="10%" class="green" id="addToCartSubtotalCell">$' + subtotal.toFixed(2) +'</td></tr>';
    $("#addToCartSummary").html(html);

}

function OrderTotal()
{
    var subtotal = parseFloat(cart.getSubtotal()), shipping = parseFloat(cart.getShipping()), tax = parseFloat(cart.getTax());

    if(!isNaN(subtotal+shipping+tax))
    {
        total = (subtotal+shipping+tax);
        total.toFixed(2);
        cart.setTotal(total);
    }
    else
    {  
        total = 'TBD';
    }
    
//    total = String(subtotal)+String(shipping)+String(tax);
    return total;
}

function CheckoutTax(subtotal)
{
    var tax = Number(subtotal * tax_rate).toFixed(2);
    cart.setTax(tax);
    return tax;
}

function PopulateCart(items)
{
    var subtotal = 0;

    for( var item_id in items )
    {
        subtotal += Number(items[item_id]['total']);
        var item = items[item_id]['item'];
        html += '<tr><td width="5%">' + items[item_id]['count'] + '</td><td width="85%">' + item['name'] + '<td><td width="10%" class="green">$' + items[item_id]['total'] +'</td></tr>';
    }

    var tax = CheckoutTax(subtotal), shipping = cart.getShipping(), total;

    //keep track of this stiuff in the cart
    cart.setSubtotal(subtotal);
    cart.setShipping(shipping);
    cart.setTax(tax);
    
    //now we can get the total
    total = OrderTotal();

    html += '<tr><td width="5%"></td><td width="85%">Subtotal<td><td width="10%" class="green">$' + subtotal.toFixed(2) +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Tax<td><td width="10%" class="green">$' + tax  +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Shipping<td><td width="10%" id="shippingCostTD" class="green">$' + shipping +'</td></tr>';
    html += '<tr><td width="5%"></td><td width="85%">Total<td><td width="10%" id="shoppingCartTotal" class="green">$' + total +'</td></tr>';
    html += '</table>';

    $("#shoppingCartInfo").html(html);

}

