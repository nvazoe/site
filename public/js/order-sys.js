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