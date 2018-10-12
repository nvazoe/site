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
     * @SWG\Tag(name="Restaurants")
     */
    public function getRestauListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $listrestau = $em->getRepository(Restaurant::class)->getRestaurants(intval($limit), intval($page), false);
        $array = [];
        foreach ($listrestau as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]['longitude'] = $l->getLongitude();
            $array[$k]['latitude'] = $l->getLatidude();
            $array[$k]['status'] = $l->getStatus();
            if($l->getImage())
                $array[$k]['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/restaurant/'.$l->getImage();
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(Restaurant::class)->getRestaurants($limit, $page, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }
        return new JsonResponse($result);
    }
    
    
    /**
     * @Get("/api/restaurants/{id}")
     * 
     * *@SWG\Response(
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
        
        if(!is_null($restau)){
            $result['code'] = 200;
            $result['data']['id'] = $restau->getId();
            $result['data']['name'] = $restau->getName();
            $result['data']['longitude'] = $restau->getLongitude();
            $result['data']['latitude'] = $restau->getLatidude();
            $result['data']['status'] = $restau->getStatus();
            $notes = $restau->getRestaurantNotes();
            if(count($notes)){
                $t = 0;
                foreach ($notes as $k){
                    $t += $k->getNote();
                }
                $result['data']['notes']['avg'] = ceil($t/count($notes));
                $result['data']['notes']['avis'] = count($notes);
            }
        }else{
            $result['code'] = 400;
            $result['description'] = "Ce restaurant n'existe pas.";
        }
        
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Get("/api/restaurants/{id}/menus")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get restaurant informations"
     * )
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
     * @SWG\Tag(name="Restaurants")
     */
    public function getRestauMenusAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        if(!$em->getRepository(Restaurant::class)->find($id)) {
            $result = array('code' => 4000, 'description' => "Unexisting restaurant id.");
            return new JsonResponse($result, 400);
        }
        $menus = $em->getRepository(Menu::class)->findByRestau(intval($id), intval($limit), intval($page));
        
        $array = [];
        foreach ($menus as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]["description"] = $l->getDescription();
            $array[$k]["price"] = $l->getPrice().'€';
        }
        $result['code'] = 200;
        $result['items'] = $array;
        $result['total'] = $em->getRepository(Menu::class)->findByRestau(intval($id), intval($limit), intval($page), true);
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
        $user = $request->query->get('user')?$request->query->get('user'):$request->request->get('user');
        
        if(is_null($em->getRepository(User::class)->find($user)))
            return new JsonResponse(array(
                'code' => 400,
                'description' => "Identifiant user n'existe pas."
            ), 400);
            
        $restau = new Restaurant();
        $restau->setName($name);
        $restau->setDescription($description);
        $restau->setLongitude($longitude);
        $restau->setLatidude($latitude);
        $restau->setStatus(0);
        $restau->setOwner($em->getRepository(User::class)->find($user));
        $em->persist($restau);
        $em->flush();
        
        $result['code'] = 201;
        $result['restaurant_id'] = $restau->getId();
        
        return new JsonResponse($result, $result['code']);
    }
}
