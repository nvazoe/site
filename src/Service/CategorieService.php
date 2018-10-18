<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\CategoryMenu;

/**
 * Description of Categories
 *
 * @author user
 */
class CategorieService {
    
    protected $em;
    public function __construct(\Doctrine\ORM\EntityManager $em){
        $this->em = $em;
    }
    
    public function getCategories(){
        return $this->em->getRepository(CategoryMenu::class)->findAll();
    }
}
