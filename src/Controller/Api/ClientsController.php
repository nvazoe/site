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
use App\Entity\BankCard;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        
        $code = substr(strtoupper(md5(random_bytes(10))), 0, 4);
            
        $del = new User();
        $del->setUsername($username);
        $del->setFirstname($firstname);
        $del->setLongitude($longitude);
        $del->setLatitude($latitude);
        $del->setLastname($lastname);
        $del->setPhoneNumber($phone);
        $del->setCode($code);
        $del->setEmail($email);
        $del->setState(0);
        $del->setPassword($encoder->encodePassword($del, $password));
        $del->setRoles(["ROLE_CLIENT"]);
        $em->persist($del);
        $em->flush();
        
        
        /** Sending code by email **/
        $message = (new \Swift_Message('Validation de compte'))
            ->setFrom('contact@ubereat.com')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/registration.html.twig',
                    array('name' => $firstname, 'code' => $code)
                ),
                'text/html'
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
     * @SWG\Tag(name="Clients")
     */
    public function getClientOrders(Request $request, $id){
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
        $orders = $client->getOrders();
        foreach ($orders as $k=>$l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['amount'] = $l->getAmount();
            $array[$k]['reference'] = $l->getRef();
            $array[$k]['date'] = $l->getDateCreated()->format('d-m-Y');
            $array[$k]['hour'] = $l->getDateCreated()->format('H:i');
            $array[$k]['status']['id'] = $l->getOrderStatus()->getId();
            $array[$k]['status']['name'] = $l->getOrderStatus()->getName();
            $array[$k]['restaurant']['id'] = $l->getRestaurant()->getId();
            $array[$k]['restaurant']['name'] = $l->getRestaurant()->getName();
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
        //$client = $request->query->get('client')?$request->query->get('client'):$request->request->get('client');
        $name = $request->query->get('name')?$request->query->get('name'):$request->request->get('name');
        $security = $request->query->get('security')?$request->query->get('security'):$request->request->get('security');
        $month = $request->query->get('month')?$request->query->get('month'):$request->request->get('month');
        $year = $request->query->get('year')?$request->query->get('year'):$request->request->get('year');
        $card = $request->query->get('card_number')?$request->query->get('card_number'):$request->request->get('card_number');
        
        
        // validate card  number
        if($card){
            if(!is_string($card)){
                $result = array('code'=>4000, 'description' => 'Card number must be string');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code'=>4000, 'description' => 'Card number is required.');
            return new JsonResponse($result, 400);
        }
        
        if($name){
            if(!is_string($name)){
                $result = array('code'=>4000, 'description' => 'Name must be string');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code'=>4000, 'description' => 'name is required.');
            return new JsonResponse($result, 400);
        }
        
        if($year){
            if(!is_string($year)){
                $result = array('code'=>4000, 'description' => 'year must be string');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code'=>4000, 'description' => 'year is required.');
            return new JsonResponse($result, 400);
        }
        
        if($month){
            if(!is_string($month)){
                $result = array('code'=>4000, 'description' => 'month must be string');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code'=>4000, 'description' => 'month is required.');
            return new JsonResponse($result, 400);
        }
        
        
        if($security){
            if(!is_string($security)){
                $result = array('code'=>4000, 'description' => 'security must be string');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code'=>4000, 'description' => 'security is required.');
            return new JsonResponse($result, 400);
        }
        
        $cl = $em->getRepository(User::class)->find($id);
        if(!$cl){
            $result = array('code'=>4000, 'description' => 'unexisting client.');
            return new JsonResponse($result, 400);
        }
        
        $bc = new BankCard();
        $bc->setOwnerName($name);
        $bc->setUser($cl);
        $bc->setMonthExp($month);
        $bc->setYearExp($year);
        $bc->setSecurityCode($security);
        $bc->setCardNumber($card);
        
        $em->persist($bc);
        $em->flush();
        
        $result['code'] = 201;
        $result['bank_card_id'] = $bc->getId();
        
        return new JsonResponse($result, 201);
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
        $client->setState(1);
        $em->flush();
        
        $array['code'] = 200;
        $array['data']['id'] = $client->getId();
        $array['data']['username'] = $client->getUsername();
        $array['data']['firstname'] = $client->getFirstname();
        $array['data']['lastname'] = $client->getLastname();
        $array['data']['email'] = $client->getEmail();
        $array['data']['phone_number'] = $client->getPhoneNumber();
        $array['data']['address'] = $client->getAddress();
        $array['data']['longitude'] = $client->setLongitude();
        $array['data']['latitude'] = $client->setLatitude();
        
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
}
