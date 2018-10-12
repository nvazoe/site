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

/**
 * Description of MenusControler
 *
 * @author user
 */
class MenusController extends Controller {
    
    /**
     * @Get("/api/menus")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get menus list"
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
     * @SWG\Tag(name="Menus")
     */
    public function getMenuListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $menus = $em->getRepository(Menu::class)->getMenus(intval($limit), intval($page), false);
        $array = [];
        foreach ($menus as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]["description"] = $l->getDescription();
            $array[$k]["price"] = $l->getPrice().'€';
            $array[$k]["restaurant"]["id"] = $l->getRestaurant() ? $l->getRestaurant()->getId() : null;
            $array[$k]["restaurant"]["name"] = $l->getRestaurant()? $l->getRestaurant()->getName() : null;
            if($l->getImage())
                $array[$k]["restaurant"]["image"] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/restaurant/'.$l->getImage();
        }
        $result['code'] = 200;
        $result['items'] = $array;
        $result['total'] = $em->getRepository(Menu::class)->getMenus($limit, $page, true);
        $result['current_page'] = $page;
        $result['per_page'] = $limit;
        
        return new JsonResponse($result, $result['code'] = 200);
    }
    
    
    /**
     * @Get("/api/menus/{id}")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get menu informations"
     * )
     *
     * 
     * @SWG\Tag(name="Menus")
     */
    public function getMenuAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository(Menu::class)->find(intval($id));
        
        if(!is_null($menu)){
            $result['code'] = 200;
            $result['data']['id'] = $menu->getId();
            $result['data']['name'] = $menu->getName();
            $result['data']['description'] = $menu->getDescription();
            $result['data']['price'] = $menu->getPrice().'€';
            $result['data']['restaurant']['id'] = $menu->getRestaurant() ? $menu->getRestaurant()->getId() : null;
            $result['data']['restaurant']['name'] = $menu->getRestaurant() ? $menu->getRestaurant()->getName() : null;
            $result['data']['category']['id'] = $menu->getCategoryMenu() ? $menu->getCategoryMenu()->getId() : null;
            $result['data']['category']['name'] = $menu->getCategoryMenu() ? $menu->getCategoryMenu()->getName() : null;
            
            // Get options
            $options = $menu->getMenuMenuOptions();
            if(!is_null($options)){
                foreach ($options as $k=>$v){
                    $result['data']['options']["$k"]["name"] = $v->getMenuOption()->getName();
                    
                    //Get products for options
                    $products = $v->getMenuOption()->getYes();
                    if(!is_null($products)){
                        foreach($products as $key=>$val){
                            $result['data']['options']["$k"]["products"]["$key"]['id'] = $val->getProduct()->getName();
                            $result['data']['options']["$k"]["products"]["$key"]['name'] = $val->getProduct()->getName();
                            $result['data']['options']["$k"]["products"]["$key"]['price'] = $val->getAttribut();
                        }
                    }
                }
            }
        }else{
            $result['code'] = 400;
            $result['description'] = "Ce menu n'existe pas.";
        }
        
        
        return new JsonResponse($result, $result['code']);
    }
}
