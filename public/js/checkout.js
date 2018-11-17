/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function(){
    $('.restau-name').html(localStorage.restau);
    $('#pass-order').on('click', function(){
        var $cart = JSON.parse(localStorage.cart);
        console.log($cart);
        var data = {
            "client": 8,
            "delivery_address": "Presta",
            "delivery_phone": "674323",
            "restaurant": 2,
            "payment_mode": 1,
            "menus": $cart,
            "creditcard": {
                id: -1
            }
        };
        
        $.ajax({
            url: $('body').data('base-url') + 'api/orders',
            type: 'post',
            headers: {
                "content-type": 'application/json',
                "accept": "application/json"
            },
            data: JSON.stringify(data),
            crossDomain: true
        }).done(function(resp){
            console.log(resp);
        }).fail(function(xhr){
            console.log(xhr);
        });
        
    });
});