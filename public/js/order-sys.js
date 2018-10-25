/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




$(document).ready(function () {
    $('#info div.actionqty.minus').click(function () {
        var qty = $('.qty').text();
        var amount = $('.amount-menu').text();
        var unitPrice = $('button.basket').data('unit-price');
        if (parseInt(qty) > 1)
            $('.qty').text(parseInt(qty) - 1);

        $('.amount-menu').text(parseFloat(unitPrice) * parseInt($('.qty').text()));
    });

    $('#info div.actionqty.plus').click(function () {
        var qty = $('.qty').text();
        var amount = $('.amount-menu').text();
        var unitPrice = $('button.basket').data('unit-price');
        $('.qty').text(parseInt(qty) + 1);

        $('.amount-menu').text(parseFloat(unitPrice) * parseInt($('.qty').text()));
    });

    $('button.basket').click(function () {
        if ($('.box-basket > .item').length > 0) {
            $('.box-basket').append(item_basket_html(1, "Menu dest", 12));
            $('#info').modal('hide');
        } else {
            $('.box-basket').html(item_basket_html(1, "Nom du menu", 12));
            $('#info').modal('hide');
        }

    });
    
    $('.box').on('click', function(){
       var prd = $(this).data('menu');
        $.ajax({
            url: 'http://restau.me/api/menus/'+prd,
            type: 'get',
            crossDomain: true
        }).done(function(resp){
            console.log(resp);
            // set value
            $('#menu-name').html(resp.data.name);
            $('#menu-description').html(resp.data.description);
            $('#unit-price, .amount-menu').data('unit-price', resp.data.price);
            $('.amount-menu').text(resp.data.price);
            if(typeof resp.data.options !== 'undefined'){
                var options = resp.data.options;
                var t = "";
                t += options_html(options);
            }
            console.log(t);
            $('.block-options').html(t);
        }).fail();
    });


});

function item_basket_html(qty, title, amount) {
    var text = "";
    text += '<div class="item"><div class="row"><div class="col-sm-2">';
    text += qty;
    text += '</div><div class="col-sm-8">';
    text += title;
    text += '</div><div class="col-sm-2">';
    text += amount;
    text += '</div></div></div>';

    return text;
}

function options_html(dat){
    var text = "";
    
    for(k=0, g=dat.length; k<g; k++){
        text += '<div class="blck-option">';
        text += '<div class="option-title"><h3>';
        text += dat[k].name;
        text += '</h3>';
        if(dat[k].item_required !== 0)
            text += '<p>Choisissez '+dat[k].item_required+'</p>';
        text += '</div><form class="form-horizontal"><div class="form-group">';
        for(j=0, c=dat[k].products.length; j<c; j++){
            console.log(dat[k].products[j]);
            text += '<div class=""><div class="col-sm-1"><input type="radio" name="options" value="'+dat[k].products[j].id+'"></div><label class="col-sm-8">'+dat[k].products[j].name+'</label><label class="col-sm-3"><span class="price">'+dat[k].products[j].price+'</span> <span class="currency">€</span></label></div>';
        }
        text += '</div></form></div></div>';
    }
    
    
    return text;
}

function add_to_basket(){
    try{
        if (typeof localStorage.cart != 'undefined') {
            $cart = JSON.parse(localStorage.cart);
        } else {
            $cart = [];
        }
        $item = {
            "name": $name,
            "link": $url,
            "price": parseFloat($price),
            "qty": $mqo,
            "old_price": $old_price,
            "thumbnail": $image,
            "prd_id": $prd_id
        };
        $cart.push($item);
        localStorage.setItem('cart', JSON.stringify($cart));
        setup_cart(localStorage.cart);
    }catch(error){
        console.log(error);
    }
}

function setup_cart($tring) {
        try {
            var $cart, $html = "", $amount_cart = 0, $nb_art = 0;
            $cart = JSON.parse($tring);
            // set DOM cart
            for (i = 0; i < $cart.length; i++) {
                $html += html_for_cart($cart[i].thumbnail, $cart[i].link, $cart[i].name, $cart[i].price, $cart[i].old_price, $cart[i].qty);
                $amount_cart = parseInt($amount_cart) + (parseInt($cart[i].price) * $cart[i].qty);
                $nb_art += parseInt($cart[i].qty);
            }
            localStorage.setItem('amount', $amount_cart);
            localStorage.setItem('nb_article', $nb_art);
            // update de DOM
            $('.subtotal .price span, .counter-your-cart .counter-price span').text(localStorage.amount);
            $('.subtitle span, span.counter.qty > span.counter-number').text(localStorage.nb_article);
            $('.minicart-items-wrapper ol').empty().prepend($html);
        } catch (error) {
            console.log(error);
        }
    }
