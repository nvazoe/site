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
use App\Entity\DeliveryDisponibility;
use App\Entity\Menu;
use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Entity\DeliveryProposition;
use App\Entity\ConnexionLog;
use App\Entity\ShippingLog;
use App\Entity\ShippingNote;
use App\Entity\Configuration;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of DeliversController
 *
 * @author user
 */
class DeliversController extends Controller{
    /**
     * @Get("/api/delivers")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get delivers list"
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
     * @SWG\Tag(name="Delivers")
     */
    public function getDeliverListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $users = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        $array = [];
        
        foreach ($users as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["firstname"] = $l->getFirstname();
            $array[$k]["lastname"] = $l->getLastname();
            $array[$k]["username"] = $l->getUsername();
            $array[$k]["email"] = $l->getEmail();
            
            $dispo = $l->getDeliveryDisponibilities();
            foreach($dispo as $m => $n){
                $array[$k]["disponibilities"][$m]['day'] = $n->getDay();
                $array[$k]["disponibilities"][$m]['startHour'] = $n->getStartHour();
                $array[$k]["disponibilities"][$m]['endHour'] = $n->getEndHour();
            }
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = count($array);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }else{
            $result['items'] = [];
        }
           
        
        return new JsonResponse($result, $result['code'] = 200);
    }
    
    
    /**
     * @Post("/api/delivers")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new delivery man."
     * )
     * 
     * @QueryParam(
     *      name="username",
     *      description="username of delivery man",
     *      strict=true
     * )
     * @QueryParam(
     *      name="firstname",
     *      description="Firstname of delivery man",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="lastname",
     *      description="Lastname of the delivery man",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="latitude",
     *      description="Position latitude of the delivery",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="longitude",
     *      description="Position longitude of the delivery",
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
     *      description="password of delivery man",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function postDeliveryAction(Request $request, UserPasswordEncoderInterface $encoder){
        $em = $this->getDoctrine()->getManager();
        $username = $request->query->get('username')?$request->query->get('username'):$request->request->get('username');
        $firstname = $request->query->get('firstname')?$request->query->get('firstname'):$request->request->get('firstname');
        $lastname = $request->query->get('lastname')?$request->query->get('lastname'):$request->request->get('lastname');
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
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
            
        $del = new User();
        $del->setUsername($username);
        $del->setFirstname($firstname);
        $del->setLongitude($longitude);
        $del->setLatitude($latitude);
        $del->setLastname($lastname);
        $del->setEmail($email);
        $del->setPassword($encoder->encodePassword($del, $password));
        $del->setRoles(["ROLE_DELIVER"]);
        $del->setConnectStatus(0);
        $em->persist($del);
        $em->flush();
        
        $result['code'] = 201;
        $result['delivery_id'] = $del->getId();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Post("/api/delivers/{id}/disponibilities")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new delivery disponibilities."
     * )
     
     * 
     * @QueryParam(
     *      name="dayz",
     *      description="Set day of the week",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="hourStart",
     *      description="Set starting hour.",
     *      strict=true,
     *      default=00
     * )
     *
     *  @QueryParam(
     *      name="hourEnd",
     *      description="Position latitude of the delivery",
     *      strict=true,
     *      default=23
     * )
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function postDeliveryDispoAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $dayz = $request->query->get('dayz')?$request->query->get('dayz'):$request->request->get('dayz');
        $start = $request->query->get('hourStart')?$request->query->get('hourStart'):$request->request->get('hourStart');
        $end = $request->query->get('hourEnd')?$request->query->get('hourEnd'):$request->request->get('hourEnd');
        
        // Default values
        $start = ($start == null) ? 00 : $start;
        $end = ($end == null) ? 23 : $end;
        
        /** Validate dayz input **/
        if(!is_null($dayz)){
            if (!is_string($dayz)) {
                $result = array('code' => 4000, 'description' => "dayz must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "dayz is required. Ex: Monday, Tuesday ..."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate hourStart **/
        
        
        $user = $em->getRepository(User::class)->find($id);
        if(!in_array("ROLE_DELIVER", $user->getRoles())){
            $result = array('code' => 4013, 'description' => 'Ce livreur n\'existe pas.');
            return new JsonResponse($result, 400);
        }
        
        if(intval($start)> intval($end)){
            $result = array('code' => 4014, 'description' => "L'heure de début doit être inférieur à l'heure de fin.");
            return new JsonResponse($result, 400);
        }
        
            
        $del = new DeliveryDisponibility();
        $del->setDeliveryMan($user);
        $del->setEndHour($end);
        $del->setStartHour($start);
        $del->setDay($dayz);
        $em->persist($del);
        $em->flush();
        
        $result['code'] = 201;
        $result['disponiblity_user_id'] = $user->getId();
        $result['day'] = $del->getDay();
        $result['from'] = $del->getStartHour();
        $result['to'] = $del->getEndHour();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Get("/api/delivers/{id}/orders")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get deliver's orders shipped list"
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
     * 
     * @QueryParam(
     *      name="status",
     *      description="Order status ID",
     *      strict=false
     * )
     * 
     * @QueryParam(
     *      name="restaurant",
     *      description="restaurant ID",
     *      strict=false
     * )
     * 
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function getOrdersDeliveredAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $status = $request->query->get('status')?$request->query->get('status'):$request->request->get('status');
        $restaurant = $request->query->get('restaurant')?$request->query->get('restaurant'):$request->request->get('restaurant');
        
        
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $deliver = $em->getRepository(User::class)->find($id);
        if(!$deliver){
            $result = array('code' => 400, 'description' => "Unexisting client");
            return new JsonResponse($result, 400);
        }
        
        
        
        $array = [];
        $orders = $em->getRepository(User::class)->getOrders(null, $id, $status, $restaurant, $limit, $page, false);
        foreach ($orders as $k=>$l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['amount'] = $l->getAmount();
            $array[$k]['reference'] = $l->getRef();
            $array[$k]['date'] = $l->getDateCreated()->format('d-m-Y');
            $array[$k]['hour'] = $l->getDateCreated()->format('H:i');
            $array[$k]['delivery_address'] = $l->getAddress();
            $array[$k]['delivery_city'] = $l->getCity();
            $array[$k]['delivery_phone'] = $l->getPhoneNumber();
            $array[$k]['delivery_local'] = $l->getDeliveryLocal();
            $array[$k]['delivery_note'] = $l->getDeliveryNote();
            $array[$k]['delivery_hour'] = $l->getDeliveryHour();
            $array[$k]['delivery_date'] = $l->getDeliveryDate();
            $array[$k]['delivery_type'] = $l->getDeliveryType();
            $array[$k]['status']['id'] = $l->getOrderStatus()->getId();
            $array[$k]['status']['name'] = $l->getOrderStatus()->getName();
            $array[$k]["client"]["id"] = $l->getClient()->getId();
            $array[$k]["client"]["username"] = $l->getClient()->getUsername();
            $array[$k]["client"]["position"]["latitude"] = $l->getClient()->getLatitude();
            $array[$k]["client"]["position"]["longitude"] = $l->getClient()->getLongitude();
            $array[$k]['status']['id'] = $l->getOrderStatus()->getId();
            $array[$k]['status']['name'] = $l->getOrderStatus()->getName();
            $array[$k]['restaurant']['id'] = $l->getRestaurant()->getId();
            $array[$k]['restaurant']['name'] = $l->getRestaurant()->getName();
            $array[$k]['restaurant']['address'] = $l->getRestaurant()->getAddress();
            $array[$k]['restaurant']['city'] = $l->getRestaurant()->getCity();
            $array[$k]['restaurant']['postal_code'] = $l->getRestaurant()->getCp();
            $array[$k]['restaurant']['note'] = $l->getRestauNote();
            if($l->getMessenger()){
                $array[$k]['deliver']['id'] = $l->getMessenger()->getId();
                $array[$k]['deliver']['username'] = $l->getMessenger()->getusername();
                $array[$k]['deliver']['note'] = $l->getDeliverNote();
            }
            
            
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(Order::class)->getOrders(null, $id, 4, $limit, $page, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }else{
            $result['items'] = [];
        }
        return new JsonResponse($result);
    }
    
    
    /**
     * @Put("/api/delivers/{deliver}/orders/{order}/approved")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Approved an order"
     * )
     * 
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function approvedDeliveringAction(Request $request, $deliver, $order){
        $em = $this->getDoctrine()->getManager();
        
        $del = $em->getRepository(User::class)->find($deliver);
        if(!$del){
            $result = array('code' => 400, 'description' => "Unexisting deliver");
            return new JsonResponse($result, 400);
        }elseif(!in_array("ROLE_DELIVER", $del->getRoles())){
            $result = array('code' => 400, 'description' => "Not a deliver account.");
            return new JsonResponse($result, 400);
        }
        
        $ord = $em->getRepository(Order::class)->find($order);
        if(!$ord){
            $result = array('code' => 400, 'description' => "Unexisting order");
            return new JsonResponse($result, 400);
        }elseif($ord->getMessenger()){
            $result = array('code' => 400, 'description' => "A deliver already assigned.");
            
            $propos = $em->getRepository(DeliveryProposition::class)->findBy(array("command" => $order));
            foreach($propos as $val)
                $em->remove ($val);
            
            return new JsonResponse($result, 400);
        }
        
        $orderRow = $em->getRepository(DeliveryProposition::class)->findOneBy(array(
            'command' => $ord
        ));
        if(!is_null($orderRow)){
            $ord->setMessenger($del);
            //$ord->setOrderStatus($em->getRepository(OrderStatus::class)->find(6));
            
        }
        
        $propos = $em->getRepository(DeliveryProposition::class)->findBy(array("command" => $order));
        foreach($propos as $val)
            $em->remove ($val);
        
        $em->flush();
        
        return new JsonResponse(array('code'=>200));
    }
    
    
    /**
     * @Put("/api/delivers/{deliver}/orders/{order}/shipped")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Mark as shipped for an order"
     * )
     * 
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function shippedOrderAction(Request $request, $deliver, $order){
        $em = $this->getDoctrine()->getManager();
        
        $del = $em->getRepository(User::class)->find($deliver);
        if(!$del){
            $result = array('code' => 400, 'description' => "Unexisting deliver");
            return new JsonResponse($result, 400);
        }elseif(!in_array("ROLE_DELIVER", $del->getRoles())){
            $result = array('code' => 400, 'description' => "Not a deliver account.");
            return new JsonResponse($result, 400);
        }
        
        $ord = $em->getRepository(Order::class)->find($order);
        if(!$ord){
            $result = array('code' => 400, 'description' => "Unexisting order");
            return new JsonResponse($result, 400);
        }elseif(!$ord->getMessenger()){
            $result = array('code' => 400, 'description' => "No deliver was assigned.");
            return new JsonResponse($result, 400);
        }elseif($ord->getMessenger()->getId() != intval($deliver)){
            $result = array('code' => 400, 'description' => "You are not allowed.");
            return new JsonResponse($result, 400);
        } elseif($em->getRepository(ShippingLog::class)->findOneBy(array('command'=>$order))) {
            $result = array('code' => 400, 'description' => "Order already shipped.");
            return new JsonResponse($result, 400);
        }
        
        $shipLog = new ShippingLog();
        $shipLog->setMessenger($del);
        $shipLog->setCommand($ord);
        $shipLog->setMakeAt(time());
        
        $em->persist($shipLog);
        
        try{
            if($ord->getPaymentMode()){
                
                if($ord->getPaymentMode()->getId() == 1){
                    $stripePublicKey = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_SECRET')->getValue();
                    // Set your secret key: remember to change this to your live secret key in production
                    // See your keys here: https://dashboard.stripe.com/account/apikeys
                    \Stripe\Stripe::setApiKey($stripePublicKey);
                    
                    // UPDATE CUSTOMER DEFAULT SOURCE
                    $cu = \Stripe\Customer::retrieve($ord->getClient()->getStripeId());
                    $cu->default_source = $ord->getPayment()->getStripeId();
                    $cu->save();
                    

                    $charge = \Stripe\Charge::create([
                        'amount' => $ord->getAmount()*100, // $15.00 this time
                        'currency' => 'eur',
                        'customer' => $ord->getClient()->getStripeId(), // Previously stored, then retrieved
                        'metadata' => ['order_id' => $ord->getId()]
                    ]);
                    //echo '<pre>'; die(var_dump($charge)); echo '</pre>';
                    if($charge){
                        //Update status order
                        $ord->setOrderStatus($em->getRepository(OrderStatus::class)->find(4));
                        $ord->setBalanceTransaction($charge->id);
                    }
                }else{
                    //Update status order
                    $ord->setOrderStatus($em->getRepository(OrderStatus::class)->find(4));
                }
                
            }
        }catch(\Exception $e){
            echo $e->getMessage();
        }
        
        
        
        $em->flush();
        
        return new JsonResponse(array('code'=>200));
    }
    
    
    /**
     * @Put("/api/delivers/{deliver}/orders/{order}/declined")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Declined an shipping order proposition"
     * )
     * 
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function declinedDeliveringAction(Request $request, $deliver, $order){
        $em = $this->getDoctrine()->getManager();
        
        $del = $em->getRepository(User::class)->find($deliver);
        if(!$del){
            $result = array('code' => 400, 'description' => "Unexisting deliver");
            return new JsonResponse($result, 400);
        }elseif(!in_array("ROLE_DELIVER", $del->getRoles())){
            $result = array('code' => 400, 'description' => "Not a deliver account.");
            return new JsonResponse($result, 400);
        }
        
        $ord = $em->getRepository(Order::class)->find($order);
        if(!$ord){
            $result = array('code' => 400, 'description' => "Unexisting order");
            return new JsonResponse($result, 400);
        }elseif($ord->getMessenger()){
            $result = array('code' => 400, 'description' => "A Deliver already manage the order.");
            return new JsonResponse($result, 400);
        }
        
        $orderRow = $em->getRepository(DeliveryProposition::class)->getOrderRow($deliver, $order);
        if($orderRow){
            $orderRow->setValueDeliver(2);
            
            $em->flush();
        }
        
        return new JsonResponse(array('code'=>200));
    }
    
    /**
     * @Get("/api/delivers/{id}")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get Deliver informations"
     * )
     *
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function getDeliverAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $deliver = $em->getRepository(User::class)->find(intval($id));
        $infosShip = $em->getRepository(ShippingNote::class)->getShippingByDeliver($deliver);
        $avg = 0;
        if(count($infosShip)){
            $som = 0;
            $total = count($infosShip);
            foreach ($infosShip as $ind){
                $som += (int)$ind->getDeliverNote();
            }
            $avg = ceil(($som/$total)/2);
        }else{
            $total = 0;
        }
        
        if(!is_null($deliver)){
            if(!in_array('ROLE_DELIVER', $deliver->getRoles())){
                $result = array('code'=>400, 'description'=>"Unexisting deliver account");
            }else{
                $result['code'] = 200;
                $result['data']['id'] = $deliver->getId();
                $result['data']['username'] = $deliver->getUsername();
                $result['data']['firstname'] = $deliver->getFirstname();
                $result['data']['lastname'] = $deliver->getLastname();
                $result['data']['email'] = $deliver->getEmail();
                $result['data']['phone'] = $deliver->getPhoneNumber();
                $result['data']['address'] = $deliver->getAddress();
                $result['data']['position']['latitude'] = $deliver->getLatitude();
                $result['data']['position']['longitude'] = $deliver->getLongitude();
                $result['data']['note']['stars'] = $avg;
                $result['data']['note']['avis'] = $total;
            }
        }else{
            $result['code'] = 400;
            $result['description'] = "Ce client n'existe pas.";
        }
        
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Put("/api/delivers/{id}/position")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Update position of deliver man"
     * )
     * 
     * @QueryParam(
     *      name="latitude",
     *      description="latitude geo position",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="longitude",
     *      description="longitude geo position",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function putPositionAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        
        $del = $em->getRepository(User::class)->find($id);
        if(!$del){
            $result = array('code' => 4000, 'description' => 'Unexisting deliver account.');
            return new JsonResponse($result, 400);
        }
        
        $del->setLatitude($latitude);
        $del->setLongitude($longitude);
        
        $user = $em->getRepository(ConnexionLog::class)->getLastConnectRow($id);
        if($user){
            if(time() - $user->getstartDatetime() <= 300 ){
                $user->setendDatetime(time());
            }else{
                $role = "S";
                if(in_array('ROLE_DELIVER', $del->getRoles()))
                    $role = "D";
                if(in_array('ROLE_CLIENT', $del->getRoles()))
                    $role = "C";
                if(in_array('ROLE_ADMIN', $del->getRoles()))
                    $role = "A";
                
                $conn = new ConnexionLog();
                $conn->setUser($del);
                $conn->setRole($role);
                $conn->setConnectStatus(1);
                $conn->setDateCreated(new \DateTime());
                $conn->setstartDatetime(time());
                $conn->setendDatetime(time());
                $em->persist($conn);
            }
        }
        
        
        
        $em->flush();
        
        $result = array(
            'code'=> 200,
            'data' => array(
                'deliver_id' => $del->getId(),
                'lat' => $del->getLatitude(),
                'lng' => $del->getLongitude()
            )
        );
        
        return new JsonResponse($result);
    }
    
    /**
     * @Get("/api/delivers/{id}/orders-to-deliver")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get delivers list"
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
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function getProposedDelivringOrders(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $del = $em->getRepository(User::class)->find($id);
        if(!$del){
            $result = array('code' => 4000, 'description' => 'Unexisting deliver account.');
            return new JsonResponse($result, 400);
        }
        
        $array = [];
        $orders = $em->getRepository(DeliveryProposition::class)->getOrders($id, $limit, $page, false);
        foreach ($orders as $k=>$l){
            $array[$k]["id"] = $l->getCommand()->getId();
            $array[$k]['amount'] = $l->getCommand()->getAmount();
            $array[$k]['reference'] = $l->getCommand()->getRef();
            $array[$k]['date'] = $l->getCommand()->getDateCreated()->format('d-m-Y');
            $array[$k]['hour'] = $l->getCommand()->getDateCreated()->format('H:i');
            $array[$k]['delivery_address'] = $l->getCommand()->getAddress();
            $array[$k]['delivery_city'] = $l->getCommand()->getCity();
            $array[$k]['delivery_phone'] = $l->getCommand()->getPhoneNumber();
            $array[$k]['delivery_local'] = $l->getCommand()->getDeliveryLocal();
            $array[$k]['delivery_note'] = $l->getCommand()->getDeliveryNote();
            $array[$k]['delivery_hour'] = $l->getCommand()->getDeliveryHour();
            $array[$k]['delivery_date'] = $l->getCommand()->getDeliveryDate();
            $array[$k]['delivery_type'] = $l->getCommand()->getDeliveryType();
            $array[$k]['status']['id'] = $l->getCommand()->getOrderStatus()->getId();
            $array[$k]['status']['name'] = $l->getCommand()->getOrderStatus()->getName();
            $array[$k]['client']['id'] = $l->getCommand()->getClient()->getId();
            $array[$k]['client']['username'] = $l->getCommand()->getClient()->getUsername();
            $array[$k]['client']['firstname'] = $l->getCommand()->getClient()->getFirstname();
            $array[$k]['client']['lastname'] = $l->getCommand()->getClient()->getLastname();
            $array[$k]['client']['phone'] = $l->getCommand()->getClient()->getPhoneNumber();
            $array[$k]['restaurant']['id'] = $l->getCommand()->getRestaurant()->getId();
            $array[$k]['restaurant']['name'] = $l->getCommand()->getRestaurant()->getName();
            $array[$k]['restaurant']['address'] = $l->getCommand()->getRestaurant()->getAddress();
            $array[$k]['restaurant']['city'] = $l->getCommand()->getRestaurant()->getCity();
            $array[$k]['restaurant']['note'] = $l->getCommand()->getRestauNote();
            $array[$k]['restaurant']['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/restaurant/'.$l->getCommand()->getRestaurant()->getImage();
            if($l->getCommand()->getMessenger()){
                $array[$k]['deliver']['id'] = $l->getCommand()->getMessenger()->getId();
                $array[$k]['deliver']['username'] = $l->getCommand()->getMessenger()->getusername();
                $array[$k]['deliver']['note'] = $l->getCommand()->getDeliverNote();
            }
            
        }
        
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(DeliveryProposition::class)->getOrders($id, $limit, $page, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }else{
            $result['items'] = [];
        }
        return new JsonResponse($result);
    }
    
    
    /**
     * @Post("/api/delivers/{id}/deconnexion")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Deconnect a deliver account"
     * )
     * 
     * @SWG\Tag(name="Delivers")
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
     * @Get("/api/delivers/{id}/ping")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get connect status"
     * )
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function getConnectStatus(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $user = $em->getRepository(User::class)->find($id);
        
        if(!$user){
            $result = array('code' => 400, 'description' => "Unexisting deliver account");
            return new JsonResponse($result, 400);
        }
        
        if($user->getConnectStatus()){
            $result = "CONNECTED";
        }else{
            $result = "NON CONNECTED";
        }
        
        return new JsonResponse(array('code'=>200, 'data' => $result));
    }
    
    
    
    /**
     * @Put("/api/delivers/{id}/avatar")
     * 
     *  @QueryParam(
     *      name="avatar",
     *      description="File",
     *      strict=true
     * )
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Update deliver's avatar."
     * )
     * 
     * @SWG\Tag(name="Delivers")
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
    
    
    
    /**
     * @Put("/api/delivers/{id}/update-password")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Update deliver account password."
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
