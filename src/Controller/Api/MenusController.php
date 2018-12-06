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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            $result['data']['price'] = floatval($menu->getPrice());
            $result['data']['restaurant']['id'] = $menu->getRestaurant() ? $menu->getRestaurant()->getId() : null;
            $result['data']['restaurant']['name'] = $menu->getRestaurant() ? $menu->getRestaurant()->getName() : null;
            $result['data']['category']['id'] = $menu->getCategoryMenu() ? $menu->getCategoryMenu()->getId() : null;
            $result['data']['category']['name'] = $menu->getCategoryMenu() ? $menu->getCategoryMenu()->getName() : null;
            if($menu->getImage()){
                $result['data']["image"] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/menu/'.$menu->getImage();
            }else{
                $result['data']["image"] = null;
            }
            // Get options
            $options = $menu->getMenuMenuOptions();
            if(!is_null($options)){
                foreach ($options as $k=>$v){
                    $result['data']['options']["$k"]["id"] = $v->getMenuOption()->getId();
                    $result['data']['options']["$k"]["name"] = $v->getMenuOption()->getName();
                    $result['data']['options']["$k"]["item_required"] = $v->getMenuOption()->getItem();
                    $result['data']['options']["$k"]["type"] = $v->getMenuOption()->getType();
                    //Get products for options
                    $products = $v->getMenuOption()->getYes();
                    if(!is_null($products)){
                        foreach($products as $key=>$val){
                            $result['data']['options']["$k"]["products"]["$key"]['id'] = $val->getid();
                            $result['data']['options']["$k"]["products"]["$key"]['name'] = $val->getProduct()->getName();
                            $result['data']['options']["$k"]["products"]["$key"]['price'] = floatval($val->getAttribut());
                            $result['data']['options']["$k"]["products"]["$key"]['image'] = $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL).'images/product/'.$val->getProduct()->getImage();
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
    
    
    /**
     * @Post("/api/menus")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new menu."
     * )
     * 
     * @QueryParam(
     *      name="name",
     *      description="Name of the menu",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="description",
     *      description="Description of the menu",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="price",
     *      description="Price of the menu.",
     *      strict=true
     * )
     * 
     *  @QueryParam(
     *      name="type_menu",
     *      description="Type of menu",
     *      strict=false
     * )
     
     * @QueryParam(
     *      name="image",
     *      description="image of the menu",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="category_menu",
     *      description="category of menu",
     *      strict=true
     * )
     * 
     *  @QueryParam(
     *      name="restaurant",
     *      description="Restaurant owning the menu",
     *      strict=true
     * )
     * 
     * 
     * @SWG\Tag(name="Menus")
     */
    public function postMenuAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $name = $request->query->get('name')?$request->query->get('name'):$request->request->get('name');
        $description = $request->query->get('description')?$request->query->get('description'):$request->request->get('description');
        $price = $request->query->get('price')?$request->query->get('price'):$request->request->get('price');
        $typeMenu = $request->query->get('type_menu')?$request->query->get('type_menu'):$request->request->get('type_menu');
        $categoryMenu = $request->query->get('category_menu')?$request->query->get('category_menu'):$request->request->get('category_menu');
        $restaurant = $request->query->get('restaurant')?$request->query->get('restaurant'):$request->request->get('restaurant');
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
        
        // validate description
        if(!is_null($description)){
            if(!is_string($description)){
                $result = array('code' => 4000, 'description' => "descriptioin must be string.");
                return new JsonResponse($result, 400);
            }
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
        
        // validate type of menu
        if(!is_null($typeMenu)){
            if(!is_numeric($typeMenu)){
                $result = array('code' => 4000, 'description' => "type_menu must be integer/numeric.");
                return new JsonResponse($result, 400);
            }
            
            $tm = $em->getRepository(TypeMenu::class)->find($typeMenu);
            if(is_null($tm)){
                $result = array('code' => 4012, 'description' => "Unexisting Type menu.");
                return new JsonResponse($result, 400);
            }
        }
        
        // Validate categorie menu
        if(!is_null($categoryMenu)){
            if(!is_numeric($categoryMenu)){
                $result = array('code' => 4000, 'description' => "category_menu must be integer/numeric.");
                return new JsonResponse($result, 400);
            }
            $cm = $em->getRepository(CategoryMenu::class)->find($categoryMenu);
            if(is_null($cm)){
                $result = array('code' => 4013, 'description' => "Unexisting category menu.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4013, 'description' => "category_menu ID is required.");
            return new JsonResponse($result, 400);
        }
        
        
        // Validate restaurant
        if(!is_null($restaurant)){
            if(!is_numeric($restaurant)){
                $result = array('code' => 4000, 'description' => "restaurant must be integer/numeric.");
                return new JsonResponse($result, 400);
            }
            $res = $em->getRepository(Restaurant::class)->find($restaurant);
            if(is_null($res)){
                $result = array('code' => 4013, 'description' => "Unexisting restaurant.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4013, 'description' => "restaurant ID is required.");
            return new JsonResponse($result, 400);
        }
        
        
        $menu = new Menu();
        
        if (!is_null($image)) {
            $fileName = md5(random_bytes(32)) . '.' . $image->guessExtension();
            $public_path = $request->server->get('DOCUMENT_ROOT');
            $dest_dir = $public_path . "/images/menu"; //die(var_dump($dest_dir));

            if (file_exists($dest_dir) === FALSE) {
                mkdir($dest_dir, 0777, true);
            }

            $image->move($dest_dir, $fileName);

            $menu->setImage($fileName);
        }
        
        $menu->setName($name);
        $menu->setDescription($description);
        $menu->setPrice($price);
        $menu->setTypeMenu($tm);
        $menu->setCategoryMenu($cm);
        $menu->setRestaurant($res);
        
        $em->persist($menu);
        $em->flush();
        
        $result['code'] = 201;
        $result['restaurant_id'] = $menu->getId();
        
        return new JsonResponse($result, $result['code']);
        
    }
    
    
    /**
     * @Post("/api/menus/{id}/options")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Record new menu option."
     * )
     * 
     * @QueryParam(
     *      name="name",
     *      description="Option's name",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="option_type",
     *      description="type's menu option.",
     *      strict=true
     * )
     *
     *  @QueryParam(
     *      name="item",
     *      description="item products allowed.",
     *      strict=true,
     *      default=1
     * )
     * 
     * 
     * @SWG\Tag(name="Menus")
     */
    public function postOptionMenuAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $name = $request->query->get('name')?$request->query->get('name'):$request->request->get('name');
        $optionType = $request->query->get('option_type')?$request->query->get('option_type'):$request->request->get('option_type');
        $item = $request->query->get('item')?$request->query->get('item'):$request->request->get('item');
        
        is_null($item) ? $item = 0 : $item = $item;
        
        $menu = $em->getRepository(Menu::class)->find($id);
        if(is_null($menu)){
            $result = array('code' => 4014, 'description' => "Unexisting menu ID");
            return new JsonResponse($result, 400);
        }
        
        if(!is_null($name)){
            if(!is_string($name)){
                $result = array('code' => 4000, 'description' => "name must be string.");
                return new JsonResponse($result, 400);
            }
        }
        
        if(!is_null($optionType)){
            if(!in_array($optionType, ["REQUIRED", "NOT REQUIRED"])){
                $result = array('code' => 4000, 'description' => "option_type must be one of this value [\"REQUIRED\", \"NOT REQUIRED\"].");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "option_type is required.");
            return new JsonResponse($result, 400);
        }
        
        if($item == 0){
            if($optionType == "REQUIRED"){
                $result = array('code' => 4000, 'description' => "Please specifiy number of products required for this menu.");
                return new JsonResponse($result, 400);
            }
        }
        
        
        // Menuoption
        $menuoption = new MenuOption();
        $menuoption->setName($name);
        $menuoption->setType($optionType);
        $menuoption->setItem($item);
        
        $em->persist($menuoption);
        $em->flush();
        
        $Mnmenuoption = new MenuMenuOption();
        $Mnmenuoption->setMenu($menu);
        $Mnmenuoption->setMenuOption($menuoption);
        $em->persist($Mnmenuoption);
        $em->flush();
        
        $result['code'] = 201;
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Post("/api/menus/{id}/notes")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Add a note to a menu by client."
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
     *      description="Note given by a client on the menu",
     *      strict=true
     * )
     *
     * 
     * 
     * @SWG\Tag(name="Menus")
     */
    public function postMenuNoteAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        $client = $request->query->get('client')?$request->query->get('client'):$request->request->get('client');
        $note = $request->query->get('note')?$request->query->get('note'):$request->request->get('note');
        
        $menu = $em->getRepository(Menu::class)->find($id);
        if(!$menu){
            $result = array('code' => 400, 'description' => "Unexisting menu");
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
        
        $menunote = MenuNote();
        $menunote->setUser($cl);
        $menunote->setMenu($menu);
        $menunote->setNote($note);
        
        $em->persist($menunote);
        $em->flush();
        
        $result['code'] = 201;
        
        return new JsonResponse($result, $result['code']);
    }
}
