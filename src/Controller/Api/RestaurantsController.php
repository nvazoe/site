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
use App\Entity\RestaurantNote;
use App\Entity\ShippingNote;
use App\Entity\Menu;
use App\Entity\Configuration;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of RestaurantsController
 *
 * @author user
 */
class RestaurantsController extends Controller {
    
    /**
     * @Get("/api/restaurants")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get restaurants list"
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
     *      name="longitude",
     *      description="longitude position of restaurant",
     *      strict=false
     * )
     * 
     * 
     * @QueryParam(
     *      name="latitude",
     *      description="latitude position of restaurant",
     *      strict=false
     * )
     * 
     * @SWG\Tag(name="Restaurants")
     */
    public function getRestauListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        $status = $request->query->get('status')?$request->query->get('status'):$request->request->get('status');
        $distance = $em->getRepository(Configuration::class)->findOneByName('RESTAURANT_RANGE')->getValue();
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        $status = ($status == null) ? 1 : $status;
        
        $listrestau = $em->getRepository(Restaurant::class)->getRestaurants($longitude, $latitude, $status, intval($limit), intval($page), false, $distance);
        $array = [];
        foreach ($listrestau as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]['longitude'] = $l->getLongitude();
            $array[$k]['latitude'] = $l->getLatidude();
            $array[$k]['status'] = $l->getStatus();
            $array[$k]['city'] = $l->getCity();
            $array[$k]['address'] = $l->getAddress();
            $array[$k]['note'] = $l->getNote();
            if($l->getImage()){
                $array[$k]['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/restaurant/'.$l->getImage();
            }else{
                $array[$k]['image'] = null;
            }
            
            
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(Restaurant::class)->getRestaurants($longitude, $latitude, $status, $limit, $page, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }
        return new JsonResponse($result);
    }
    
    
    /**
     * @Get("/api/restaurants/{id}")
     * 
     * @SWG\Response(
     *      response=200,
     *      description="Get restaurant informations"
     * )
     *
     * 
     * @SWG\Tag(name="Restaurants")
     */
    public function getRestauAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $restau = $em->getRepository(Restaurant::class)->find(intval($id));
        
        $infosShip = $em->getRepository(ShippingNote::class)->getShippingByRestaurant($restau);
        $avg = 0;
        if(count($infosShip)){
            $som = 0;
            $total = count($infosShip);
            foreach ($infosShip as $ind){
                $som += (int)$ind->getRestauNote();
            }
            $avg = ceil(($som/$total)/2);
        }else{
            $total = 0;
        }
        
        if(!is_null($restau)){
            $result['code'] = 200;
            $result['data']['id'] = $restau->getId();
            $result['data']['name'] = $restau->getName();
            $result['data']['longitude'] = $restau->getLongitude();
            $result['data']['latitude'] = $restau->getLatidude();
            $result['data']['status'] = $restau->getStatus();
            $result['data']['city'] = $restau->getCity();
            $result['data']['address'] = $restau->getAddress();
            $result['data']['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/restaurant/'.$restau->getImage();
            $result['data']['note'] = $restau->getNote();;
            
        }else{
            $result['code'] = 400;
            $result['description'] = "Ce restaurant n'existe pas.";
        }
        
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Get("/api/restaurants/{id}/menus")
     * 
     * @SWG\Response(
     *      response=200,
     *      description="Get restaurant's menu informations"
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
     *      name="category",
     *      description="specify category restriction",
     *      strict=false
     * )
     * 
     * @SWG\Tag(name="Restaurants")
     */
    public function getRestauMenusAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $category = $request->query->get('category')?$request->query->get('category'):$request->request->get('category');
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        if($category){
            if(!is_numeric($category)){
                $result = array('code' => 4000, 'description' => "category must be numeric");
                return new JsonResponse($result, 400);
            }
        }
        
        if(!$em->getRepository(Restaurant::class)->find($id)) {
            $result = array('code' => 4000, 'description' => "Unexisting restaurant id.");
            return new JsonResponse($result, 400);
        }
        $menus = $em->getRepository(Menu::class)->findByRestau(intval($id), intval($limit), intval($page),$category, false );
        
        $array = [];
        foreach ($menus as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]["description"] = $l->getDescription();
            $array[$k]["price"] = floatval($l->getPrice());
                if($l->getImage()){
                $array[$k]['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/menu/'.$l->getImage();
                }else{
                $array[$k]['image'] = null;
                }
            $array[$k]["category"] = $l->getCategoryMenu() ? $l->getCategoryMenu()->getName() :  '';
            $array[$k]["restaurant"] = $l->getRestaurant()->getName();
            }
        $result['code'] = 200;
        $result['items'] = $array;
        $result['total'] = $em->getRepository(Menu::class)->findByRestau(intval($id), intval($limit), intval($page), true, $category);
        $result['current_page'] = $page;
        $result['per_page'] = $limit;
        
        return new JsonResponse($result);
    }
    
    /**
     * @Post("/api/restaurants")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new restaurant."
     * )
     * 
     * @QueryParam(
     *      name="name",
     *      description="Name of the restaurant",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="description",
     *      description="Description of the restaurant",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="latitude",
     *      description="Position latitude of the restaurant",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="longitude",
     *      description="Position longitude of the restaurant",
     *      strict=false
     * )
     
     * @QueryParam(
     *      name="image",
     *      description="image of the restaurant",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="address",
     *      description="address of the restaurant",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="city",
     *      description="city address of the restaurant",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="user",
     *      description="ID of the user owner",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Restaurants")
     */
    public function postRestaurantAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $name = $request->query->get('name')?$request->query->get('name'):$request->request->get('name');
        $description = $request->query->get('description')?$request->query->get('description'):$request->request->get('description');
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        $address = $request->query->get('address')?$request->query->get('address'):$request->request->get('address');
        $city = $request->query->get('city')?$request->query->get('city'):$request->request->get('city');
        $user = $request->query->get('user')?$request->query->get('user'):$request->request->get('user');
        $image = $request->files->get('image');
        
        /** Validate  user **/
        if(!is_null($user)){
            if(!is_numeric($user)){
                $result = array('code' => 4000, 'description' => "user must be numeric");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "user is required");
            return new JsonResponse($result, 400);
        }
        
        $u = $em->getRepository(User::class)->find($user);
        if(is_null($u)){
            return new JsonResponse(array(
                'code' => 4000,
                'description' => "Unexisting user."
            ), 400);
        }
        
        /** Validate name **/
        if(!is_null($name)){
            if(!is_string($name)){
                $result = array('code' => 4000, 'description' => "name must be string");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "name is required");
            return new JsonResponse($result, 400);
        }
        
        /** Validate description **/
        if(!is_null($description)){
            if(!is_string($description)){
                $result = array('code' => 4000, 'description' => "description must be string");
                return new JsonResponse($result, 400);
            }
        }
        
        
        $restau = new Restaurant();
        
        if (!is_null($image)) {
            $fileName = md5(random_bytes(32)) . '.' . $image->guessExtension();
            $public_path = $request->server->get('DOCUMENT_ROOT');
            $dest_dir = $public_path . "/images/restaurant"; //die(var_dump($dest_dir));

            if (file_exists($dest_dir) === FALSE) {
                mkdir($dest_dir, 0777, true);
            }

            $image->move($dest_dir, $fileName);

            $restau->setImage($fileName);
        }
        
        $restau->setName($name);
        $restau->setDescription($description);
        $restau->setLongitude($longitude);
        $restau->setLatidude($latitude);
        $restau->setAddress($address);
        $restau->setCity($city);
        $restau->setStatus(false); 
        $restau->setOwner($u);
        $em->persist($restau);
        $em->flush();
        
        
        $result['code'] = 201;
        $result['restaurant_id'] = $restau->getId();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Post("/api/restaurants/{id}/notes")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Add a note to a restaurant by client."
     * )
     * 
     * @QueryParam(
     *      name="client",
     *      description="Client giving the note",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="note",
     *      description="Note given by a client on the restaurant",
     *      strict=true
     * )
     *
     * 
     * 
     * @SWG\Tag(name="Restaurants")
     */
    public function postRestauNoteAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $client = $request->query->get('client')?$request->query->get('client'):$request->request->get('client');
        $note = $request->query->get('note')?$request->query->get('note'):$request->request->get('note');
        
        $restau = $em->getRepository(Restaurant::class)->find($id);
        if(!$restau){
            $result = array('code' => 400, 'description' => "Unexisting restaurant.");
            return new JsonResponse($result, 400);
        }
        
        if($client){
            if(!is_numeric($client)){
                $result = array('code' => 4000, 'description' => 'client must be integer/numeric');
                return new JsonResponse($result, 400);
            }
            
            $cl = $em->getRepository(User::class)->find($client);
            if(!in_array("ROLE_CLIENT", $cl->getRoles())){
                $result = array('code' => 4015, 'description' => "User must be a client.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => 'client is required.');
            return new JsonResponse($result, 400);
        }
        
        if($note){
            if(!is_int(intval($note))){
                $result = array('code' => 4000, 'description' => 'note must be interger');
                return new JsonResponse($result, 400);
            }elseif(intval($note) < 1 || intval($note) > 5){
                $result = array('code' => 4000, 'description' => 'note is between 1 and 5.');
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => 'note is required.');
            return new JsonResponse($result, 400);
        }
        
        $restaunote = RestaurantNote();
        $restaunote->setUser($cl);
        $restaunote->setRestaurant($restau);
        $restaunote->setNote($note);
        
        $em->persist($restaunote);
        $em->flush();
        
        $result['code'] = 201;
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Get("/api/restaurants/{id}/orders")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get restaurants list"
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
     *      description="status order",
     *      strict=false
     * )
     * 
     * @SWG\Tag(name="Restaurants")
     */
    public function getRestauOrders(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $status = $request->query->get('status')?$request->query->get('status'):$request->request->get('status');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $restau = $em->getRepository(Restaurant::class)->find(intval($id));
        if(!$restau){
            $result = array('code' => 4000, 'description' => "Unexisting restaurant.");
            return new JsonResponse($result, 400);
        }
        
        $listorders = $em->getRepository(Restaurant::class)->getOrders($id, $status, intval($limit), intval($page), false);
        $array = [];
        
        foreach ($listorders as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]['amount'] = $l->getAmount();
            $array[$k]['reference'] = $l->getRef();
            $array[$k]['date'] = $l->getDateCreated()->format('d-m-Y');
            $array[$k]['hour'] = $l->getDateCreated()->format('H:i');
            $array[$k]["client"]["id"] = $l->getClient()->getId();
            $array[$k]["client"]["username"] = $l->getClient()->getUsername();
            $array[$k]["client"]["position"]["latitude"] = $l->getClient()->getLatitude();
            $array[$k]["client"]["position"]["longitude"] = $l->getClient()->getLongitude();
            $array[$k]['status']['id'] = $l->getOrderStatus()->getId();
            $array[$k]['status']['name'] = $l->getOrderStatus()->getName();
            $array[$k]['restaurant']['id'] = $l->getRestaurant()->getId();
            $array[$k]['restaurant']['name'] = $l->getRestaurant()->getName();
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(Restaurant::class)->getOrders($id, $status, intval($limit), intval($page), true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }else{
            $result['items'] = [];
        }
        return new JsonResponse($result);
    }
}
