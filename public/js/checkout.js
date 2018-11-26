/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function(){
    $('.restau-name').html(localStorage.restau);
    $('#pass-order').on('click', function(){
        make_order();
    });
});

function make_order(){
    try{
        
        // validate select
        $.validator.addMethod("valueNotEquals", function (value, element, arg) {
            return arg !== value;
        }, "Value must not equal arg.");
        
        var validator = $('#address-form').validate({
            rules:{
                city:{
                    required:true
                },
                address:{
                    required:true
                },
                phone:{
                    required:true
                },
                mpayment:{
                    valueNotEquals: 0
                }
            },
            messages:{
                city:{
                    required: 'Champ requis'
                },
                address:{
                    required: 'Champ requis'
                },
                phone:{
                    required: 'Champ requis'
                },
                mpayment:{
                    valueNotEquals: 'Choisissez un mode paiement.'
                }
            }
        });
        
        var $validated = $('#address-form').valid();
        if(!$validated){
            //alert('Erreur de renseignement');
        }else{
            order();
        }
    }catch(error){
        console.log(error);
    }
}

function order(){
    try{
        var $cart = JSON.parse(localStorage.cart);
        var pm = $('select[name="mpayment"]').val();
        if(pm == 2){
            var ticket = {
                code: "NSLO-KSNO-DMME",
                value: 4.60
            };
        }else{
            var ticket = {
                id: -1
            };
        }
        //console.log($cart);
        var data = {
            "client": 8,
            "delivery_address": $('input[name="address"]').val(),
            "delivery_phone": $('input[name="phone"]').val(),
            "delivery_city": $('input[name="city"]').val(),
            "delivery_type": $('input[name="delivery-type"]').val(),
            "delivery_note": $('input[name="delivery-note"]').val(),
            "restaurant": parseInt(localStorage.restau_id),
            "payment_mode": parseInt(pm),
            "menus": $cart,
            "creditcard": {
                id: -1
            },
            "ticket": ticket
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
            localStorage.clear();
        }).fail(function(xhr){
            console.log(xhr);
        });
        
        
        
        
    }catch(error){
        console.log(error);
    }
}