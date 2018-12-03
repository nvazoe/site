/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function(){
    $('#submit').on('click', function(){
        submit();
    });
});

function submit(){
    try{
        $('#form-contact').validate({
            rules:{
                name:{
                    required: true
                },
                email:{
                    required: true,
                    email: true
                },
                phone:{
                    number: true
                },
                subject:{
                    required: true
                },
                message:{
                    required: true,
                    minlength: 10
                }
            }
        });
        var $validated = $('#form-contact').valid();
        if($validated){
            $('#form-contact').submit();
            
        }
    }catch(error){
        console.log(error);
    }
}