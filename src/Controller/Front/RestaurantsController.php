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
use App\Entity\RestaurantSpeciality;
use App\Entity\CategoryMenu;
use App\Entity\Configuration;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        $categoriesmenu = $em->getRepository(CategoryMenu::class)->findAll();
        
        return array(
            'restaurant' => $em->getRepository(Restaurant::class)->find($id),
            'categories' => $categoriesmenu,
            'base' => $this->generateUrl('commander', array(), UrlGeneratorInterface::ABSOLUTE_URL)
            );
    }
    
    /**
     * @method({"GET"})
     * @Route("/", name="homepage")
     * @Template("/index.html.twig")
     */
    public function home(Request $request){
        $em = $this->getDoctrine()->getManager();
        $restaurants = $em->getRepository(Restaurant::class)->findAll();
        $categoriesmenu = $em->getRepository(CategoryMenu::class)->findAll();
        
        return array('restaurants' => $restaurants, 'categories' => $categoriesmenu, 'base' => $this->generateUrl('commander', array(), UrlGeneratorInterface::ABSOLUTE_URL));
    }
    
    
    /**
     * @method({"GET"})
     * @Route("/category/{id}/restaurants", name="categorierestau")
     * @Template("/category-restaurants.html.twig")
     */
    public function getCategRestauAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository(CategoryMenu::class)->find($id);
        $categoriesmenu = $em->getRepository(CategoryMenu::class)->findAll();
        
        
        return array('category' => $category, 'categories' => $categoriesmenu);
    }
    
    
    /**
     * @method({"GET"})
     * @Route("/checkout", name="checkout")
     * @Template("/checkout.html.twig")
     */
    public function checkoutAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        return array(
            'base' => $this->generateUrl('commander', array(), UrlGeneratorInterface::ABSOLUTE_URL),
            'shipping_cost' => $em->getRepository(Configuration::class)->findOneByName('SHIPPING_COST')->getValue()
        );
    }
}
