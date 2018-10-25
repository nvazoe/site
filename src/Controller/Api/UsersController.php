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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
/**
 * Description of UsersController
 *
 * @author user
 */
class UsersController extends Controller {
    
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    
    /**
     * @Post("/auth/login")
     * @QueryParam(
     *   name="login",
     *   description="User login.",
     *   strict=true
     * )
     * 
     * @QueryParam(
     *   name="pass",
     *   description="User passsword.",
     *   strict=true
     * )
     * 
     * * @SWG\Response(
     *     response=200,
     *     description="Returns the user details informations"
     * )
	 * @SWG\Tag(name="Users")
     */
    public function postLoginAction(Request $request)
    {
        $login = $request->query->get('login')?$request->query->get('login'):$request->request->get('login');
        $pass = $request->query->get('pass')?$request->query->get('pass'):$request->request->get('pass');
//        if(!$login || !$pass) {
//          throw new HttpException(401, 'Credentials are required.');
//        }
        /** Validate input **/
        if(!is_null($login)){
            if(!is_string($login)){
                $result = array('code' => 4000, 'description' => "login must be string/email");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "login is required.");
            return new JsonResponse($result, 400);
        }
        
        if(!is_null($pass)){
            if(!is_string($pass)){
                $result = array('code' => 4000, 'description' => "pass must be string.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "pass is required.");
            return new JsonResponse($result, 400);
        }
        
        $user_account = $this->getDoctrine()->getRepository(User::class)->findOneBy(array('email'=>$login));
        if(!$user_account){
            $result = array('code' => 4007, 'description' => "Unexisting email.");
            return new JsonResponse($result, 400);
        }
        $encoded = $this->container->get("security.password_encoder")->isPasswordValid($user_account, $pass);
        //var_dump($encoded, $pass, $user_account2->getPassword(), $user_account->getPassword());
        if($encoded != $user_account->getPassword()) {
            $result = array('code' => 4008, 'description' => "Bad credentials.");
            return new JsonResponse($result, 403);
        }
        
        return new JsonResponse(array(
            'code' => 200,
            'data' => array(
                'id' => $user_account->getId(),
                'username' => $user_account->getusername(),
                'lastname' => $user_account->getlastname(),
                'email' => $user_account->getemail(),
                'role' => $user_account->getroles(),
                'active' => $user_account->getstate(),
            )
        ));
    }
    
    
    /**
     * @Get("/Users")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get users list"
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
     * @SWG\Tag(name="Users")
     */
    public function getUsersListAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $limit = $request->query->get('limit')?$request->query->get('limit'):$request->request->get('limit');
        $page = $request->query->get('page')?$request->query->get('page'):$request->request->get('page');
        
        $menus = $em->getRepository(User::class)->findAll();
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
            //$result['total'] = $em->getRepository(Menu::class)->getMenus($limit, $page, true);
            $result['current_page'] = $page;
            $result['per_page'] = $limit;
        }
           
        
        return new JsonResponse($result, $result['code'] = 200);
    }
}
