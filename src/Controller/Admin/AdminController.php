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
use App\Entity\DeliveryProposition;
use App\Entity\ConnexionLog;
use App\Entity\ShippingLog;
use App\Entity\Configuration;

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
        $entity->setConnectStatus(0);
        $entity->setRoles([$entity->getRoles()]);
        $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
    }
    
    public function preUpdateUserEntity($entity) {
        if (!is_null($entity->getPassword())) {
            //  update password 
            $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
        } else {
            $original = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
            $password = $original['password'];
            $entity->setPassword($password);
        }
    }
    
    public function preUpdateDeliverEntity($entity) {
        if (!is_null($entity->getPassword())) {
            //  update password 
            $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
        } else {
            $original = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
            $password = $original['password'];
            $entity->setPassword($password);
        }
    }
    
    public function preUpdateAdminEntity($entity) {
        if (!is_null($entity->getPassword())) {
            //  update password 
            $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
        } else {
            $original = $this->em->getUnitOfWork()->getOriginalEntityData($entity);
            $password = $original['password'];
            $entity->setPassword($password);
        }
    }
    
    public function prePersistClientEntity($entity) {
        //  set password
        $entity->setRoles(["ROLE_CLIENT"]);
        $entity->setConnectStatus(0);
        $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
    }
    
    public function prePersistAdminEntity($entity) {
        //  set password
        $entity->setRoles(["ROLE_ADMIN"]);
        $entity->setConnectStatus(0);
        $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
    }
    
    public function prePersistDeliverEntity($entity) {
        //  set password
        $entity->setRoles(["ROLE_DELIVER"]);
        $entity->setConnectStatus(0);
        $entity->setPassword($this->container->get("security.password_encoder")->encodePassword($entity, $entity->getPassword()));
    }
    
    public function prePersistRestaurantEntity($entity) {
        $em = $this->getDoctrine()->getManager();
        
        $lieu = $entity->getAddress().', '.$entity->getCp().', '.$entity->getCity();
        $url = "https://maps.google.com/maps/api/geocode/json?components=country:FR&key=AIzaSyAt0qBmUbppuFGzGCqhqREOdgwBq-vgJkA&address=".urlencode($lieu);
        $location = file_get_contents($url);
        $loc = json_decode($location, true);
        //echo '<pre>'; die(var_dump($loc["results"][0]["geometry"]["location"])); echo '</pre>';
        $geo_restau = $loc["results"][0]["geometry"]["location"];
        $entity->setLongitude($geo_restau["lng"]);
        $entity->setLatidude($geo_restau["lat"]);
        
        
        // Create Stripe account ID
        $stripePublicKey = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_SECRET')->getValue();
        \Stripe\Stripe::setApiKey($stripePublicKey);
        
        $acct = \Stripe\Account::create([
            "country" => "US",
            "type" => "custom"
        ]);
        if($acct)
            $entity->setStripeAccountId($acct->id);
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
    
    
    public function createRestaurantEntityFormBuilder($entity, $view){
        $formBuilder = parent::createEntityFormBuilder($entity, $view);
        $users = $this->em->getRepository(User::class)->findAllUserByRole('ROLE_ADMIN', false);
        
        $formBuilder->add('owner', ChoiceType::class, array(
            'choices' => $users,
            'choice_label' => function($user, $key, $value){
                return $user->getFirstname();
            },
        ));
        
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
            $restaurants = $this->getUser()->getRestaurants();
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
            
            if($options){
                foreach ($options as $opt){
                $option = new MenuOption();
                $option->setName($opt['name']);
                $option->setType($opt['type']);
                $option->setItem($opt['item']);
                
                $em->persist($option);
                
                
                // association produit - option
                foreach ($opt['productoption'] as $prdOpt){
                    $prdOption = new MenuOptionProducts();
                    $prdOption->setMenuOption($option);
                    $prdOption->setProduct($em->getRepository(Product::class)->find($prdOpt['id']));
                    $prdOption->setAttribut($prdOpt['price']);
                    
                    $em->persist($prdOption);
                    
                }
                // association menu - option
                $menuoption = new MenuMenuOption();
                $menuoption->setMenu($dishmenu);
                $menuoption->setMenuOption($option);
                
                $em->persist($menuoption);
                
            }
            }
            
            $em->flush();
            
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
        $user = $this->getUser()->getid();
        $products = $em->getRepository(Product::class)->findByNameField($product, $user);
        
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
        $status = $em->getRepository(OrderStatus::class)->findAll();
        
        $total = 0.00;
        $details = $order->getOrderDetails();
        foreach($details as $dt){
            $total += intval($dt->getQuantity()) * floatval($dt->getPrice());
            $dtt = $dt->getOrderDetailsMenuProducts();
            foreach ($dtt as $val){
                $total += floatval($val->getPrice());
            }
        }
        
        $lieu = $order->getAddress().', '.$order->getCp().', '.$order->getCity();
        
        $url = "https://maps.google.com/maps/api/geocode/json?components=country:FR&key=AIzaSyAt0qBmUbppuFGzGCqhqREOdgwBq-vgJkA&address=".urlencode($lieu);
         $location = file_get_contents($url);
         $loc = json_decode($location, true);
        //echo '<pre>'; die(var_dump($loc["results"][0]["geometry"]["location"])); echo '</pre>';
        $geo_order = $loc["results"][0]["geometry"]["location"];
        
        return array('order' => $order, 'delivers' => $delivers, 'total' => $total, 'geo_order'=>$geo_order, 'status'=>$status);
    }
    
    
    /**
     * @Method({"GET"})
     * @Route("/order/ticket/{id}", name="ticket")
     * @Template("/admin/printable.html.twig")
     */
    public function printTicketAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $order = $em->getRepository(Order::class)->find($id);
        $delivers = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        
        $total = 0.00;
        $details = $order->getOrderDetails();
        foreach($details as $dt){
            $total += intval($dt->getQuantity()) * floatval($dt->getPrice());
            $dtt = $dt->getOrderDetailsMenuProducts();
            foreach ($dtt as $val){
                $total += floatval($val->getPrice());
            }
        }
        
        return array('order' => $order, 'delivers' => $delivers, 'total' => $total);
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/order/propose-delivery-service", name="assign")
     * @Template("/admin/invoice.html.twig")
     */
    public function assignDeliverAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $delivers = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        if($request->getMethod('post')){
            $delivers = $request->get('delivers');
            $order = $request->get('order');
            
            
            $orderObj = $em->getRepository(Order::class)->find($order);
            
            if(is_array($delivers)){
                foreach ($delivers as $dl){
                    $delProposition = new DeliveryProposition();
                    $delProposition->setRestaurant($orderObj->getRestaurant());
                    $delProposition->setValueResto(1);
                    $delProposition->setDeliver($em->getRepository(User::class)->find($dl));
                    $delProposition->setValueDeliver(0);
                    $delProposition->setCommand($orderObj);

                    $em->persist($delProposition);
                }
            }
            
            
            $status = $em->getRepository(OrderStatus::class)->find(2);
            $orderObj->setOrderStatus($status);
            
            $em->flush();
            
            $this->addFlash('success', "Propositions de livraison envoyées.");
            
            return $this->redirectToRoute('invoice', array('id'=>$order));
        }
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/order/assign-deliver", name="assign_deliver")
     * @Template("/admin/invoice.html.twig")
     */
    public function assignDeliver2Action(Request $request){
        $em = $this->getDoctrine()->getManager();
        $delivers = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        if($request->getMethod('post')){
            $deliver = $request->get('deliver');
            $order = $request->get('order');
            
            
            $orderObj = $em->getRepository(Order::class)->find($order);
            
            $status = $em->getRepository(OrderStatus::class)->find(6);
            $messenger = $em->getRepository(User::class)->find($deliver);
            $orderObj->setMessenger($messenger);
            $orderObj->setOrderStatus($status);
            
            $em->flush();
            
            
            // send mail notification
            
            $this->addFlash('success', "Propositions de livraison envoyées.");
            
            return $this->redirectToRoute('invoice', array('id'=>$order));
        }
    }
    
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/order/change-status", name="change_status")
     * @Template("/admin/invoice.html.twig")
     */
    public function changeOrderStatusAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        if($request->getMethod('post')){
            $status = $request->get('status');
            $order = $request->get('order');
            $orderObj = $em->getRepository(Order::class)->find($order);
            
            if($status == 4){
                try{
                    if($ord->getPaymentMode()->getId() == 1){
                        $stripePublicKey = $em->getRepository(Configuration::class)->findOneByName('AZ_STRIPE_ACCOUNT_SECRET')->getValue();
                        // Set your secret key: remember to change this to your live secret key in production
                        // See your keys here: https://dashboard.stripe.com/account/apikeys
                        \Stripe\Stripe::setApiKey($stripePublicKey);

                        $charge = \Stripe\Charge::create([
                            'amount' => $orderObj->getAmount() * 100, // $15.00 this time
                            'currency' => 'eur',
                            'customer' => $orderObj->getClient()->getStripeId(), // Previously stored, then retrieved
                            'metadata' => ['order_id' => $orderObj->id],
                            'destination' =>  [
                                'account' => "{".$orderObj->getRestaurant()->getStripeAccountid()."}",
                                'amount' => $orderObj->getRestauEarn()
                            ],
                            'source' => $orderObj->getClient()->getStripeid()
                        ]);
                    }

                    if($charge){
                        //Update status order
                        $orderObj->setOrderStatus($em->getRepository(OrderStatus::class)->find(4));
                        $orderObj->setBalanceTransaction($charge->balance_transaction);
                    }

                }catch(\Exception $e){
                    echo $e->getMessage();
                }
            }else{
                $orderObj->setOrderStatus($em->getRepository(OrderStatus::class)->find($status));
            }
            
            $em->flush();
            
            $this->addFlash('success', "Changement de status de la commande effectué.");
            
            return $this->redirectToRoute('invoice', array('id'=>$order));
        }
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/order/map-delivers", name="map_delivers")
     * @Template("/admin/map-delivers.html.twig")
     */
    public function deliversInMapAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $dels = $em->getRepository(User::class)->getConnectedUser('ROLE_DELIVER');
        $delivers = [];
        foreach ($dels as $k=>$l){
            $delivers[$k]['name'] = $l->getFirstname().' '.$l->getLastname();
            $delivers[$k]['phone'] = $l->getPhoneNumber();
            $delivers[$k]['address'] = $l->getAddress();
            $delivers[$k]['lat'] = $l->getLatitude();
            $delivers[$k]['lng'] = $l->getLongitude();
        }
        return array('delivers' => $delivers);
    }
    
    /**
     * @Method({"GET"})
     * @Route("/map-delivers")
     */
    public function deliversInMapAPIAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $dels = $em->getRepository(User::class)->getConnectedUser('ROLE_DELIVER');
        $delivers = [];
        foreach ($dels as $k=>$l){
            $delivers[$k]['name'] = $l->getFirstname().' '.$l->getLastname();
            $delivers[$k]['phone'] = $l->getPhoneNumber();
            $delivers[$k]['address'] = $l->getAddress();
            $delivers[$k]['lat'] = $l->getLatitude();
            $delivers[$k]['lng'] = $l->getLongitude();
        }
        return new JsonResponse($delivers);
    }
    
    /**
     * @Method({"GET", "POST"})
     * @Route("/order/logged-delivers", name="logged_delivers")
     * @Template("/admin/logged-delivers.html.twig")
     */
    public function deliversConnectedInfos(Request $request){
        $em = $this->getDoctrine()->getManager();
        $period = $request->get('period', 1);
        $dels = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        $arrayDels = [];
        foreach($dels as $k=>$v){
            $logs = $em->getRepository(ShippingLog::class)->getDeliverActionBetweenAPeriod($v->getId(), time(), time()- (60*(int)$period));
            //$dels[$k]->logs = $logs;
            if(count($logs) <= 2){
                $v->delCount = count($logs);
                $arrayDels[] = $v;
            }
        }
        
        return array('delivers' => $arrayDels, 'period' => $period);
    }
    
    
    /**
     * @Method({"GET"})
     * @Route("/orders/dashboard", name="dashboard_o")
     * @Template("/admin/orders-dashboard.html.twig")
     */ 
    public function ordersDashboard(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $orders = $em->getRepository(Order::class)->getUserOrders($this->getUser()->getId(), 100, 1, array(1,2,7), false);
        
        return array('orders' => $orders, 'totalRows' => ceil(count($orders)/3));
    }
    
    /**
     * @Method({"GET"})
     * @Route("/orders/infos")
     */ 
    public function jsonGetorders(Request $request){
        $em = $this->getDoctrine()->getManager();
        $status = $request->get('status');
        $orders = $em->getRepository(Order::class)->getUserOrders($this->getUser()->getId(), 100, 1, $status, false);
        $items = [];
        foreach($orders as $key=>$val){
            $items[$key] = $val->getId();
        }
        
        return new JsonResponse(array('code'=>200, 'items' => $items));
    }
    
    
    /**
     * @Method({"GET"})
     * @Route("/orders/change-status")
     */ 
    public function orderchangestatus(Request $request){
        $em = $this->getDoctrine()->getManager();
        $status = $request->get('status');
        $ord = $request->get('order');
        //die(var_dump($ord));
        $order = $em->getRepository(Order::class)->find($ord);
        if($order){
            $order->setOrderStatus($em->getRepository(OrderStatus::class)->find($status));
        }
        $em->flush();
        
        return new JsonResponse(array('code'=>200 ));
    }
    
    
    public function createRestaurantListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        //$idowner = isset($_GET['id'])?$_GET['id']:false;
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            $idowner = $this->getUser()->getId();
            if($idowner){
                $dqlFilter = 'entity.owner = '.$idowner;
            }
			//die('tests');
        }
        

        return parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);
    }
    
    public function createMenuListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            foreach ($restaurants as $k=>$v){
                $ids[] = $v->getId();
            }
            $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
            //$qb->andWhere('entity.deleteStatus = :del')->setParameter('del', 0);
        }
        
        

        return $qb;
    }
    
    
    public function createOrderListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            foreach ($restaurants as $k=>$v){
                $ids[] = $v->getId();
            }
            $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
        }
        

        return $qb;
    }
    
    public function createNewOrderListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $qb->andWhere('entity.orderStatus = 1');
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            foreach ($restaurants as $k=>$v){
                $ids[] = $v->getId();
            }
            $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
        }
        
        return $qb;
    }
    
    public function createApprovedOrderListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $qb->andWhere('entity.orderStatus = 2');
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            foreach ($restaurants as $k=>$v){
                $ids[] = $v->getId();
            }
            $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
        }
        
        return $qb;
        

        return $qb;
    }
    
    public function createShippingOrderListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $qb->andWhere('entity.orderStatus = 6');
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            foreach ($restaurants as $k=>$v){
                $ids[] = $v->getId();
            }
            $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
        }
        
        return $qb;
    }
    
    
    public function createShippedOrderListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $qb->andWhere('entity.orderStatus = 4');
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            foreach ($restaurants as $k=>$v){
                $ids[] = $v->getId();
            }
            $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
        }
        
        return $qb;
    }
    
    public function createTicketListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            foreach ($restaurants as $k=>$v){
                $ids[] = $v->getId();
            }
            $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);

        }
        
        return $qb;
    }
    
    
    public function createProductListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createListQueryBuilder($dqlFilter, $sortDirection, $sortField);
        $ids = [];
        if ( !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            if ( $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
                foreach ($restaurants as $k=>$v){
                    $ids[] = $v->getId();
                }
                $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
            }
        }
        
        

        return $qb;
    }
    
    
    public function createAgendaSearchQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        $yearfilter = trim($this->request->query->get('yearfilter'));
        $monthfilter = trim($this->request->query->get('monthfilter'));
//        $monthfilter = '2019-01-';
        $qb = parent::createSearchQueryBuilder($entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection, $dqlFilter);
        if($monthfilter != '' || $yearfilter != '') {
            $filter = ($yearfilter ? $yearfilter.$monthfilter : "%$monthfilter");
            $qb->andWhere("entity.date like '$filter%'");
        }
        return  $qb;
    }
    
    
    public function createProductSearchQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createSearchQueryBuilder($entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection, $dqlFilter);
        $ids = [];
        foreach ($restaurants as $k=>$v){
            $ids[] = $v->getId();
        }
        $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
        return  $qb;
    }
    
    public function createMenuSearchQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        $restaurants = $this->getUser()->getRestaurants();
        $qb = parent::createSearchQueryBuilder($entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection, $dqlFilter);
        $ids = [];
        foreach ($restaurants as $k=>$v){
            $ids[] = $v->getId();
        }
        $qb->andWhere('entity.restaurant IN (:ids)')->setParameter('ids', $ids);
        //$qb->andWhere('entity.deleteStatus = :del')->setParameter('del', 0);
        return  $qb;
    }
    
    public function createRestaurantSearchQueryBuilder($entityClass, $searchQuery, array $searchableFields, $sortField = null, $sortDirection = null, $dqlFilter = null)
    {
        $idowner = $this->getUser()->getId();
        if($idowner){
            $dqlFilter = 'entity.owner = '.$idowner;
        }

        return parent::createSearchQueryBuilder($entityClass, $searchQuery, $searchableFields, $sortField, $sortDirection, $dqlFilter);
    }
    
    
    /**
     * @Method({"GET"})
     * @Route("/delivers/{id}", name="info_deliver")
     * @Template("/admin/info-deliver.html.twig")
     */ 
    public function viewDeliverAction(Request $request, $id){
        $id = base64_decode($id);
    }
    
    
}
