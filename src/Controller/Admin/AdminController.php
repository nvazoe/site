<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Util\LegacyFormHelper;
use App\Entity\User;
use App\Entity\Client;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\CategoryMenu;
use App\Entity\Product;
use App\Entity\MenuOptionProducts;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Menu;
use App\Entity\Restaurant;
use App\Entity\MenuOption;
use App\Entity\MenuMenuOption;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Order;
use App\Entity\OrderStatus;

class AdminController extends BaseAdminController {
    
    /**
     * @Method({"GET"})
     * @Route("/accueil", name="admin_home_dashboard")
     * @Template("/admin/home.html.twig")
     */ 
    public function adminDashboard(Request $request){
        return array();
    }
    
    public function prePersistUserEntity($entity) {
        //  set password
//        die(var_dump($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword())));
        $entity->setRoles([$entity->getRoles()]);
        $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
    }
    
    public function preUpdateUserEntity($entity) {
        //$entity->setRoles(['ROLE_ADMIN']);
        if (!is_null($entity->getPassword())) {
            //  update password 
//            die(var_dump($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword())));
            $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
        } else {
            $original = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
            $password = $original['password'];
            $entity->setPassword($password);
        }
    }
    
    public function prePersistClientEntity($entity) {
        //  set password
        $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
    }
    
    public function preUpdateClientEntity($entity) {
        if (!is_null($entity->getPassword())) {
            //  update password          
            $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
        } else {
            $original = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
            $password = $original['password'];
            $entity->setPassword($password);
        }
    }
    
    
    public function createDishMenuEntityFormBuilder($entity, $view){
        $formBuilder = parent::createEntityFormBuilder($entity, $view);
        $em = $this->getDoctrine()->getManager();
        
        $categories = $em->getRepository(Category::class)->findAll();
        
//        foreach ($categories as $ca){
//            $champ = str_replace(' ', '_', $ca->getName());
//            $formBuilder->add($champ, EntityType::class, array(
//                'class' => CategoryProduct::class,
//                'choice_label' => function($CategoryProduct){
//                    return $CategoryProduct->getProduct()->getName();
//                }
//            ));
//        }
        
        return $formBuilder;
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/add-menu/{id_menu}", name="add_menu")
     * @Template("/admin/form-menu.html.twig")
     */ 
    public function addMenu(Request $request, $id_menu=null){
        $em = $this->getDoctrine()->getManager();
        
        if(is_null($id_menu)){
            $products = $em->getRepository(Product::class)->findAll();
            $categories = $em->getRepository(CategoryMenu::class)->findAll();
            $restaurants = $em->getRepository(Restaurant::class)->findAll();
        }else{
            $menu = new Menu($id_menu);
            die($menu->getName());
        }
        
        
        if($request->isMethod('POST')){
            $menu = $request->get('dishmenu');
            $options = $request->get('options');
            $photo = $request->files->get('dishmenu')['photo'];
//            echo '<pre>';
//            var_dump($options);
//            echo '</pre>'; die();
            $dishmenu = new Menu();
            $dishmenu->setName($menu['name']);
            $dishmenu->setRestaurant($em->getRepository(Restaurant::class)->find($menu['restaurant']));
            $dishmenu->setDescription($menu['description']);
            $dishmenu->setPrice($menu['price']);
            //$dishmenu->setProduct($em->getRepository(Product::class)->find($data['product_id']));
            
            
            // Manage file
            if (!is_null($photo)) {
                $fileName = $this->generateUniqueFileName() . '.' . $photo->guessExtension();
                $public_path = $request->server->get('DOCUMENT_ROOT');
                $dest_dir = $public_path . "/images/menu"; //die(var_dump($dest_dir));

                if (file_exists($dest_dir) === FALSE) {
                    mkdir($dest_dir, 0777, true);
                }

                $photo->move($dest_dir, $fileName);

                $dishmenu->setImage($fileName);
            }
            
            $em->persist($dishmenu);
            $em->flush();
            
            foreach ($options as $opt){
                $option = new MenuOption();
                $option->setName($opt['name']);
                $option->setType($opt['type']);
                $option->setItem($opt['item']);
                
                $em->persist($option);
                $em->flush();
                
                // association produit - option
                foreach ($opt['productoption'] as $prdOpt){
                    $prdOption = new MenuOptionProducts();
                    $prdOption->setMenuOption($option);
                    $prdOption->setProduct($em->getRepository(Product::class)->find($prdOpt['product']));
                    $prdOption->setAttribut($prdOpt['price']);
                    
                    $em->persist($prdOption);
                    $em->flush();
                }
                // association menu - option
                $menuoption = new MenuMenuOption();
                $menuoption->setMenu($dishmenu);
                $menuoption->setMenuOption($option);
                
                $em->persist($menuoption);
                $em->flush();
            }
            
            
            $this->addFlash('success', 'Menu correctement ajouté.');
            return $this->redirectToRoute('easyadmin', [
                'entity'=> 'Menu',
                'action'=> 'list',
                'menyIndex' => 3,
                'submenuIndex'=> -1
            ]);
        }
        return array(
            'products' => $products,
            'categories' => $categories,
            'restaurants' => $restaurants
        );
    }
    
    
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/search-product", name="search_product")
     */ 
    public function getproduct(Request $request){
        $em = $this->getDoctrine()->getManager();
        $product = $request->get('product');
        $products = $em->getRepository(Product::class)->findByNameField($product);
        
        $array = [];
        foreach ($products as $k => $p){
            $array["$k"]["id"] = $p->getId();
            $array["$k"]["value"] = $p->getName();
            $array["$k"]["label"] = $p->getName();
            $array["$k"]["price"] = $p->getPrice();
            //$array[] = $p->getName();
        }
        //die(var_dump($response));
        return new JSONResponse($array) ;
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/add-client", name="add_client")
     * @Template("/admin/form-client.html.twig")
     */ 
    public function addclientAction(Request $request, UserPasswordEncoderInterface $encoder){
        $em = $this->getDoctrine()->getManager();
        
        if($request->isMethod('POST')){
            $client = $request->get('client');
            
            $obj = new User();
            $obj->setUsername($client['username']);
            $obj->setFirstname($client['firstname']);
            $obj->setLastname($client['lastname']);
            $obj->setEmail($client['email']);
            $obj->setRoles([$client['roles']]);
            
            //encode password
            $encoded = $encoder->encodePassword($obj, $client['password']);
            $obj->setPassword($encoded);
            
            $em->persist($obj);
            $em->flush();
            
            $this->addFlash('success', "Client enregistré.");
            return $this->redirectToRoute('easyadmin', [
                'entity'=> 'User',
                'action'=> 'list',
                'menyIndex' => 0,
                'submenuIndex'=> -1
            ]);
        }
        //die(var_dump($response));
        return array() ;
    }
    
    
    /**
     * @Method({"GET"})
     * @Route("/order/invoice/{id}", name="invoice")
     * @Template("/admin/invoice.html.twig")
     */
    public function viewInvoiceAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $order = $em->getRepository(Order::class)->find($id);
        $delivers = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        
        $total = 0.00;
        $details = $order->getOrderDetails();
        foreach($details as $dt){
            $total += intval($dt->getQuantity()) * floatval($dt->getPrice());
        }
        
        return array('order' => $order, 'delivers' => $delivers, 'total' => $total);
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/order/assign-deliver", name="assign")
     * @Template("/admin/invoice.html.twig")
     */
    public function assignDeliverAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $delivers = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        if($request->getMethod('post')){
            $deliver = $request->get('deliver');
            $order = $request->get('order');
            
            
            $orderObj = $em->getRepository(Order::class)->find($order);
            $status = $em->getRepository(OrderStatus::class)->find(4);
            $msg = $em->getRepository(User::class)->find($deliver);
            $orderObj->setMessenger($msg);
            $orderObj->setOrderStatus($status);
            $em->flush();
            
            $this->addFlash('success', "Livreur assigné.");
            
            return $this->redirectToRoute('invoice', array('id'=>$order));
        }
    }
}
