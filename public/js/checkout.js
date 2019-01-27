/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function(){
    toastr.options.closeButton = true;
    
    // Create a Stripe client.
    var stripe = Stripe(config.stripe_pk);
    console.log(config.stripe_pk);
    // Create an instance of Elements.
    var elements = stripe.elements();

    // Custom styling can be passed to options when creating an Element.
    // (Note that this demo uses a wider set of styles than the guide below.)
    var style = {
        base: {
            color: '#32325d',
            lineHeight: '18px',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };

    // Create an instance of the card Element.
    var card = elements.create('card', {style: style});
    
    var mode_payment = $('#select-payment');
    
    mode_payment.on('change', function () {
            var val = $(this).val();
            if (val == 2) {
                $('#form-ticket').removeClass('hidden');
            } else {
                $('#form-ticket').addClass('hidden');
            }

            if (val == 1) {
                $('#form-card').removeClass('hidden');
                

                // Add an instance of the card Element into the `card-element` <div>.
                card.mount('#card-element');
                

                // Handle real-time validation errors from the card Element.
                card.addEventListener('change', function (event) {
                    var displayError = document.getElementById('card-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                    } else {
                        displayError.textContent = '';
                    }
                });
            } else {
                $('#form-card').addClass('hidden');
            }
        });
    
    
    
    // Affiche le nom du restaurant
    $('.restau-name').html(localStorage.restau);
    
    $('#pass-order').on('click', function(){
        if(mode_payment.val() == 1)
            make_order_cb(stripe, card);
        else
            make_order_tkt();
    });
    
    
    // set delivery plan
    $('body').on('click', '#setPlan', function(){
        var deliv_date = $('select[name="delivery-date"]').val();
        var deliv_hour = $('select[name="delivery-hour"]').val();
        var plan = deliv_date+' '+deliv_hour;
        
        $('#getPlan').html(plan);
        $('#classPage').html('<a href="#" data-toggle="modal" data-target="#myModal2">Maintenant</a>');
    });
    
    
    $('body').on('click', '#setPlan2', function(){
        
        $('#getPlan').html("Dès que possible.");
        $('#classPage').html('<a href="#" data-toggle="modal" data-target="#myModal">Planifier</a>');
    });
    
});

function make_order_cb(stripe, card){
    try{
        
        // validate select
        $.validator.addMethod("valueNotEquals", function (value, element, arg) {
            return arg !== value;
        }, "Value must not equal arg.");
        
        var validator = $('#payment-form').validate({
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
                },
                email:{
                    required: true,
                    email: true
                },
                firstname:{
                    required: true,
                    minlength: 2
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
        
        var $validated = $('#payment-form').valid();
        if(!$validated){
            //alert('Erreur de renseignement');
        }else{
            // Handle form submission.
            var form = document.getElementById('payment-form');
            form.addEventListener('submit', function(event) {
              event.preventDefault();

              stripe.createToken(card).then(function(result) {
                if (result.error) {
                  // Inform the user if there was an error.
                  var errorElement = document.getElementById('card-errors');
                  errorElement.textContent = result.error.message;
                } else {
                  
                  console.log(result.token);
                  stripeTokenHandler(result.token);
                }
              });
            });
            //order(stripe, card);
        }
    }catch(error){
        console.log(error);
    }
}


// Submit the form with the token ID.
function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    var form = document.getElementById('payment-form');
    
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripeToken');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);
    
    // Send the token to your server.
    var cart = JSON.parse(localStorage.cart);
    var hiddenInputMenu = document.createElement('input');
    hiddenInputMenu.setAttribute('type', 'hidden');
    hiddenInputMenu.setAttribute('name', 'menus');
    hiddenInputMenu.setAttribute('value', JSON.stringify(cart));
    form.appendChild(hiddenInputMenu);
    
    var resto = localStorage.restau_id;
    var hiddenInputRestau = document.createElement('input');
    hiddenInputRestau.setAttribute('type', 'hidden');
    hiddenInputRestau.setAttribute('name', 'restau');
    hiddenInputRestau.setAttribute('value', resto);
    form.appendChild(hiddenInputRestau);
    
    
    var hiddenInputDH = document.createElement('input');
    hiddenInputDH.setAttribute('type', 'hidden');
    hiddenInputDH.setAttribute('name', 'delivery-hour');
    hiddenInputDH.setAttribute('value', $('select[name="delivery-hour"]').val());
    form.appendChild(hiddenInputDH);
    
    
    var hiddenInputDD = document.createElement('input');
    hiddenInputDD.setAttribute('type', 'hidden');
    hiddenInputDD.setAttribute('name', 'delivery-date');
    hiddenInputDD.setAttribute('value', $('select[name="delivery-date"]').val());
    form.appendChild(hiddenInputDD);
    
    localStorage.clear();
    
    // Submit the form
    form.submit();
}


function make_order_tkt(){
    try{
        
        // validate select
        $.validator.addMethod("valueNotEquals", function (value, element, arg) {
            return arg !== value;
        }, "Value must not equal arg.");
        
        var validator = $('#payment-form').validate({
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
                },
                email:{
                    required: true,
                    email: true
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
        
        var $validated = $('#payment-form').valid();
        if(!$validated){
            //alert('Erreur de renseignement');
        }else{
            // Handle form submission.
            var form = document.getElementById('payment-form');
            
            var hiddenInput = document.createElement('input');
            hiddenInput.setAttribute('type', 'hidden');
            hiddenInput.setAttribute('name', 'value-ticket');
            hiddenInput.setAttribute('value', $('input[name="ticket-value"]').val());
            form.appendChild(hiddenInput);// Send the token to your server.
            
            
            var cart = JSON.parse(localStorage.cart);
            var hiddenInputMenu = document.createElement('input');
            hiddenInputMenu.setAttribute('type', 'hidden');
            hiddenInputMenu.setAttribute('name', 'menus');
            hiddenInputMenu.setAttribute('value', JSON.stringify(cart));
            form.appendChild(hiddenInputMenu);

            var resto = localStorage.restau_id;
            var hiddenInputRestau = document.createElement('input');
            hiddenInputRestau.setAttribute('type', 'hidden');
            hiddenInputRestau.setAttribute('name', 'restau');
            hiddenInputRestau.setAttribute('value', resto);
            form.appendChild(hiddenInputRestau);
            
            
            var hiddenInputDH = document.createElement('input');
            hiddenInputDH.setAttribute('type', 'hidden');
            hiddenInputDH.setAttribute('name', 'delivery-hour');
            hiddenInputDH.setAttribute('value', $('select[name="delivery-hour"]').val());
            form.appendChild(hiddenInputDH);


            var hiddenInputDD = document.createElement('input');
            hiddenInputDD.setAttribute('type', 'hidden');
            hiddenInputDD.setAttribute('name', 'delivery-date');
            hiddenInputDD.setAttribute('value', $('select[name="delivery-date"]').val());
            form.appendChild(hiddenInputDD);
            
            
            localStorage.clear();
            form.submit();
        }
    }catch(error){
        console.log(error);
    }
}