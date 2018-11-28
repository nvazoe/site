/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function(){
    toastr.options.closeButton = true;
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
        
        var validator = $('#address-form, #ticket-form').validate({
            rules:{
                city:{
                    required:true
                },
                address:{
                    required:true
                },
                phone:{
                    required:true,
                    number: true
                },
                mpayment:{
                    valueNotEquals: 0
                },
                cp:{
                    required: true,
                    number: true
                },
                'ticket-value':{
                    number: true
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
                    required: 'Champ requis',
                    number: 'Valeur numérique réquise'
                },
                mpayment:{
                    valueNotEquals: 'Choisissez un mode paiement.'
                },
                cp:{
                    required: 'Champ requis',
                    number: 'Valeur numérique réquise'
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
            "delivery_cp": $('input[name="cp"]').val(),
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
            toastr.info('Votre commande a été enregistrée.');
            localStorage.clear();
        }).fail(function(xhr){
            if(typeof xhr.responseText != 'undefined'){
                var resp = JSON.parse(xhr.responseText);
                if(resp.code == 4025){
                    //alert("Vous devez choisir un mode de paiement.");
                    toastr.error('Vous devez choisir un mode de paiement.');
                }
                if(resp.code == 4000)
                    toastr.error('Paramètres invalides.');
            }
        });
        
        
        
        
    }catch(error){
        console.log(error);
    }
}