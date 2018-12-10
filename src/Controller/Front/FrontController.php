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
use App\Entity\Configuration;
use App\Entity\User;
use App\Entity\Ticket;
use App\Entity\BankCard;
use App\Entity\Order;
use App\Entity\DeliveryProposition;
use App\Entity\PaymentMode;
use App\Entity\OrderStatus;
use App\Entity\OrderDetails;
use App\Entity\MenuOption;
use App\Entity\OrderDetailsMenuProduct;
use App\Entity\MenuOptionProducts;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
    public function contactAction(Request $request, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();
        
        if($request->isMethod('post')){
            $nom = $request->get('name');
            $prenom = $request->get('prenom');
            $email = $request->get('email');
            $tel = $request->get('tel');
            $message = $request->get('message');
            $subject = $request->get('subject');
            $emailAdmin = $em->getRepository(Configuration::class)->findOneByName('AZ_ADMIN_EMAIL')->getValue();
            
            // Send mail to admin
            $message2 = (new \Swift_Message($subject))
                ->setFrom($email, $nom.' '.$prenom)
                ->setTo($emailAdmin)
                ->setBody(
                    $this->renderView(
                        // templates/emails/registration.html.twig
                        'emails/mail-from-client.html.twig', array('message' => $message, 'tel' => $tel)
                    ), 'text/html'
                )
                ->setCharset('utf-8')
            /*
             * If you also want to include a plaintext version of the message
              ->addPart(
              $this->renderView(
              'emails/registration.txt.twig',
              array('name' => $name)
              ),
              'text/plain'
              )
             */
            ;

            $mailer->send($message2);
            
            $this->addFlash('success', 'Votre message a été envoyé.');
            return $this->redirectToRoute('homepage');
        }
    }
    
    
    /**
     * @Method({"GET"})
     * @Route("/commander", name="commander")
     * @Template("/commander.html.twig")
     */ 
    public function commanderAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $restaurants = $em->getRepository(Restaurant::class)->findBy(array('status'=>1));
        
        return array('restaurants' => $restaurants);
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/login", name="front_login")
     * @Template("/login.html.twig")
     */
    public function loginAction(Request $request){
        
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/payment-process", name="save_credit")
     */
    public function chargeAction(Request $request, \Swift_Mailer $mailer, UserPasswordEncoderInterface $encoder) {
        $em = $this->getDoctrine()->getManager();
        $token = $request->get('stripeToken');
        $email = $request->get('email');
        $menus = json_decode($request->get('menus'), true);
        $restauId = $request->get('restau');
        $delivery_type = $request->get('delivery-type');
        $name = $request->get('firstname');
        $delivery_date = $request->get('delivery-date'); 
        $date = date_create_from_format('l, d M', $delivery_date); //die(var_dump($date));
        $delivery_hour = $request->get('delivery-hour');
        $delivery_note = $request->get('delivery-note');
        $delivery_address = $request->get('address');
        $delivery_phone = $request->get('phone');
        $delivery_city = $request->get('city');
        $delivery_cp = $request->get('cp');
        $pymde = $request->get('mpayment');
        $vTicket = $request->get('value-ticket');
        $total = 0.00;
        $delivers = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        
        $azCommission = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_COMMISSION')->getValue();
        $adminEmail = $em->getRepository(Configuration::class)->findOneByName('AZ_ADMIN_EMAIL')->getValue();
        
        try{
            $user = $em->getRepository(User::Class)->findOneByEmail($email);
            
            $stripePublicKey = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_SECRET')->getValue();
            // Set your secret key: remember to change this to your live secret key in production
            // See your keys here: https://dashboard.stripe.com/account/apikeys
            \Stripe\Stripe::setApiKey($stripePublicKey);
            // Create a Customer:
            $customer = \Stripe\Customer::create([
                'source' => $token,
                'email' => $email,
                'description' => $name
            ]);
        
            //echo '<pre>'; die(var_dump($customer)); echo '</pre>';

            // Create user account if not
            if(!$user){
                $user = new User();
                $user->setEmail($email);
                $user->setFirstname($name);
                $user->setRoles(['ROLE_USER']);
                $user->setConnectStatus(0);
                $user->setPassword($encoder->encodePassword($user, "pass"));
                

                if($pymde == 1){
                    

                    // Created Card 
                    $carte = $customer->sources->data[0];

                    //echo '<pre>'; die(var_dump($carte)); echo '</pre>';

                    $card = new BankCard();
                    $card->setUser($user);
                    $card->setMonthExp($carte->exp_month);
                    $card->setYearExp($carte->exp_year);
                    $card->setCardNumber($carte->last4);
                    $card->setStripeId($carte->id);
                    $card->setOwnerName($name);
                    
                    $user->setStripeId($customer->id);
                    
                    $em->persist($user);
                    $em->persist($card);
                    
                    
                }

            }else{
                
                if($pymde == 1){
                    $carte = $customer->sources->data[0];
                    $cardChecked = $em->getRepository(BankCard::class)->findOneBy(array('stripeId'=>$carte->id));
                    if(!$cardChecked){
                        $card = new BankCard();
                        $card->setUser($user);
                        $card->setMonthExp($carte->exp_month);
                        $card->setYearExp($carte->exp_year);
                        $card->setCardNumber($carte->last4);
                        $card->setStripeId($carte->id);
                        $card->setOwnerName($name);

                        $user->setStripeId($customer->id);

                        $em->persist($user);
                        $em->persist($card);
                    }
                }
                
            }



            $reference = substr(strtoupper(md5(random_bytes(6))), 0, 6);

            // save order params
            $order = new Order();
            $order->setClient($user);
            $order->setRef($reference);
            $order->setRestaurant($em->getRepository(Restaurant::class)->find($restauId));
            $order->setAddress($delivery_address);
            $order->setPhoneNumber($delivery_phone);
            $order->setcity($delivery_city);
            $order->setDeliveryHour($delivery_hour);
            $order->setDeliveryDate($date);
            $order->setcp($delivery_cp);
            $order->setDeliveryNote($delivery_note);
            $order->setDeliveryType($delivery_type);
            $order->setCommission(intval($azCommission) / 100);

            $order->setPaymentMode($em->getRepository(PaymentMode::class)->find($pymde));
            $order->setOrderStatus($em->getRepository(OrderStatus::class)->find(1));

            if($pymde == 2){
                $tkt = new Ticket();
                $tkt->setClient($user);
                $tkt->setCode("code");
                $tkt->setRestaurant($em->getRepository(Restaurant::class)->find($restauId));
                $tkt->setDateCreated(new \DateTime());
                $tkt->setValue($vTicket);
                $tkt->setValid(1);
                $em->persist($tkt);

                $order->setTicket($tkt);
            }


            $em->persist($order);

            // Save order details
            foreach ($menus as $m) {
                $ordDt = new OrderDetails();
                $menu = $em->getRepository(Menu::class)->find($m['id']);
                $ordDt->setCommand($order);
                $ordDt->setmenu($menu);
                $ordDt->setPrice($menu->getPrice());
                $ordDt->setMenuName($menu->getName());
                $ordDt->setQuantity($m['quantity']);
                $total += $menu->getPrice() * (int) $m['quantity'];
                $em->persist($ordDt);

                if (count($m['options'])) {
                    foreach ($m['options'] as $o) {
                        $menuOption = $em->getRepository(MenuOption::class)->find($o['option']);
                        if(isset($o['products'])){
                            foreach ($o['products'] as $op) {
                                $prd = $em->getRepository(MenuOptionProducts::class)->find($op['id']);

                                $ordDtPrd = new OrderDetailsMenuProduct();
                                $ordDtPrd->setOrderDetails($ordDt);
                                $ordDtPrd->setMenu($menu);
                                $ordDtPrd->setProduct($prd->getProduct());
                                $ordDtPrd->setPrice($prd->getAttribut());
                                $total += $ordDtPrd->getPrice() * (int) $m['quantity'];
                            }
                            $em->persist($ordDtPrd);
                        }


                    }
                }
            }


            //propositions to dlivers
            foreach ($delivers as $dl){
                $delProposition = new DeliveryProposition();
                $delProposition->setRestaurant($em->getRepository(Restaurant::class)->find($restauId));
                $delProposition->setValueResto(1);
                $delProposition->setDeliver($dl);
                $delProposition->setValueDeliver(0);
                $delProposition->setCommand($order);

                $em->persist($delProposition);

                // Send mail to delivers
                $message2 = (new \Swift_Message('Nouvelle commande à livrer'))
                    ->setFrom($adminEmail)
                    ->setTo($dl->getEmail())
                    ->setBody(
                        $this->renderView(
                            // templates/emails/registration.html.twig
                            'emails/new-order-to-deliver.html.twig', array('name' => $dl->getFirstname(), 'order' => $order)
                        ), 'text/html'
                    )
                    ->setCharset('utf-8')
                /*
                 * If you also want to include a plaintext version of the message
                  ->addPart(
                  $this->renderView(
                  'emails/registration.txt.twig',
                  array('name' => $name)
                  ),
                  'text/plain'
                  )
                 */
                ;

                $mailer->send($message2);
            }



            //echo '<pre>'; die(var_dump($customer)); echo '</pre>';

            // Charge the Customer instead of the card:
    //        $charge = \Stripe\Charge::create([
    //                'amount' => 1000,
    //                'currency' => 'usd',
    //                'customer' => $customer->id,
    //        ]);

            // YOUR CODE: Save the customer ID and other info in a database for later.
            // When it's time to charge the customer again, retrieve the customer ID.
    
            $order->setAmount($total);
            $em->flush();
            //echo '<pre>'; die(var_dump($charge)); echo '</pre>';
            $this->addFlash('success', "Votre commande a été enregistrée.");

            return $this->redirectToRoute('commander');
        }catch(\Exception $e){
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('checkout');
        }
    }

}
