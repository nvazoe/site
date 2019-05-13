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
            stripe_pk: "pk_live_eo4MYvhD0gazKbeMzchjmrSU"
        }
    }
};
var environment = 'staging';
var config = settings.environments[environment];

$('header.top-header .blck-menu i.fa-bars').click(function(){
    $('header.top-header .blck-menu ul').show('500');
    $('header.top-header .blck-menu i.fa-close').show();
    $('header.top-header .blck-menu i.fa-bars').hide();
});

$('header.top-header .blck-menu i.fa-close').click(function(){
    $('header.top-header .blck-menu ul').hide('500');
    $('header.top-header .blck-menu i.fa-close').hide();
    $('header.top-header .blck-menu i.fa-bars').show();
});