/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */




$(document).ready(function () {

    //localStorage.clear();

    if (typeof localStorage.cart != 'undefined')
        setup_cart(localStorage.cart);



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

        add_to_basket();
        setup_cart(localStorage.cart);
        $('#info').modal('hide');

    });

    $('.box').on('click', function () {
        var prd = $(this).data('menu');
        $.ajax({
            url: $('body').data('base-url')+'api/menus/' + prd,
            type: 'get',
            crossDomain: true
        }).done(function (resp) {
            console.log(resp);
            // set value
            $('#menu-name').html(resp.data.name);
            $('#menu-description').html(resp.data.description);
            $('#unit-price, .amount-menu').data('unit-price', resp.data.price);
            $('.amount-menu').text(resp.data.price);
            
            $('.qty').text(1);
            if (typeof resp.data.options !== 'undefined') {
                var options = resp.data.options;
                var t = "";
                t += options_html(options);
            }
            //console.log(t);
            $('.block-options').html(t);
            var height = $('.side-data').height();
            $('.bck-img').css({
                "background-image": 'url('+resp.data.image+')',
                "background-size": "cover",
                "height": height
            });
        }).fail();
    });
    
    $('body').on('click', '.delete', function(){
        var id = $(this).data('command');
        remove_from_cart(id);
        $(this).closest('.item').fadeOut(1000);
    });

});

function item_basket_html(qty, title, amount, options, ID) {
    var text = "";
    text += '<div class="item"><div class="row"><div class="col-sm-2">';
    text += qty;
    text += '</div><div class="col-sm-8">';
    text += '<div class="menu-title">' + title + '</div>';
    text += '<div class="options"><ul class="list-unstyled">';
    if (options.length) {
        for (var i = 0, c = options.length; i < c; i++) {
            if (typeof options[i].products != 'undefined') {
                if (options[i].products.length) {
                    for (var j = 0, d = options[i].products.length; j < d; j++) {
                        text += '<li>' + options[i].products[j].prd_name + '</li>';
                    }
                }
            }
        }
    }
    text += '</ul></div>';
    text += '<div class="delete" data-command="'+ID+'">Supprimer</div>';
    text += '</div><div class="col-sm-2">';
    text += amount;
    text += '</div></div></div>';

    return text;
}

function options_html(dat) {
    var text = "";

    for (var k = 0, g = dat.length; k < g; k++) {
        text += '<div class="blck-option">';
        text += '<div class="option-title" data-option="' + dat[k].id + '"><h3>';
        text += dat[k].name;
        text += '</h3>';
        if (dat[k].item_required > 0)
            text += '<p>Choisissez ' + dat[k].item_required + '</p>';
        
        text += '</div><form class="form-horizontal"><div class="form-group">';
        if(dat[k].item_required <= 1){
            for (var j = 0, c = dat[k].products.length; j < c; j++) {
                text += '<div class="opt"><div class="col-sm-1"><input type="radio" name="options" data-option-name="' + dat[k].products[j].name + '" data-option-prd="' + dat[k].products[j].id + '" value="' + dat[k].products[j].id + '"></div><label class="col-sm-8">' + dat[k].products[j].name + '</label><label class="col-sm-3"><span class="price">' + dat[k].products[j].price + '</span> <span class="currency">€</span></label></div>';
            }
            text += '</div></form></div></div>';
        }else{
            for (var j = 0, c = dat[k].products.length; j < c; j++) {
                text += '<div class="opt"><div class="col-sm-1"><input type="checkbox" name="options" data-option-name="' + dat[k].products[j].name + '" data-option-prd="' + dat[k].products[j].id + '" value="' + dat[k].products[j].id + '"></div><label class="col-sm-8">' + dat[k].products[j].name + '</label><label class="col-sm-3"><span class="price">' + dat[k].products[j].price + '</span> <span class="currency">€</span></label></div>';
            }
            text += '</div></form></div></div>';
        }
        
    }
    return text;
}

function add_to_basket() {
    try {
        var name = $('#menu-name').text();
        var qty = parseInt($('.qty').text());
        var amount = parseFloat($('.amount-menu').text()).toFixed(2);
        var restau = $('#restau').data('restau');

        var options = $('.blck-option');
        var menus = [];
        for (var i = 0, c = options.length; i < c; i++) {
            var m = {
                option_id: $(options[i]).find('.option-title').data('option')
            };
            //console.log('option = '+ $(options[i]).find('.option-title').data('option'));
            var opts = $(options[i]).find('input:checked');
            //console.log('Les opt = '+ opts.length);
            if (opts.length > 0) {
                var prods = [];
                for (v = 0, w = opts.length; v < w; v++) {
                    var v = {
                        prd_id: $(opts[v]).data('option-prd'),
                        prd_name: $(opts[v]).data('option-name')
                    };
                    prods.push(v);
                }
                m.products = prods;
            }
            menus.push(m);
        }
        if (typeof localStorage.cart !== 'undefined') {
            var $cart = JSON.parse(localStorage.cart);
        } else {
            var $cart = [];
        }
        var $item = {
            "title": name,
            "amount": amount,
            "qty": qty,
            "options": menus
        };
        $cart.push($item);
        localStorage.setItem('cart', JSON.stringify($cart));
        localStorage.setItem('restau', restau);
        setup_cart(localStorage.cart);
    } catch (error) {
        console.log(error);
    }
}

function setup_cart($tring) {
    try {
        var $cart, $html = "", $amount_cart = 0.00, $nb_art = 0;
        $cart = JSON.parse($tring);
        console.log($cart);
        // set DOM cart
        for (var a = 0, b = $cart.length; a < b; a++) {
            $html += item_basket_html($cart[a].qty, $cart[a].title, $cart[a].amount, $cart[a].options, a);
            $amount_cart = parseFloat($amount_cart) + (parseFloat($cart[a].amount));
            $nb_art += parseInt($cart[a].qty);
        }
        
        localStorage.setItem('amount', $amount_cart.toFixed(2));
        localStorage.setItem('nb_article', $nb_art);
        // update de DOM
//            $('.subtotal .price span, .counter-your-cart .counter-price span').text(localStorage.amount);
//            $('.subtitle span, span.counter.qty > span.counter-number').text(localStorage.nb_article);
//            $('.minicart-items-wrapper ol').empty().prepend($html);

        $('.box-basket').html($html);
        $('.box-infos').html(info_cart_html(localStorage.nb_article, localStorage.amount));

    } catch (error) {
        console.log(error);
    }
}

function info_cart_html(articles, montant) {
    var text = "";

    text += '<div class="row">';
    text += '<div class="col-sm-6">';
    text += 'Sous-total (' + articles + ' ';
    if (articles > 1) {
        text += 'articles)';
    } else {
        text += 'article)';
    }
    text += '</div><div class="col-sm-6">';
    text += montant + '<span>€</span>';
    text += '</div></div>';

    return text;
}


function remove_from_cart($id) {
    try {
        var cart, position;
        cart = JSON.parse(localStorage.cart);

        cart.splice($id, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        setup_cart(localStorage.cart);
    } catch (error) {
        console.log(error);
    }
}