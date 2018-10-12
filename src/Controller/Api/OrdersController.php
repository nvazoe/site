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
use App\Entity\ShippingStatus;

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
     * @SWG\Tag(name="Orders")
     */
    public function getOrdersListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $listorders = $em->getRepository(Order::class)->getOrders(intval($limit), intval($page), false);
        $array = [];
        foreach ($listorders as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['amount'] = $l->getAmount();
            $array[$k]['reference'] = $l->getRef();
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
            $result['total'] = $em->getRepository(Order::class)->getOrders($limit, $page, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
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
                $result['data']['menus']["$key"]['price'] = $val->getPrice();
                // Get products of menu
                $products = $val->getOrderDetailsMenuProducts();
                foreach ($products as $k=>$v){
                    $result['data']['menus']["$key"]['products']["$k"]['id'] = $v->getProduct()->getId();
                    $result['data']['menus']["$key"]['products']["$k"]['name'] = $v->getProduct()->getName();
                    $result['data']['menus']["$key"]['products']["$k"]['price'] = $v->getPrice();
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
     *      name="reference",
     *      description="Reference's order",
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
    public function postOrderAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $client = $request->query->get('client')?$request->query->get('client'):$request->request->get('client');
        $reference = $request->query->get('reference')?$request->query->get('reference'):$request->request->get('reference');
        $restaurant = $request->query->get('restaurant')?$request->query->get('restaurant'):$request->request->get('restaurant');
        $amount = $request->query->get('amount')?$request->query->get('amount'):$request->request->get('amount');
        $address = $request->query->get('address')?$request->query->get('address'):$request->request->get('address');
        $status = $request->query->get('status')?$request->query->get('status'):$request->request->get('status');
        
        // Validation params
        if(!is_null($client)){
            if(!is_numeric($client)){
                $result = array('code' => 4000, 'description' => "client must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "client is required.");
            return new JsonResponse($result, 400);
        }
        
        if(!is_null($reference)){
            if(!is_numeric($reference)){
                $result = array('code' => 4000, 'description' => "reference must be string.");
                return new JsonResponse($result, 400);
            }
            
        }else{
            $result = array('code' => 4000, 'description' => "reference is required.");
            return new JsonResponse($result, 400);
        }
        
        if(!is_null($restaurant)){
            if(!is_numeric($restaurant)){
                $result = array('code' => 4000, 'description' => "restaurant must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "restaurant is required");
            return new JsonResponse($result, 400);
        }
        
        if(!is_null($amount)){
            if(!is_numeric($amount)){
                $result = array('code' => 4000, 'description' => "amount must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "amount is required.");
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
        
        
        if(!is_null($address)){
            if(!is_string($address)){
                $result = array('code' => 4000, 'description' => "address must be string.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "address is required.");
            return new JsonResponse($result, 400);
        }
        
        $cl = $em->getRepository(User::class)->find($client);
        if(!is_null($cl)){
            if(!in_array('ROLE_CLIENT', $cl->getRoles())){
                $result = array('code' => 4002, 'description' => "You must be client to pass an order.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4003, 'description' => "Unexisting client.");
            return new JsonResponse($result, 400);
        }
        
        $resto = $em->getRepository(Restaurant::class)->find($restaurant);
        if(is_null($resto)){
            $result = array('code' => 4004, 'description' => "Ce restaurant n'existe pas.");
            return new JsonResponse($result, 400);
        }
        
        $st = $em->getRepository(OrderStatus::class)->find($status);
        if(is_null($st)){
            $result = array('code' => 4004, 'description' => "Unexisting order status");
            return new JsonResponse($result, 400);
        }
        // End validation
        $order = new Order();
        $order->setClient($cl);
        $order->setRef($reference);
        $order->setRestaurant($resto);
        $order->setAmount($amount);
        $order->setAddress($address);
        $order->setOrderStatus($st);
        
        $em->persist($order);
        $em->flush();
        
        $result['code'] = 201;
        $result['order_id'] = $order->getId();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Post("/api/orders/{id}/menus")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record details of an order"
     * )
     * 
     * @QueryParam(
     *      name="menu",
     *      description="ID's menu",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="price",
     *      description="menu's price when passing order",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="name",
     *      description="Menu's name when passing order",
     *      strict=false
     * )
     * 
     
     * 
     * @SWG\Tag(name="Orders")
     */
    public function postOrderDetailAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $menu = $request->query->get('menu')?$request->query->get('menu'):$request->request->get('menu');
        $name = $request->query->get('name')?$request->query->get('name'):$request->request->get('name');
        $price = $request->query->get('price')?$request->query->get('price'):$request->request->get('price');
        
        if(!is_null($menu)){
            if(!is_numeric($menu)){
                $result = array('code' => 4000, 'description' => "menu must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "menu ID is required.");
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
        
        $m = $em->getRepository(Menu::class)->find($menu);
        if(is_null($m)){
            $result = array('code' => 4004, 'description' => "Ce menu n'existe pas.");
            return new JsonResponse($result, 400);
        }
        
        $o = $em->getRepository(Order::class)->find($id);
        if(is_null($o)){
            $result = array('code' => 4005, 'description' => "Cette commande n'existe pas.");
            return new JsonResponse($result, 400);
        }
        
        $order = new OrderDetails();
        $order->setCommand($o);
        $order->setMenuName($name);
        $order->setPrice($price);
        $order->setMenu($m);
        
        $em->persist($order);
        $em->flush();
        
        $result['code'] = 201;
        $result['order_details_id'] = $order->getId();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Post("/api/orders/{id_order_details}/products")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record product's menu of an order"
     * )
     * 
     * @QueryParam(
     *      name="Product",
     *      description="ID's product",
     *      strict=true
     * )
     *
     * @QueryParam(
     *      name="name",
     *      description="Product'name",
     *      strict=false
     * )
     * 
     * @QueryParam(
     *      name="price",
     *      description="product's price when passing order",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Orders")
     */
    public function postOrderDetailPrdAction(Request $request, $id_order_details){
        $em = $this->getDoctrine()->getManager();
        
        $product = $request->query->get('product')?$request->query->get('product'):$request->request->get('product');
        $price = $request->query->get('price')?$request->query->get('price'):$request->request->get('price');
        $name = $request->query->get('name')?$request->query->get('price'):$request->request->get('name');
        /** Validate inputs **/
        if(!is_null($price)){
            if(!is_numeric($price)){
                $result = array('code' => 4000, 'description' => "Price must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "price is required.");
            return new JsonResponse($result, 400);
        }
        
        if(!is_null($product)){
            if(!is_numeric($product)){
                $result = array('code' => 4000, 'description' => "product must be numeric.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "product is required.");
            return new JsonResponse($result, 400);
        }
        /** End validation **/
        
        $m = $em->getRepository(OrderDetails::class)->find($id_order_details);
        if(is_null($m)){
            $result = array('code' => 4006, 'description' => "Unexisting order details.");
            return new JsonResponse($result, 400);
        }
        
        
        $prd = $em->getRepository(Product::class)->find($product);
        if(is_null($prd)){
            $result = array('code' => 4009, 'description' => "Unexisting product.");
            return new JsonResponse($result, 400);
        }
        
        $order = new OrderDetailsMenuProduct();
        $order->setOrderDetails($m);
        $order->setMenu($m->getMenu());
        $order->setProduct($prd);
        $order->setPrice($price);
        $order->setName($name);
        
        $em->persist($order);
        $em->flush();
        
        $result['code'] = 201;
        $result['order_product_id'] = $order->getId();
        
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
    
}
