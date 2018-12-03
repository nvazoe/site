<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Description of AppExtension
 *
 * @author user
 */
class AppExtension extends AbstractExtension {
    //put your code here
    public function getFilters()
    {
        return array(
            new TwigFilter('base64_encode', array($this, 'base64Encode')),
        );
    }
    
    public function base64Encode($string){
        return base64_encode($string);
    }
}
