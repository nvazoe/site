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
use FOS\RestBundle\Controller\Annotations\Put;
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


/**
 * Description of SecurityController
 *
 * @author user
 */
class SecurityController extends Controller {
    
    /**
     * @Post("/api/security/pass-forget")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Post an email to verify an account"
     * )
     * 
     * @QueryParam(
     *      name="email",
     *      description="email user",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Security")
     */
    public function postEmailAction(Request $request, \Swift_Mailer $mailer){
        $em = $this->getDoctrine()->getManager();
        
        $email = $request->query->get('email')?$request->query->get('email'):$request->request->get('email');
        if(is_null($email)){
            $result = array('code' => 4000, 'description' => "Email is required.");
            return new JsonResponse($result, 400);
        }
        
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if(!$user){
            $result = array('code' => 4000, 'description' => "Unexisting address email.");
            return new JsonResponse($result, 400);
        }
        
        $code = "";
        for ($i = 0; $i<4; $i++) 
        {
            $code .= mt_rand(0,9);
        }
        
        $user->setCode($code);
        $em->flush();
        
        // Sending code by email
        $message = (new \Swift_Message('Mot de passe oublié'))
            ->setFrom('contact@ubereat.com')
            ->setTo($email)
            ->setBody(
                $this->renderView(
                    // templates/emails/registration.html.twig
                    'emails/pass-forget.html.twig',
                    array('name' => $user->getFirstname(), 'code' => $code)
                ),
                'text/html'
            )
            ->setCharset('utf-8')
            /*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        $mailer->send($message);
        
        $result = array(
            'code' => 200,
            'data' => array(
                'user_id' => $user->getId(),
                'verif_code' => $code
            ));
        
        return new JsonResponse($result);
    }
    
    
    /**
     * @Post("/api/security/verification")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="verify an account"
     * )
     * 
     * @QueryParam(
     *      name="id",
     *      description="ID's user",
     *      strict=true
     * )
     * 
     * @QueryParam(
     *      name="code",
     *      description="Code",
     *      strict=true
     * )
     * 
     * @SWG\Tag(name="Security")
     */
    public function postVerifAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $infos = file_get_contents('php://input');
        $data = json_decode($infos, TRUE);
        
        if(array_key_exists('code', $data)){
            if(!is_string($data['code'])){
                $result = array('code' => 4000, 'description' => "code must be string.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "code is required.");
            return new JsonResponse($result, 400);
        }
        
        if(array_key_exists('id', $data)){
            if(!is_int($data['id'])){
                $result = array('code' => 4000, 'description' => "id must be integer.");
                return new JsonResponse($result, 400);
            }
            $user = $em->getRepository(User::class)->find($data['id']);
            if(!$user){
                $result = array('code' => 4000, 'description' => "Unexisting user.");
                return new JsonResponse($result, 400);
            }elseif($user->getCode()!= $data['code']){
                $result = array('code' => 4020, 'description' => "Invalid code.");
                return new JsonResponse($result, 400);
            }
        }else{
            $result = array('code' => 4000, 'description' => "User ID is required.");
            return new JsonResponse($result, 400);
        }
        
        $result = array('code' => 200, 'description' => 'valid code.');
        return new JsonResponse($result);
    }
}
