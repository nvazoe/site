/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


var settings = {
    environments:{
        local: {
            base_url: $('body').data('base-url'),
            stripe_pk: "pk_test_hUvysW5ZouHgLBqNi8zADyfX"
        },
        staging:{
            base_url: $('body').data('base-url'),
            stripe_pk: "pk_test_hUvysW5ZouHgLBqNi8zADyfX"
        }
    }
};
var environment = 'local';
var config = settings.environments[environment];