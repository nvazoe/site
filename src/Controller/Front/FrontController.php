<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Entity\Menu;
use App\Entity\Restaurant;

/**
 * Description of FrontController
 *
 * @author user
 */
class FrontController extends Controller{
    
    /**
     * @Method({"GET"})
     * @Route("/home", name="home")
     * @Template("/index.html.twig")
     */ 
    public function indexAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $restaurants = $em->getRepository(Restaurant::class)->findAll();
        
        return array('restaurants' => $restaurants);
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/contact", name="contact")
     * @Template("/contact.html.twig")
     */ 
    public function contactAction(Request $request){
        
    }
    
    
    /**
     * @Method({"GET"})
     * @Route("/commander", name="commander")
     * @Template("/commander.html.twig")
     */ 
    public function commanderAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $restaurants = $em->getRepository(Restaurant::class)->findAll();
        
        return array('restaurants' => $restaurants);
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/login", name="front_login")
     * @Template("/login.html.twig")
     */
    public function loginAction(Request $request){
        
    }
}
