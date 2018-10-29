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
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\OrderDetailsMenuProduct;
use App\Entity\OrderShipping;
use App\Entity\OrderStatus;
use App\Entity\Product;
use App\Entity\MenuOption;
use App\Entity\ShippingStatus;
use App\Entity\MenuOptionProducts;

/**
 * Description of OrdersController
 *
 * @author user
 */
class OrdersController extends Controller{
    
    /**
     * @Get("/api/orders")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get orders list"
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
     *      description="order status",
     *      strict=false,
     *      default=null
     * )
     * 
     * @SWG\Tag(name="Orders")
     */
    public function getOrdersListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $status = $request->query->get('status')?$request->query->get('status'):$request->request->get('status');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $listorders = $em->getRepository(Order::class)->getOrders(intval($limit), intval($page), $status, false);
        $array = [];
        foreach ($listorders as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['amount'] = $l->getAmount();
            $array[$k]['reference'] = $l->getRef();
            $array[$k]['date'] = $l->getDateCreated()->format('d-m-Y');
            $array[$k]['hour'] = $l->getDateCreated()->format('H:i');
            $array[$k]["client"]["id"] = $l->getClient()->getId();
            $array[$k]["client"]["username"] = $l->getClient()->getUsername();
            $array[$k]['status']['id'] = $l->getOrderStatus()->getId();
            $array[$k]['status']['name'] = $l->getOrderStatus()->getName();
            $array[$k]['restaurant']['id'] = $l->getRestaurant()->getId();
            $array[$k]['restaurant']['name'] = $l->getRestaurant()->getName();
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(Order::class)->getOrders($limit, $page, $status, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }else{
            $result['items'] = [];
        }
        return new JsonResponse($result);
    }
    
    
    /**
     * @Get("/api/orders/{id}")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get order details"
     * )
     *
     * 
     * @SWG\Tag(name="Orders")
     */
    public function getOrderAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository(Order::class)->find(intval($id));
        
        if(!is_null($order)){
            $result['code'] = 200;
            $result['data']['id'] = $order->getId();
            $result['data']['ref'] = $order->getRef();
            $result['data']['amount'] = $order->getAmount();
            $array['data']['date'] = $order->getDateCreated()->format('d-m-Y');
            $array['data']['hour'] = $order->getDateCreated()->format('H:i');
            $result['data']['restaurant']['id'] = $order->getRestaurant()->getId();
            $result['data']['restaurant']['name'] = $order->getRestaurant()->getName();
            $result['data']['status']['id'] = $order->getOrderStatus()->getId();
            $result['data']['status']['name'] = $order->getOrderStatus()->getname();
            $result['data']['client']['id'] = $order->getClient()->getId();
            $result['data']['client']['username'] = $order->getClient()->getUsername();
            $result['data']['client']['firstname'] = $order->getClient()->getFirstname();
            $result['data']['client']['lastname'] = $order->getClient()->getLastname();
            $result['data']['client']['address'] = $order->getClient()->getAddress();
            
            // Get details
            $menus = $order->getOrderDetails();
            foreach ($menus as $key=>$val){
                $result['data']['menus']["$key"]['id'] = $val->getMenu()->getId();
                $result['data']['menus']["$key"]['name'] = $val->getMenuName();
                $result['data']['menus']["$key"]['price'] = floatval($val->getPrice());
                // Get products of menu
                $products = $val->getOrderDetailsMenuProducts();
                foreach ($products as $k=>$v){
                    $result['data']['menus']["$key"]['products']["$k"]['id'] = $v->getProduct()->getId();
                    $result['data']['menus']["$key"]['products']["$k"]['name'] = $v->getProduct()->getName();
                    $result['data']['menus']["$key"]['products']["$k"]['price'] = floatval($v->getPrice());
                }
            }
        }else{
            $result['code'] = 400;
            $result['description'] = "Cette commande n'existe pas.";
        }
        
        
        return new JsonResponse($result, $result['code']);
    }
    
    /**
     * @Post("/api/orders")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new order"
     * )
     * 
     * @QueryParam(
     *      name="client",
     *      description="ID of client who is passing the order",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="restaurant",
     *      description="ID of restaurant where order is passed.",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="amount",
     *      description="Amount of the order",
     *      strict=true
     * )
     * @QueryParam(
     *      name="address",
     *      description="Delivery address",
     *      strict=true
     * )
     * @QueryParam(
     *      name="status",
     *      description="Order status",
     *      strict=true
     * )
     * @SWG\Tag(name="Orders")
     */
    public function postOrderAction(Request $request, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();
        $total = 0.00;
        $order_infos = file_get_contents('php://input');
        $data = json_decode($order_infos, TRUE);
        
        if (!is_array($data)) {
            $result = array("code" => 4000, "description" => "invalide request body");
            return new JsonResponse($result,400);
        }
        
        $validate = $this->validate_post_order($data);
        if(!$validate){
            http_response_code(400);
            die();
        }
        
        $client = array_key_exists('client', $data) ? $data['client'] : null;
        $restaurant = array_key_exists('restaurant', $data) ? $data['restaurant'] : null;
        $amount = array_key_exists('amount', $data) ? $data['amount'] : null;
        $delivery_address = array_key_exists('delivery_address', $data) ? $data['delivery_address'] : null;
        $delivery_phone = array_key_exists('delivery_phone', $data) ? $data['delivery_phone'] : null;
        $menus = array_key_exists('menus', $data) ? $data['menus'] : [];
        
        $restau = $em->getRepository(Restaurant::class)->find($restaurant);
        $cl = $em->getRepository(User::class)->find($client);
        $reference = substr(strtoupper(md5(random_bytes(6))), 0, 6);
        
        
        // End validation
        $order = new Order();
        $order->setClient($cl);
        $order->setRef($reference);
        $order->setRestaurant($restau);
        $order->setAddress($delivery_address);
        $order->setPhoneNumber($delivery_phone);
        $order->setAmount($total);
        $order->setOrderStatus($em->getRepository(OrderStatus::class)->find(1));
        
        $em->persist($order);
        
        
        
        foreach($menus as $m){
            $ordDt = new OrderDetails();
            $menu = $em->getRepository(Menu::class)->find($m['id']);
            $ordDt->setCommand($order);
            $ordDt->setmenu($menu);
            $ordDt->setPrice($menu->getPrice());
            $ordDt->setMenuName($menu->getName());
            $ordDt->setQuantity($m['quantity']);
            $total += $menu->getPrice() * (int)$m['quantity'];
            $em->persist($ordDt);
            
            if(count($m['options'])){
                foreach($m['options'] as $o){
                    $menuOption = $em->getRepository(MenuOption::class)->find($o['option']);
                    
                    foreach($o['products'] as $op){
                        $prd = $em->getRepository(MenuOptionProducts::class)->find($op['id']);
                        
                        $ordDtPrd = new OrderDetailsMenuProduct();
                        $ordDtPrd->setOrderDetails($ordDt);
                        $ordDtPrd->setMenu($menu);
                        $ordDtPrd->setProduct($prd->getProduct());
                        $ordDtPrd->setPrice($prd->getAttribut());
                        $total += $ordDtPrd->getPrice() * (int)$m['quantity'];
                    }
                    
                    $em->persist($ordDtPrd);
                }
            }  
        }
        $order->setAmount($total);
//        $em->persist($order);
        $em->flush();
        
        // Send mail to restaurant
        $message = (new \Swift_Message('Nouvelle commande'))
            ->setFrom('contact@ubereat.com')
            ->setTo($restau->getOwner()->getEmail())
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/new-order-restaurant.html.twig',
                    array('name' => $restau->getOwner()->getFirstname(), 'restau' => $restau->getName(), 'order' => $order)
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
        
        
        
        // Send mail to client
        $message1 = (new \Swift_Message('Nouvelle commande'))
            ->setFrom('contact@ubereat.com')
            ->setTo($cl->getEmail())
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/new-order-client.html.twig',
                    array('name' => $cl->getFirstname(), 'order' => $order)
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

        $mailer->send($message1);
        
        
        $result['code'] = 201;
        $result['data']['order_id'] = $order->getId();
        $result['data']['reference'] = $order->getRef();
        $result['data']['amount'] = $order->getAmount();
        $result['data']['status'] = $order->getOrderStatus()->getname();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    
    
    
    /**
     * @Post("/api/orders/{id}/shipping")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record shipping data for an order."
     * )
     * 
     * @QueryParam(
     *      name="deliver",
     *      description="ID's delivevry man selected.",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="price",
     *      description="Shipping cost",
     *      strict=true
     * )
     *
     *
     * @QueryParam(
     *      name="status",
     *      description="ID Shipping status",
     *      strict=true
     * ) 
     * @SWG\Tag(name="Orders")
     */
    public function postOrderShippingAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $status = $request->query->get('status')?$request->query->get('status'):$request->request->get('status');
        $price = $request->query->get('price')?$request->query->get('price'):$request->request->get('price');
        $deliver = $request->query->get('deliver')?$request->query->get('deliver'):$request->request->get('deliver');
        
        if(!is_null($deliver)){
            if(!is_numeric($deliver)){
                $result = array('code' => 4000, 'description' => "deliver must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "deliver is required.");
            return new JsonResponse($result, 400);
        }
        
        if(!is_null($price)){
            if(!is_numeric($price)){
                $result = array('code' => 4000, 'description' => "price must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "price is required.");
            return new JsonResponse($result, 400);
        }
        
        
        if(!is_null($status)){
            if(!is_numeric($status)){
                $result = array('code' => 4000, 'description' => "status must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "status is required.");
            return new JsonResponse($result, 400);
        }
        
        $user = $em->getRepository(User::class)->find($deliver);
        if(!is_null($user)){
            if(!in_array('ROLE_DELIVER', $user->getRoles())){
                $result = array('code' => 4002, 'description' => "Deliver ID is not a delivery account.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4003, 'description' => "Unexisting delivery account");
            return new JsonResponse($result, 400);
        }
        
        $o = $em->getRepository(Order::class)->find($id);
        if(is_null($o)){
            $result = array('code' => 4005, 'description' => "Unexisting order.");
            return new JsonResponse($result, 400);
        }
        
        $st = $em->getRepository(ShippingStatus::class)->find($status);
        if(is_null($st)){
            $result = array('code' => 4010, 'description' => "Unexisting order status.");
            return new JsonResponse($result, 400);
        }
        
        $order = new OrderShipping();
        $order->setCommand($o);
        $order->setDeliveryUser($user);
        $order->setShippingCost($price);
        $order->setStatus($st);
        
        $em->persist($order);
        $em->flush();
        
        $result['code'] = 201;
        $result['order_shipping_id'] = $order->getId();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    public function validate_post_order($params){
        $em = $this->getDoctrine()->getManager();
        
        if(!is_array($params)){
            $result = array('code'=>4000, 'description' => 'invalid input');
            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
            return false;
        }
        
        if(array_key_exists('client', $params)){
            if(!is_int($params['client'])){
                $result = array('code'=>4000, 'description' => 'client must be integer.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }
            $client = $em->getRepository(User::class)->find($params['client']);
            if(!$client){
                $result = array('code'=>4000, 'description' => 'Unexisting client.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }elseif(!in_array("ROLE_CLIENT", $client->getRoles())){
                $result = array('code'=>4000, 'description' => 'it is not a client account.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }
        }else{
            $result = array('code'=>4000, 'description' => 'client is required.');
            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
            return false;
        }
        
        
        if(array_key_exists('restaurant', $params)){
            if(!is_int($params['restaurant'])){
                $result = array('code'=>4000, 'description' => 'restaurant must be integer.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }
            $restaurant = $em->getRepository(Restaurant::class)->find($params['restaurant']);
            if(!$restaurant){
                $result = array('code'=>4000, 'description' => 'Unexisting restaurant.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }
        }else{
            $result = array('code'=>4000, 'description' => 'restaurant is required.');
            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
            return false;
        }
        
        
        if(array_key_exists('delivery_address', $params)){
            if(!is_string($params['delivery_address'])){
                $result = array('code'=>4000, 'description' => 'delivery_address must be string.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }
            
        }else{
            $result = array('code'=>4000, 'description' => 'delivery_address is required.');
            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
            return false;
        }
        
        if(array_key_exists('delivery_phone', $params)){
            if(!is_numeric($params['delivery_phone'])){
                $result = array('code'=>4000, 'description' => 'delivery_phone must be numeric.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }
            
        }else{
            $result = array('code'=>4000, 'description' => 'delivery_phone is required.');
            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
            return false;
        }
        
//        if(array_key_exists('amount', $params)){
//            if(!is_numeric($params['amount'])){
//                $result = array('code'=>4000, 'description' => 'amount must be string.');
//                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
//                return false;
//            }
//            
//        }else{
//            $result = array('code'=>4000, 'description' => 'amount is required.');
//            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
//            return false;
//        }
        
        if(array_key_exists('menus', $params)){
            if(!is_array($params['menus'])){
                $result = array('code'=>4000, 'description' => 'menus must be array data.');
                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                return false;
            }
            
            
            foreach ($params['menus'] as $m){
                if(array_key_exists('id', $m)){
                    if(!is_int($m['id'])){
                        $result = array('code'=>4000, 'description' => 'missing id menu');
                        echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                        return false;
                    }
                    
                    $menu = $em->getRepository(Menu::class)->find($m['id']);
                    if(!$menu){
                        $result = array('code'=>4000, 'description' => 'one unexisting menu id #'.$m['id']);
                        echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                        return false;
                    }
                    
                }else{
                    $result = array('code'=>4000, 'description' => 'one id menu is required.');
                    echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                    return false;
                }
                
                
                if(array_key_exists('quantity', $m)){
                    if(!is_int($m['quantity'])){
                        $result = array('code'=>4000, 'description' => 'missing quantity menu');
                        echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                        return false;
                    }
                    
                }else{
                    $result = array('code'=>4000, 'description' => 'one quantity menu is required.');
                    echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                    return false;
                }
                
//                if(array_key_exists('price', $m)){
//                    if(!is_numeric($m['price'])){
//                        $result = array('code'=>4000, 'description' => 'price\'s menu must be numeric');
//                        echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
//                        return false;
//                    }
//                    
//                }else{
//                    $result = array('code'=>4000, 'description' => 'price\'s menu is required.');
//                    echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
//                    return false;
//                }
                
                if(array_key_exists('options', $m)){
                    if(!is_array($m['options'])){
                        $result = array('code'=>4000, 'description' => 'products must be array data.');
                        echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
                        return false;
                    }
                    
//                    foreach($m['products'] as $p){
//                        if(array_key_exists('id', $p)){
//                            if(!is_int($p['id'])){
//                                $result = array('code'=>4000, 'description' => 'missing id product in one menu array.');
//                                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
//                                return false;
//                            }
//
//                            $product = $em->getRepository(Product::class)->find($p['id']);
//                            if(!$product){
//                                $result = array('code'=>4000, 'description' => 'one unexisting product id #'.$p['id']);
//                                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
//                                return false;
//                            }
//
//                        }else{
//                            $result = array('code'=>4000, 'description' => 'one id product is required.');
//                            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
//                            return false;
//                        }
//                        
//                        
////                        if(array_key_exists('price', $p)){
////                            if(!is_numeric($p['price'])){
////                                $result = array('code'=>4000, 'description' => 'price\'s product must be numeric');
////                                echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
////                                return false;
////                            }
////
////                        }else{
////                            $result = array('code'=>4000, 'description' => 'price\'s product is required.');
////                            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
////                            return false;
////                        }
//                    }
                }
            }
        } else {
            $result = array('code'=>4000, 'description' => 'array \'menus\' is required.');
            echo json_encode($result, JSON_UNESCAPED_SLASHES, 400);
            return false;
        }
        
        
        
        return true;
    }
    
    
    
    /**
     * @Put("/api/orders/{id}/tracking")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Update position of delivering order"
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
     * @SWG\Tag(name="Orders")
     */
    public function putOrderShippingAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        
        $order = $em->getRepository(Order::class)->find($id);
        if(!$order){
            $result = array('code' => 4000, 'description' => 'Unexisting order.');
            return new JsonResponse($result, 400);
        }
        
        $order->setTrackingLat($latitude);
        $order->setTrackingLng($latitude);
        
        $em->flush();
        
        $result = array(
            'code'=> 200,
            'data' => array(
                'order_id' => $order->getId(),
                'reference' => $order->getRef()
            )
        );
        
        return new JsonResponse($result);
    }
}
