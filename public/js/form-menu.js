/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



$('.duplicate').on('click', function(){
    $('.clonable').clone(true).css({'display': 'block'}).appendTo('.global').removeClass('clonable');
    var fcounted = $('.global > .tutor_stg').length;
    update_index('.global > .row.block');
});

$('body').on('click', '.substract', function () {
    $(this).closest('div.row.block').css({
        'display': 'none'
    }).removeClass('block');
    update_index('.global > .row.block');
});


$('.duplicate-1').on('click', function(){
    var blk = $('.clonable-1').clone(true).css({'display': 'block'}).removeClass('clonable-1');
    $(this).closest('.global-1').append(blk);
    update_index_sub($(this).closest('.global-1'));
});

$('body').on('click', '.substract-1', function () {
    $(this).closest('div.input-block').css({
        'display': 'none'
    }).removeClass('input-block');
    
    update_index_sub($(this).closest('.global-1'));
});


function update_index(selector){
    try{
        $(selector).each(function(ind, val){
            $(this).attr('data-block', ind);
            $(this).find('[data-option="name"]').attr('name', 'options['+ind+'][name]');
            $(this).find('[data-option="type"]').attr('name', 'options['+ind+'][type]');
            $(this).find('[data-option="item"]').attr('name', 'options['+ind+'][item]');
            $(this).find('[data-option-product="product"]').attr('name', 'options['+ind+'][productoption][0][product]');
            $(this).find('[data-option-product="price"]').attr('name', 'options['+ind+'][productoption][0][price]');
        });
    }catch(error){
        console.log(error);
    }
}


function update_index_sub(selector){
    try{
        var block = $(selector).closest('.block').data('block');
        console.log(selector);
        var sel = selector.find('.input-block');  console.log(sel);
        $(sel).each(function(ind, val){
            $(this).find('[data-option-product="product"]').attr('name', 'options['+block+'][productoption]['+ind+'][product]');
            $(this).find('[data-option-product="price"]').attr('name', 'options['+block+'][productoption]['+ind+'][price]');
        });
    }catch(error){
        console.log(error);
    }
}


//$('#search-prd').on('keypress', function(e){
//    var prd = this.value;
//    if (e.keyCode == 13){
//        $.ajax({
//            url: 'http://restau.me/search-product',
//            type: 'get',
//            data: {'product': prd},
//            headers:{
//                accept: 'application/json'
//            }
//        }).done(function(resp){
//            console.log(resp);
//        }).fail(function(xhr){
//            console.log(xhr);
//        });
//        
//        return false;
//    }
//});



$('#search-prd').autocomplete({
    source:function(request, response){
        $.ajax({
            url: "http://restau.me/search-product",
            data: {
              product: request.term
            },
            success: function(data){
                response(data);
            }
        });
    },
    minlength: 2,
    select: function(event, ui){
        console.log(ui);
        //$('div').attr('data-produit', ui.item.value).css({display:'none'}).addClass('prd-menu').appendTo('body');
    }
});