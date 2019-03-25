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
        var unitPrice = parseFloat(amount)/parseInt(qty);
        if (parseInt(qty) > 1)
            $('.qty').text(parseInt(qty) - 1);
        var sol = unitPrice * parseInt($('.qty').text());
        $('.amount-menu').text(sol.toFixed(2));
    });

    $('#info div.actionqty.plus').click(function () {
        var qty = $('.qty').text();
        var amount = $('.amount-menu').text();
        var unitPrice = parseFloat(amount)/parseInt(qty);
        $('.qty').text(parseInt(qty) + 1);
        var sol = unitPrice * parseInt($('.qty').text());
        $('.amount-menu').text(sol.toFixed(2));
    });

    $('button.basket').click(function () {
        // Validate options menu
        var options = $('.blck-option');
        var error = 0;
        if(options.length > 0){
            for(var i=0, c=options.length; i<c; i++){
                if($(options[i]).attr('data-options-require') == 1){
                    var required = $(options[i]).attr('data-max-allowed');
                    var opts = $(options[i]).find('input:checked');
                    
                    if((typeof required != 'undefined') && required != opts.length){
                        error = 1;
                        console.log($(options[i]).find('.option-title > p'));
                        $(options[i]).find('.option-title > p').css({color: 'red', "font-weigt": "700"});
                    }
                }
            }
        }
        console.log(error);
        if(!error){
            add_to_basket();
            setup_cart(localStorage.cart);
            $('#info').modal('hide');
        }
        

    });

    $('.box').on('click', function () {
        $('.loader').removeClass('hide');
        $('.infomenu').addClass('hide');
        $('#unit-price').removeAttr('data-unit-price');
        var prd = $(this).attr('data-menu');
        $.ajax({
            url: $('body').data('base-url')+'api/menus/' + prd,
            type: 'get',
            crossDomain: true
        }).done(function (resp) {
            console.log(resp);
            // set value
            $('#menu-name').html(resp.data.name);
            $('#menu-name').data('menu', resp.data.id);
            $('#menu-description').html(resp.data.description);
            $('#unit-price').attr('data-unit-price', resp.data.price);
            $('.amount-menu').text(resp.data.price);
            var t = "";
            $('.qty').text(1);
            if (typeof resp.data.options !== 'undefined') {
                var options = resp.data.options; console.log(options);
                
                t += options_html(options);
            }
            //console.log(t);
            $('.block-options').html(t);
            var height = $('.side-data').height();
//            $('.bck-img').css({
//                "background-image": 'url('+resp.data.image+')',
//                "background-size": "cover",
//                "height": height
//            });
            $('.bck-img').html('<img src="'+resp.data.image+'" alt="image">');
            $('.loader').addClass('hide');
            $('.infomenu').removeClass('hide');
        }).fail();
    });
    
    
    $('.box.product').on('click', function () {
        var prd = $(this).data('product');
        $.ajax({
            url: $('body').data('base-url')+'api/products/' + prd,
            type: 'get',
            crossDomain: true
        }).done(function (resp) {
            console.log(resp);
            // set value
            $('#menu-name').html(resp.data.name);
            $('#menu-name').data('product', resp.data.id);
            $('#menu-description').html(resp.data.description);
            $('#unit-price').attr('data-unit-price', resp.data.price);
            $('.amount-menu').text(resp.data.price);
            
            $('.qty').text(1);
            var t = "";
            if (typeof resp.data.options !== 'undefined') {
                var options = resp.data.options; console.log(options);
                
                t += options_html(options);
                
            }
            //console.log(t);
            $('.block-options').html(t);
            var height = $('.side-data').height();
            $('.bck-img').html('<img src="'+resp.data.image+'" alt="image">');
        }).fail();
    });
    
    $('body').on('click', '.delete', function(){
        var id = $(this).data('command');
        $(this).closest('.item').fadeOut(1000);
        remove_from_cart(id);
    });
    
    
    $('body').on('click', '.block-options .blck-option input', function(){
        var subTotal = 0;
        var qty = parseInt($('.qty').text());
        var unitPrice = parseFloat($('#unit-price').attr('data-unit-price'));
        var inputChecked = $(".block-options .blck-option input:checked");
        console.log(inputChecked.length);
        if(inputChecked.length > 1){
            console.log(inputChecked);
            for(var i=0, c=inputChecked.length; i<c; i++){
                subTotal += parseFloat($(inputChecked[i]).attr('data-option-price'));
            }
        }else if(inputChecked.length == 1){
            subTotal += parseFloat(inputChecked.attr('data-option-price'));
        }
        console.log(subTotal);
        console.log(unitPrice);
        $('.amount-menu').html(((subTotal+unitPrice) * qty).toFixed(2));
    });
    
    $('#info').on('hide.bs.modal', function (e) {
        $('#unit-price').removeAttr('data-unit-price');
    });
    
    $('#payment').on('click', function(){
        if(typeof localStorage.amount != 'undefined' && localStorage.amount < 15){
            toastr.options.closeButton = true;
            toastr.options.timeOut = 3000; // How long the toast will display without user interaction
            toastr.options.extendedTimeOut = 6000; // How long the toast will display after a user hovers over it
            toastr.error('Votre commande doit être supérieure ou égale à 15€.');
        }else{
            window.location.replace($('body').data('base-url')+'checkout');
        }
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
        text += '<div class="blck-option"';
        if (dat[k].item_required >= 1)
            text += ' data-max-allowed="'+dat[k].item_required+'"';
        if (dat[k].type == "REQUIRED"){
            text += ' data-options-require="1"';
        }else{
            text += ' data-options-require="0"';
        }
        text += '>';
        text += '<div class="option-title" data-option="' + dat[k].id + '"><h3>';
        text += dat[k].name;
        text += '</h3>';
        if (dat[k].item_required >= 1){
            text += '<p>Choisissez ' + dat[k].item_required + '</p>';
            text += '<span class="badge">Obligatoire</span>';
        }
        text += '</div><form class="form-horizontal"><div class="form-group">';
        if(dat[k].item_required == 1){
            for (var j = 0, c = dat[k].products.length; j < c; j++) {
                text += '<div class="opt clearfix"><div class="col-sm-1"><input type="radio" name="options" data-option-name="' + dat[k].products[j].name + '" data-option-prd="' + dat[k].products[j].id + '" data-option-price="' + dat[k].products[j].price + '"></div><label class="col-sm-8">' + dat[k].products[j].name + '</label><label class="col-sm-3"><span class="price">' + dat[k].products[j].price.toFixed(2) + '</span> <span class="currency">€</span></label></div>';
            }
            text += '</div></form></div></div>';
        }else{
            for (var j = 0, c = dat[k].products.length; j < c; j++) {
                text += '<div class="opt clearfix"><div class="col-sm-1"><input type="checkbox" name="options" data-option-name="' + dat[k].products[j].name + '" data-option-prd="' + dat[k].products[j].id + '" data-option-price="' + dat[k].products[j].price + '"></div><label class="col-sm-8">' + dat[k].products[j].name + '</label><label class="col-sm-3"><span class="price">' + dat[k].products[j].price.toFixed(2) + '</span> <span class="currency">€</span></label></div>';
            }
            text += '</div></form></div></div>';
        }
        
    }
    return text;
}

function add_to_basket() {
    try {
        var restau = $('#restau').data('restau');
        var restau_id = $('#restau').data('restau-id');
        
        // Clear basket if he want to add menu from another restau
        if(typeof localStorage.restau_id != 'undefined')
            if(restau_id != localStorage.restau_id)
                localStorage.clear();
        
        
        
        
        var name = $('#menu-name').text();
        var qty = parseInt($('.qty').text());
        var amount = parseFloat($('.amount-menu').text()).toFixed(2);

        var menu_id = $('#menu-name').data('menu');

        var options = $('.blck-option');
        var menus = [];
        for (var i = 0, c = options.length; i < c; i++) {
            var m = {
                option: $(options[i]).find('.option-title').data('option')
            };
            //console.log('option = '+ $(options[i]).find('.option-title').data('option'));
            var opts = $(options[i]).find('input:checked');
            //console.log('Les opt = '+ opts.length);
            if (opts.length > 0) {
                var prods = [];
                for (var v = 0, w = opts.length; v < w; v++) {
                    var vv = {
                        id: $(opts[v]).data('option-prd'),
                        prd_name: $(opts[v]).data('option-name')
                    };
                    prods.push(vv);
                }
                //alert(prods.length);
                console.log(prods);
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
            "id": menu_id,
            "title": name,
            "amount": amount,
            "quantity": qty,
            "options": menus
        };
        $cart.push($item);
        localStorage.setItem('cart', JSON.stringify($cart));
        localStorage.setItem('restau', restau);
        localStorage.setItem('restau_id', restau_id);
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
            $html += item_basket_html($cart[a].quantity, $cart[a].title, $cart[a].amount, $cart[a].options, a);
            $amount_cart = parseFloat($amount_cart) + (parseFloat($cart[a].amount));
            $nb_art += parseInt($cart[a].quantity);
        }
        //console.log($nb_art);
        localStorage.setItem('amount', $amount_cart.toFixed(2));
        localStorage.setItem('nb_article', $nb_art);
        // update de DOM
//            $('.subtotal .price span, .counter-your-cart .counter-price span').text(localStorage.amount);
//            $('.subtitle span, span.counter.qty > span.counter-number').text(localStorage.nb_article);
//            $('.minicart-items-wrapper ol').empty().prepend($html);

        $('.box-basket').html($html);
        $('.box-infos').html(info_cart_html(localStorage.nb_article, localStorage.amount));
        
        var nb_art = parseInt($('#nbr-article').text());
        if(!nb_art){
            $('.ship-fees').fadeOut();
            var total = parseFloat($('#total-w-ship'));
            $('#total-span').text(0.00);
        }else{
            var total = parseFloat($('#total-w-ship').text()) + parseFloat($('#ship-fees').text());
            $('#total-span').text(total.toFixed(2));
        }

    } catch (error) {
        console.log(error);
    }
}

function info_cart_html(articles, montant) {
    var text = "";

    text += '<div class="row">';
    text += '<div class="col-sm-6">';
    text += 'Sous-total (<span id="nbr-article">' + articles + '</span> ';
    if (articles > 1) {
        text += 'articles)';
    } else {
        text += 'article)';
    }
    text += '</div><div class="col-sm-6"><span id="total-w-ship">';
    text += montant + '</span><span>€</span>';
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