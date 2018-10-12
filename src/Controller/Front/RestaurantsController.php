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
 * Description of RestaurantsController
 *
 * @author user
 */
class RestaurantsController extends Controller {
    
    /**
     * Get restaurant info
     * 
     * @param Request $request
     * @param type $id
     * @return type
     * @Route("/restaurant/{id}", name="restau")
     * @Method({"GET"})
     * @Template("/restaurant-view.html.twig")
     */
    public function getRestaurant(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        return array('restaurant' => $em->getRepository(Restaurant::class)->find($id));
    }
    
    /**
     * @method({"GET"})
     * @Route("/home", name="home")
     * @Template("/home.html.twig")
     */
    public function home(Request $request){
        $em = $this->getDoctrine()->getManager();
        $restaurants = $em->getRepository(Restaurant::class)->findAll();
        
        return array('restaurants' => $restaurants);
    }
}
