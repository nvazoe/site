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
use App\Entity\TypeMenu;
use App\Entity\CategoryMenu;
use App\Entity\MenuOption;
use App\Entity\MenuNote;
use App\Entity\MenuMenuOption;
use App\Entity\Configuration;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Description of CategoriesController
 *
 * @author user
 */
class CategoriesController extends Controller{
    
    
    /**
     * @Get("/api/categories")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get categories list"
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
     * @SWG\Tag(name="Categories")
     */
    public function getCategoriesListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $categories = $em->getRepository(CategoryMenu::class)->getCategories(intval($limit), intval($page), false);
        $array = [];
        foreach ($categories as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]["description"] = $l->getDescription();
            $array[$k]["image"] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/category/'.$l->getImage();
        }
            
        $result['code'] = 200;
        $result['items'] = $array;
        $result['total'] = $em->getRepository(CategoryMenu::class)->getCategories($limit, $page, true);
        $result['current_page'] = $page;
        $result['per_page'] = $limit;
        
        return new JsonResponse($result, 200);
    }
    
    
    
    /**
     * @Get("/api/categories/{id}/menus")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get menus list by category"
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
     * @SWG\Tag(name="Categories")
     */
    public function getMenuCategoryListAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $distance = $em->getRepository(Configuration::class)->findOneByName('RESTAURANT_RANGE')->getValue();
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $cat = $em->getRepository(CategoryMenu::class)->find($id);
        if(!$cat){
            $result = array('code' => 4016, 'description' => "Unexisting category.");
            return new JsonResponse($result, 400);
        }
        
        $array = [];
        $menus = $em->getRepository(CategoryMenu::class)->getMenus($id, $limit, $page, false, $distance, $longitude, $latitude);
        foreach ($menus as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]["description"] = $l->getDescription();
            $array[$k]["price"] = floatval($l->getPrice());
            if($l->getImage()){
                $array[$k]["image"] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/menu/'.$l->getImage();
            }else{
                $array[$k]["image"] = null;
            }
            $array[$k]["restaurant"]["id"] = $l->getRestaurant() ? $l->getRestaurant()->getId() : null;
            $array[$k]["restaurant"]["name"] = $l->getRestaurant()? $l->getRestaurant()->getName() : null;
        }
            
        $result['code'] = 200;
        $result['items'] = $array;
        $result['total'] = $em->getRepository(CategoryMenu::class)->getMenus($id, $limit, $page, true, $distance, $longitude, $latitude);
        $result['current_page'] = $page;
        $result['per_page'] = $limit;
        
        return new JsonResponse($result, 200);
    }
    
    
    /**
     * @Get("/api/categories/{id}/restaurants")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get restaurant list by category"
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
     * @SWG\Tag(name="Categories")
     */
    public function getRestauCategoryListAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        $distance = $em->getRepository(Configuration::class)->findOneByName('RESTAURANT_RANGE')->getValue();
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $cat = $em->getRepository(CategoryMenu::class)->find($id);
        if(!$cat){
            $result = array('code' => 4016, 'description' => "Unexisting category.");
            return new JsonResponse($result, 400);
        }
        
        $array = [];
        $restaurants = $em->getRepository(CategoryMenu::class)->getRestaurants($id, $limit, $page, false, $distance, $longitude, $latitude);
        foreach ($restaurants as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]['longitude'] = $l->getLongitude();
            $array[$k]['latitude'] = $l->getLatidude();
            $array[$k]['status'] = $l->getStatus();
            $array[$k]['note'] = $l->getNote();
            if($l->getImage())
                $array[$k]['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/restaurant/'.$l->getImage();
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = $em->getRepository(CategoryMenu::class)->getRestaurants($id, $limit, $page, true, $distance, $longitude, $latitude);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }else{
            $result['items'] = [];
        }
        
        return new JsonResponse($result, 200);
    }

}
