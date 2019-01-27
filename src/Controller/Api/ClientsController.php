<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Swagger\Annotations as SWG;
use Symfony\Component\Filesystem\Filesystem;
use GuzzleHttp\Client;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use App\Entity\User;
use App\Entity\Restaurant;
use App\Entity\Menu;
use App\Entity\Configuration;
use App\Entity\BankCard;
use App\Entity\Ticket;
use App\Entity\ShippingNote;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Util\TokenGenerator;
use Exception;
use Psr\Log\LoggerInterface;
use App\Entity\ConnexionLog;

/**
 * Description of ClientsController
 *
 * @author user
 */
class ClientsController extends Controller {
    
    /**
     * @Get("/api/clients")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get clients list"
     * )
     * 
     * @QueryParam(
     *      name="limit",
     *      description="limit per page",
     *      strict=false,
     *      default=100
     * )
     * 
     * @QueryParam(
     *      name="page",
     *      description="Page of set",
     *      strict=false,
     *      default=1
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function getClientListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $menus = $em->getRepository(User::class)->findAllUserByRole('ROLE_CLIENT', false);
        $array = [];
        foreach ($menus as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["firstname"] = $l->getFirstname();
            $array[$k]["lastname"] = $l->getLastname();
            $array[$k]["username"] = $l->getUsername();
            $array[$k]["email"] = $l->getEmail();
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = count($array);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }
           
        
        return new JsonResponse($result, $result['code'] = 200);
    }
    
    
    /**
     * @Get("/api/clients/{id}")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get Client informations"
     * )
     *
     * 
     * @SWG\Tag(name="Clients")
     */
    public function getClientAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository(User::class)->find(intval($id));
        
        if(!is_null($client)){
            if(!in_array('ROLE_CLIENT', $client->getRoles())){
                $result = array('code'=>400, 'description'=>"Ce client n'existe pas.");
            }else{
                $result['code'] = 200;
                $result['data']['id'] = $client->getId();
                $result['data']['username'] = $client->getUsername();
                $result['data']['firstname'] = $client->getFirstname();
                $result['data']['lastname'] = $client->getLastname();
                $result['data']['email'] = $client->getEmail();
                $result['data']['phone'] = $client->getPhonenumber();
            }
        }else{
            $result['code'] = 400;
            $result['description'] = "Ce client n'existe pas.";
        }
        
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Post("/api/clients")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new client."
     * )
     * 
     * @QueryParam(
     *      name="username",
     *      description="username of client",
     *      strict=true
     * )
     * @QueryParam(
     *      name="firstname",
     *      description="Firstname of client",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="lastname",
     *      description="Lastname of the client",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="latitude",
     *      description="Position latitude of the client",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="longitude",
     *      description="Position longitude of the client",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="phone_number",
     *      description="Phone number of the client",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="email",
     *      description="Email",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="password",
     *      description="password of client",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function postClientAction(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();
        $username = $request->query->get('username')?$request->query->get('username'):$request->request->get('username');
        $firstname = $request->query->get('firstname')?$request->query->get('firstname'):$request->request->get('firstname');
        $lastname = $request->query->get('lastname')?$request->query->get('lastname'):$request->request->get('lastname');
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        $phone = $request->query->get('phone_number')?$request->query->get('phone_number'):$request->request->get('phone_number');
        $password = $request->query->get('password')?$request->query->get('password'):$request->request->get('password');
        $email = $request->query->get('email')?$request->query->get('email'):$request->request->get('email');
        $emailAdmin = $em->getRepository(Configuration::class)->findOneByName('AZ_ADMIN_EMAIL')->getValue();
        
        /** Validate inputs email**/
        if(!is_null($email)){
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result = array('code' => 4011, 'description' => "Invalid address email."); 
                return new JsonResponse($result, 400);
            }
            $mail = $em->getRepository(User::class)->findOneByEmail($email);
            if($mail){
                $result = array('code' => 4012, 'description' => "Email already exist."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "email is required."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate inputs password **/
        if(!is_null($password)){
            if (!is_string($password)) {
                $result = array('code' => 4000, 'description' => "password must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "password is required."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate inputs username **/
        if(!is_null($username)){
            if (!is_string($username)) {
                $result = array('code' => 4000, 'description' => "username must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "username is required."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate inputs firstname **/
        if(!is_null($firstname)){
            if (!is_string($firstname)) {
                $result = array('code' => 4000, 'description' => "firstname must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "firstname is required."); 
            return new JsonResponse($result, 400);
        }
        
        $code = "";
        for ($i = 0; $i<4; $i++) 
        {
            $code .= mt_rand(0,9);
        }
        $tokenGenerator = new TokenGenerator();
        $codeGenerate = $tokenGenerator->generateToken();
        $url = $this->generateUrl('activate_account', array('code' => $codeGenerate), UrlGeneratorInterface::ABSOLUTE_URL);
            
        $del = new User();
        $del->setUsername($username);
        $del->setFirstname($firstname);
        $del->setLongitude($longitude);
        $del->setLatitude($latitude);
        $del->setLastname($lastname);
        $del->setPhoneNumber($phone);
        $del->setCode($code);
        $del->setgeneratedCode($codeGenerate);
        $del->setEmail($email);
        $del->setState(0);
        $del->setconnectStatus(0);
        $del->setPassword($encoder->encodePassword($del, $password));
        $del->setRoles(["ROLE_CLIENT"]);
        $em->persist($del);
        $em->flush();
        
        
        
        /** Sending code by email **/
        $message = (new \Swift_Message('Validation de compte'))
            ->setFrom($emailAdmin)
            ->setTo($email);
            $htmlBody = $this->renderView(
                'emails/registration.html.twig',
                    array('name' => $firstname, 'code' => $code, 'link' => $url)
            );
            
            $context['titre'] = 'Validation de compte';
            $context['contenu_mail'] = $htmlBody;
            $message->setBody(
                $this->renderView('mail/default.html.twig', $context),
                'text/html'
            );
            $message->setCharset('utf-8');
            

        $mailer->send($message);

        
        
        $result['code'] = 201;
        $result['data']['client_id'] = $del->getId();
        $result['data']['firstname'] = $del->getfirstname();
        $result['data']['email'] = $del->getEmail();
        $result['data']['phone_number'] = $del->getPhoneNumber();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Get("/api/clients/{id}/orders")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="List orders of a client."
     * )
     * 
     * @QueryParam(
     *      name="limit",
     *      description="limit per page",
     *      strict=false,
     *      default=100
     * )
     * 
     * @QueryParam(
     *      name="page",
     *      description="Page of set",
     *      strict=false,
     *      default=1
     * )
     * 
     * @QueryParam(
     *      name="status",
     *      description="order status ID",
     *      strict=false
     * )
     * 
     * @QueryParam(
     *      name="restaurant",
     *      description="restaurant ID",
     *      strict=false
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function getClientOrders(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $status = $request->query->get('status')?$request->query->get('status'):$request->request->get('status');
        $restaurant = $request->query->get('restaurant')?$request->query->get('restaurant'):$request->request->get('restaurant');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $client = $em->getRepository(User::class)->find($id);
        if(!$client){
            $result = array('code' => 400, 'description' => "Unexisting client");
            return new JsonResponse($result, 400);
        }
        
        $array = [];
        $orders = $em->getRepository(User::class)->getOrders($id, null, $status, $restaurant, $limit, $page, false);
        foreach ($orders as $k=>$l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['amount'] = $l->getAmount();
            $array[$k]['reference'] = $l->getRef();
            $array[$k]['date'] = $l->getDateCreated()->format('d-m-Y');
            $array[$k]['hour'] = $l->getDateCreated()->format('H:i');
            $array[$k]['delivery_type'] = $l->getDeliveryType();
            $array[$k]['status']['id'] = $l->getOrderStatus()->getId();
            $array[$k]['status']['name'] = $l->getOrderStatus()->getName();
            $array[$k]['restaurant']['id'] = $l->getRestaurant()->getId();
            $array[$k]['restaurant']['name'] = $l->getRestaurant()->getName();
            
            $note = $em->getRepository(ShippingNote::class)->findOneBy(array('command' => $l));
            if($note){
                $array[$k]['noted'] = 1;
            }else{
                $array[$k]['noted'] = 0;
            }
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(User::class)->getOrders($id, null, $status, $limit, $page, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }else{
            $result['items'] = [];
        }
        return new JsonResponse($result);
    }
    
    /**
     * @Post("/api/clients/{id}/bank-card")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Add new bank card."
     * )
     * 
     * @QueryParam(
     *      name="name",
     *      description="Name card owner",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="card_number",
     *      description="card number",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="montth",
     *      description="Expiration's month",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="year",
     *      description="Expiration's year",
     *      strict=false
     * )
     * 
     * 
     *  @QueryParam(
     *      name="security",
     *      description="security code card",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function postClientBankCard(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $token = $request->query->get('token_stripe')?$request->query->get('token_stripe'):$request->request->get('token_stripe');
        
        $cl = $em->getRepository(User::class)->find($id);
        if(!$cl){
            $result = array('code'=>4000, 'description' => 'unexisting client.');
            return new JsonResponse($result, 400);
        }
        
        $stripePublicKey = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_SECRET')->getValue();
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($stripePublicKey);
        
        if(strlen($cl->getStripeId()) > 0){
            $customer = \Stripe\Customer::retrieve($cl->getStripeId());
            $carte = $customer->sources->create(["source" => $token]);
            // Save new card
        }else{
        // Create a Customer:
            $customer = \Stripe\Customer::create([
                'source' => $token,
                'email' => $cl->getEmail(),
                'description' => $cl->getFirstname()
            ]);
            $carte = $customer->sources->data[0];
            
        }
        
        
        if($customer){
            $card = new BankCard();
            $card->setUser($cl);
            $card->setMonthExp($carte->exp_month);
            $card->setYearExp($carte->exp_year);
            $card->setCardNumber($carte->last4);
            $card->setStripeId($carte->id);
            $card->setOwnerName($cl->getFirstname());
            $card->setDeleteStatus(0);
            
            $cl->setStripeId($customer->id);

            $em->persist($cl);
            $em->persist($card);

            $em->flush();

            $result['code'] = 201;
            $result['card_id'] = $card->getid();
            $result['card_number'] = $card->getCardNumber();

            return new JsonResponse($result, 201);

        }else {
            $result['description'] = "Verfy or generate another token";
            return new JsonResponse($result, 400);
        }
    }
    
    
    /**
     * @Get("/api/clients/{id}/bank-card")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="List bank card of a client."
     * )
     * 
     * @QueryParam(
     *      name="limit",
     *      description="limit per page",
     *      strict=false,
     *      default=100
     * )
     * 
     * @QueryParam(
     *      name="page",
     *      description="Page of set",
     *      strict=false,
     *      default=1
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function getClientCards(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $client = $em->getRepository(User::class)->find($id);
        if(!$client){
            $result = array('code' => 400, 'description' => "Unexisting client");
            return new JsonResponse($result, 400);
        }
        
        $array = [];
        $banks = $client->getBankCards();
        foreach ($banks as $k=>$l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['owner_name'] = $l->getOwnerName();
            $array[$k]['card_number'] = $l->getCardNumber();
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = count($array);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }
        return new JsonResponse($result);
    }
    
    /**
     * @Get("/api/clients/{id}/tickets")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="List restaurant's tickets of a client."
     * )
     * 
     * @QueryParam(
     *      name="limit",
     *      description="limit per page",
     *      strict=false,
     *      default=100
     * )
     * 
     * @QueryParam(
     *      name="page",
     *      description="Page of set",
     *      strict=false,
     *      default=1
     * )
     * 
     * @QueryParam(
     *      name="restaurant",
     *      description="Restaurant ID's",
     *      strict=false
     * )
     * 
     * 
     * @QueryParam(
     *      name="active",
     *      description="Boolean value for valid ticket",
     *      strict=false
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function getClientTickets(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $restaurant = $request->query->get('restaurant')?$request->query->get('restaurant'):$request->request->get('restaurant');
        $active = $request->query->get('active')?$request->query->get('active'):$request->request->get('active');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $client = $em->getRepository(User::class)->find($id);
        if(!$client){
            $result = array('code' => 400, 'description' => "Unexisting client");
            return new JsonResponse($result, 400);
        }
        
        $array = [];
        $tickets = $em->getRepository(Ticket::class)->getTickets($id, $restaurant, $active);
        foreach ($tickets as $k=>$l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['code'] = $l->getCode();
            $array[$k]['amount'] = $l->getValue();
            $array[$k]['restaurant']['id'] = $l->getRestaurant()->getId();
            $array[$k]['restaurant']['name'] = $l->getRestaurant()->getName();
        }
        $result['code'] = 200;
            $result['items'] = $array;
            $result['total'] = count($array);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        return new JsonResponse($result);
    }


    /**
     * @Post("/api/clients/{id}/account-activation")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="activation account."
     * )
     * 
     * @QueryParam(
     *      name="code",
     *      description="code for verification",
     *      strict=false,
     *      default=100
     * )
     
     * 
     * @SWG\Tag(name="Clients")
     */
    public function postCodeVerif(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $code = $request->query->get('code')?$request->query->get('code'):$request->request->get('code');
        
        //die(var_dump($code));
        if($code){
            if(!is_string($code)){
                $result = array('code'=> 4000, 'description'=> 'Code must be string');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code'=>4000, 'description'=> 'code is required');
            return new JsonResponse($result, 400);
        }
        
        $client = $em->getRepository(User::class)->find($id);
        if(!$client){
            $result = array('code'=>4000, 'description'=> 'Unexisting client.');
            return new JsonResponse($result, 400);
        }
        
        if($code != $client->getCode()){
            $result = array('code'=>4000, 'description'=> 'Code incorrect.');
            return new JsonResponse($result, 400);
        }
        
        // update state
        $client->setCode('');
        $client->setState(1);
        $em->flush();
        
        $array['code'] = 200;
        $array['data']['id'] = $client->getId();
        $array['data']['username'] = $client->getUsername();
        $array['data']['firstname'] = $client->getFirstname();
        $array['data']['lastname'] = $client->getLastname();
        $array['data']['email'] = $client->getEmail();
        $array['data']['phone'] = $client->getPhoneNumber();
        $array['data']['address'] = $client->getAddress();
        $array['data']['longitude'] = $client->getLongitude();
        $array['data']['latitude'] = $client->getLatitude();
        
        return new JsonResponse($array);
        
    }
    
    
    /**
     * @Put("/api/clients/{id}")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Update client infos."
     * )
     * 
     * @QueryParam(
     *      name="username",
     *      description="username of client",
     *      strict=true
     * )
     * @QueryParam(
     *      name="firstname",
     *      description="Firstname of client",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="lastname",
     *      description="Lastname of the client",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="latitude",
     *      description="Position latitude of the client",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="longitude",
     *      description="Position longitude of the client",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="phone_number",
     *      description="Phone number of the client",
     *      strict=false
     * )
     * 
     * 
     * @SWG\Tag(name="Clients")
     */
    public function putUpdateClientAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $username = $request->query->get('username')?$request->query->get('username'):$request->request->get('username');
        $firstname = $request->query->get('firstname')?$request->query->get('firstname'):$request->request->get('firstname');
        $lastname = $request->query->get('lastname')?$request->query->get('lastname'):$request->request->get('lastname');
        
        $phone = $request->query->get('phone_number')?$request->query->get('phone_number'):$request->request->get('phone_number');
        
        $client = $em->getRepository(User::class)->find($id);
        if(!$client){
            $result = array('code'=> 4000, 'description' => 'Unexisting client');
            return new JsonResponse($result, 400);
        }
        
        $client->setUsername($username);
        $client->setFirstname($firstname);
        $client->setLastname($lastname);
        $client->setPhoneNumber($phone);
        $em->flush();
        
        $result['code'] = 200;
        $result['data']['client_id'] = $client->getId();
        $result['data']['firstname'] = $client->getfirstname();
        $result['data']['email'] = $client->getEmail();
        $result['data']['phone_number'] = $client->getPhoneNumber();
        
        return new JsonResponse($result, 200);
    }
    
    
    /**
     * @Put("/api/clients/{id}/update-password")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Update client password."
     * )
     * 
     * @QueryParam(
     *      name="password",
     *      description="new password",
     *      strict=true
     * )
     * 
     * 
     * @SWG\Tag(name="Clients")
     */
    public function updatePassAction(Request $request, UserPasswordEncoderInterface $encoder, $id){
        $em = $this->getDoctrine()->getManager();
        
        $infos = file_get_contents('php://input');
        $data = json_decode($infos, TRUE);
        
        if(array_key_exists('password', $data)){
            if(!is_string($data['password'])){
                $result = array('code'=> 4000, 'description' => 'password must be string.');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code'=> 4000, 'description' => 'password is required.');
            return new JsonResponse($result, 400);
        }
        
        $user = $em->getRepository(User::class)->find($id);
        if(!$user){
            $result = array('code'=> 4000, 'description' => 'Unexisting client.');
            return new JsonResponse($result, 400);
        }
        
        $pass_encoded = $encoder->encodePassword($user, $data['password']);
        
        $user->setPassword($pass_encoded);
        $em->flush();
        
        return new JsonResponse(array('code' => 200));
    }
    
    /**
     * @Post("/api/clients/{id}/deconnexion")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Deconnect a client account"
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function deconnexionAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $account = $em->getRepository(User::class)->find($id);
        $user = $em->getRepository(ConnexionLog::class)->findOneBy(array('user'=>$account), array('id' => 'desc'), 1);
        if($user){
            if($user->getConnectStatus() == 0){
                $result = array('code'=> 4022, 'description'=> "User not connected.");
                return new JsonResponse($result, 400);
            }
            
            $account = $em->getRepository(User::class)->find($id);
            $account->setConnectStatus(0);
            
            $user->setConnectStatus(0);
            $user->setendDatetime(time());
            
        }else{
            $account->setConnectStatus(0);
        }
        
        $em->flush();
        
        return new JsonResponse(array('code'=>200));
    }
    
    
    /**
     * @Delete("/api/clients/{id}/bank-card/{id_card}")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="List bank card of a client."
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function deleteClientCard(Request $request, $id, $id_card, LoggerInterface $logger){
        $em = $this->getDoctrine()->getManager();
        $stripePublicKey = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_SECRET')->getValue();
        
        \Stripe\Stripe::setApiKey($stripePublicKey);
        
        
        
        $client = $em->getRepository(User::class)->find($id);
        if(!$client){
            $result = array('code' => 400, 'description' => "Unexisting client");
            return new JsonResponse($result, 400);
        }
        
        if(strlen($client->getStripeId()) > 0){
            $card_bd = $em->getRepository(BankCard::class)->find($id_card);
            if($card_bd){
                if($card_bd->getUser()->getId() != $id){
                    $result = array('code' => 401, 'description' => "Not granted to delete this card.");
                    return new JsonResponse($result, 401);
                }
                
                try{
                    $customer = \Stripe\Customer::retrieve($client->getStripeId());
                    $resp = $customer->sources->retrieve($card_bd->getStripeId())->delete();
                }catch(Exception $e){
                    $logger->error($e->getMessage());
                }
                
                
                $card_bd->setDeleteStatus(1);
            }else{
                $result = array('code' => 400, 'description' => "Card does not exist.");
                return new JsonResponse($result, 400);
            } 
        }
        $em->flush();
        $result['code'] = 200;
        return new JsonResponse($result);
          
    }
    
    
    /**
     * @Put("/api/clients/{id}/avatar")
     * 
     *  @QueryParam(
     *      name="avatar",
     *      description="File",
     *      strict=true
     * )
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Update client's avatar."
     * )
     * 
     * @SWG\Tag(name="Clients")
     */
    public function setAvatarAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $client = $em->getRepository(User::class)->find($id);
        if(!$client){
            $result = array('code' => 400, 'description' => "Unexisting client");
            return new JsonResponse($result, 400);
        }
        
        if($request->getMethod("PUT")){
            $photo = $request->files->get('avatar');
            // Manage file
            if (!is_null($photo)) {
                $fileName = $this->generateUniqueFileName() . '.' . $photo->guessExtension();
                $public_path = $request->server->get('DOCUMENT_ROOT');
                $dest_dir = $public_path . "/images/avatars/{$id}/"; //die(var_dump($dest_dir));

                if (file_exists($dest_dir) === FALSE) {
                    mkdir($dest_dir, 0777, true);
                }

                $photo->move($dest_dir, $fileName);

                $client->setAvatar($fileName);
            }
        }
        
        $em->flush();
        
        $result['code'] = 200;
        $result['data']['avatar'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL)."images/avartars/{$id}/".$client->getAvatar();
        return new JsonResponse($result);
    }
}
