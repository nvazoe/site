<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Configuration;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Util\LegacyFormHelper;
use App\Entity\User;
use App\Entity\Configuration;

/**
 * Description of ConfigurationController
 *
 * @author user
 */
class ConfigurationController extends BaseAdminController {
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/admin/stripe-config", name="stripe_config")
     * @Template("/admin/configuration/stripe-config.html.twig")
     */ 
    public function stripeConfigAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $stripe_id = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_ID');
        $stripe_secret = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_SECRET');
        $stripe_commission = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_COMMISSION');
        $rayon_resto = $em->getRepository(Configuration::class)->findOneByName('RESTAURANT_RANGE');
        $rayon_liv = $em->getRepository(Configuration::class)->findOneByName('DELIVER_RANGE');
        $admail = $em->getRepository(Configuration::class)->findOneByName('AZ_ADMIN_EMAIL');
        if($request->isMethod('POST')){
            $info = $request->get('data'); //echo '<pre>'; die(var_dump($info)); echo '</pre>';
            $stripe_id->setValue($info['id']);
            $stripe_secret->setValue($info['secret']);
            $stripe_commission->setValue($info['commission']);
            $rayon_resto->setValue($info['rayon_restaurant']);
            $rayon_liv->setValue($info['rayon_livreur']);
            $admail->setValue($info['admail']);
            $em->flush();
            
            $this->addFlash('success', "Configurations mises à jour.");
        }
        
        return array(
            'id'=>$stripe_id->getValue(), 
            'secret'=>$stripe_secret->getValue(),
            'commission'=>$stripe_commission->getValue(),
            'rayon_resto'=> $rayon_resto->getValue(),
            'rayon_liv'=> $rayon_liv->getValue(),
            'admail'=> $admail->getValue(),
            );
    }
}
