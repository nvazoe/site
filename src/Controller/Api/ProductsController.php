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
use App\Entity\MenuMenuOption;
use App\Entity\Product;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Description of ProductsController
 *
 * @author user
 */
class ProductsController extends Controller {
    
    /**
     * @Post("/api/products")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new product."
     * )
     * 
     * @QueryParam(
     *      name="name",
     *      description="Name of the product",
     *      strict=true
     * )
     
     *
     *  @QueryParam(
     *      name="price",
     *      description="Price of the product.",
     *      strict=true
     * )
     
     * @QueryParam(
     *      name="image",
     *      description="image of the product",
     *      strict=false
     * )
     *
     * 
     * 
     * @SWG\Tag(name="Products")
     */
    public function postProductAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $name = $request->query->get('name')?$request->query->get('name'):$request->request->get('name');
        $price = $request->query->get('price')?$request->query->get('price'):$request->request->get('price');
        $image = $request->files->get('image');
        
        // validate name
        if(!is_null($name)){
            if(!is_string($name)){
                $result = array('code' => 4000, 'description' => "name must be string.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "name is required.");
            return new JsonResponse($result, 400);
        }
        
        // validate price
        if(!is_null($price)){
            if(!is_string($price)){
                $result = array('code' => 4000, 'description' => "price must be string.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "price is required.");
            return new JsonResponse($result, 400);
        }
        
        $product = new Product();
        
        if (!is_null($image)) {
            $fileName = md5(random_bytes(32)) . '.' . $image->guessExtension();
            $public_path = $request->server->get('DOCUMENT_ROOT');
            $dest_dir = $public_path . "/images/product"; //die(var_dump($dest_dir));

            if (file_exists($dest_dir) === FALSE) {
                mkdir($dest_dir, 0777, true);
            }

            $image->move($dest_dir, $fileName);

            $menu->setImage($fileName);
        }
        
        $product->setName($name);
        $product->setPrice($price);
        
        $em->persist($product);
        $em->flush();
        
        $result['code'] = 201;
        $result['product_id'] = $product->getId();
        
        $restaurantObj->selectfield = "t.*,  ( 6371  acos( cos( radians($lat) )  cos( radians( latitude ) )  cos( radians( longitude ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance";
    $restaurantObj->setTri('distance ASC');
        
        return new JsonResponse($result, $result['code']);
        
    }
    
    
    /**
     * @Get("/api/products")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get products list"
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
     * @SWG\Tag(name="Products")
     */
    public function getListProductAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $products = $em->getRepository(Product::class)->getProducts(intval($limit), intval($page), false);
        $array = [];
        foreach ($products as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["name"] = $l->getName();
            $array[$k]["price"] = floatval($l->getPrice());
            if($l->getImage()){
                $array[$k]["image"] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/product/'.$l->getImage();
            }else{
                $array[$k]["image"] = null;
            }
        
            
        }
        
        $result['code'] = 200;
        $result['items'] = $array;
        $result['total'] = $em->getRepository(Product::class)->getProducts($limit, $page, true);
        $result['current_page'] = $page;
        $result['per_page'] = $limit;
        
        return new JsonResponse($result, $result['code'] = 200);
    }
    
    
    /**
     * @Get("/api/products/{id}")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get product infos"
     * )
     * 
     
     * 
     * @SWG\Tag(name="Products")
     */
    public function getProductAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository(Product::class)->find($id);
        
        if(!$product){
            $result['code'] = 400;
            $result['description'] = "Ce client n'existe pas.";
        }else{
            $result['code'] = 200;
            $result['data']['id'] = $product->getId();
            $result['data']['name'] = $product->getName();
            $result['data']['price'] = $product->getPrice();
            $result['data']['description'] = $product->getDescription();
            if($product->getImage()){
                $result['data']['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/product/'.$product->getImage();
            }else{
                $result['data']['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/menu/dish.jpg';
            }
            
        }
        
        return new JsonResponse($result, $result['code']);
    }
}
