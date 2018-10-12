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
use App\Entity\DeliveryDisponibility;
use App\Entity\Menu;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Description of DeliversController
 *
 * @author user
 */
class DeliversController extends Controller{
    /**
     * @Get("/api/delivers")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get delivers list"
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
     * @SWG\Tag(name="Delivers")
     */
    public function getDeliverListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        // Default values
        $limit = ($limit == null) ? 100 : $limit;
        $page = ($page == null) ? 1 : $page;
        
        $menus = $em->getRepository(User::class)->findAllUserByRole('ROLE_DELIVER', false);
        $array = [];
        foreach ($menus as $k => $l){
            $array[$k]["id"] = $l->getId();
            $array[$k]["firstname"] = $l->getFirstname();
            $array[$k]["lastname"] = $l->getLastname();
            $array[$k]["username"] = $l->getUsername();
            $array[$k]["email"] = $l->getEmail();
        }
        $result['code'] = 200;
        if(count($array) > 0){
            $result['items'] = $array;
            $result['total'] = count($array);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }
           
        
        return new JsonResponse($result, $result['code'] = 200);
    }
    
    
    /**
     * @Post("/api/delivers")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new delivery man."
     * )
     * 
     * @QueryParam(
     *      name="username",
     *      description="username of delivery man",
     *      strict=true
     * )
     * @QueryParam(
     *      name="firstname",
     *      description="Firstname of delivery man",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="lastname",
     *      description="Lastname of the delivery man",
     *      strict=false
     * )
     *
     *  @QueryParam(
     *      name="latitude",
     *      description="Position latitude of the delivery",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="longitude",
     *      description="Position longitude of the delivery",
     *      strict=false
     * )
     * 
     *  @QueryParam(
     *      name="email",
     *      description="Email",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="password",
     *      description="password of delivery man",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function postDeliveryAction(Request $request, UserPasswordEncoderInterface $encoder){
        $em = $this->getDoctrine()->getManager();
        $username = $request->query->get('username')?$request->query->get('username'):$request->request->get('username');
        $firstname = $request->query->get('firstname')?$request->query->get('firstname'):$request->request->get('firstname');
        $lastname = $request->query->get('lastname')?$request->query->get('lastname'):$request->request->get('lastname');
        $longitude = $request->query->get('longitude')?$request->query->get('longitude'):$request->request->get('longitude');
        $latitude = $request->query->get('latitude')?$request->query->get('latitude'):$request->request->get('latitude');
        $password = $request->query->get('password')?$request->query->get('password'):$request->request->get('password');
        $email = $request->query->get('email')?$request->query->get('email'):$request->request->get('email');
        
        /** Validate inputs email**/
        if(!is_null($email)){
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $result = array('code' => 4011, 'description' => "Invalid address email."); 
                return new JsonResponse($result, 400);
            }
            $mail = $em->getRepository(User::class)->findOneByEmail($email);
            if($mail){
                $result = array('code' => 4012, 'description' => "Email already exist."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "email is required."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate inputs password **/
        if(!is_null($password)){
            if (!is_string($password)) {
                $result = array('code' => 4000, 'description' => "password must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "password is required."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate inputs username **/
        if(!is_null($username)){
            if (!is_string($username)) {
                $result = array('code' => 4000, 'description' => "username must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "username is required."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate inputs firstname **/
        if(!is_null($firstname)){
            if (!is_string($firstname)) {
                $result = array('code' => 4000, 'description' => "firstname must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "firstname is required."); 
            return new JsonResponse($result, 400);
        }
            
        $del = new User();
        $del->setUsername($username);
        $del->setFirstname($firstname);
        $del->setLongitude($longitude);
        $del->setLatitude($latitude);
        $del->setLastname($lastname);
        $del->setEmail($email);
        $del->setPassword($encoder->encodePassword($del, $password));
        $del->setRoles(["ROLE_DELIVER"]);
        $em->persist($del);
        $em->flush();
        
        $result['code'] = 201;
        $result['delivery_id'] = $del->getId();
        
        return new JsonResponse($result, $result['code']);
    }
    
    
    /**
     * @Post("/api/delivers/{id}/disponibilities")
     * 
     * *@SWG\Response(
     *      response=201,
     *      description="Record new delivery disponibilities."
     * )
     
     * 
     * @QueryParam(
     *      name="dayz",
     *      description="Set day of the week",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="hourStart",
     *      description="Set starting hour.",
     *      strict=true,
     *      default=00
     * )
     *
     *  @QueryParam(
     *      name="hourEnd",
     *      description="Position latitude of the delivery",
     *      strict=true,
     *      default=23
     * )
     * 
     * @SWG\Tag(name="Delivers")
     */
    public function postDeliveryDispoAction(Request $request, $id){
        $em = $this->getDoctrine()->getManager();
        
        $dayz = $request->query->get('dayz')?$request->query->get('dayz'):$request->request->get('dayz');
        $start = $request->query->get('hourStart')?$request->query->get('hourStart'):$request->request->get('hourStart');
        $end = $request->query->get('hourEnd')?$request->query->get('hourEnd'):$request->request->get('hourEnd');
        
        // Default values
        $start = ($start == null) ? 00 : $start;
        $end = ($end == null) ? 23 : $end;
        
        /** Validate dayz input **/
        if(!is_null($dayz)){
            if (!is_string($dayz)) {
                $result = array('code' => 4000, 'description' => "dayz must be string."); 
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "dayz is required. Ex: Monday, Tuesday ..."); 
            return new JsonResponse($result, 400);
        }
        
        /** Validate hourStart **/
        
        
        $user = $em->getRepository(User::class)->find($id);
        if(!in_array("ROLE_DELIVER", $user->getRoles())){
            $result = array('code' => 4013, 'description' => 'Ce livreur n\'existe pas.');
            return new JsonResponse($result, 400);
        }
        
        if(intval($start)> intval($end)){
            $result = array('code' => 4014, 'description' => "L'heure de début doit être inférieur à l'heure de fin.");
            return new JsonResponse($result, 400);
        }
        
            
        $del = new DeliveryDisponibility();
        $del->setDeliveryMan($user);
        $del->setEndHour($end);
        $del->setStartHour($start);
        $del->setDay($dayz);
        $em->persist($del);
        $em->flush();
        
        $result['code'] = 201;
        $result['disponiblity_user_id'] = $user->getId();
        $result['day'] = $del->getDay();
        $result['from'] = $del->getStartHour();
        $result['to'] = $del->getEndHour();
        
        return new JsonResponse($result, $result['code']);
    }
}
