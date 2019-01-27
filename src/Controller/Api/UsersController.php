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
use App\Entity\Configuration;
use App\Entity\ConnexionLog;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Util\TokenGenerator;
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
    public function postLoginAction(Request $request, \Swift_Mailer $mailer)
    {
        $em = $this->getDoctrine()->getManager();
        $login = $request->query->get('login')?$request->query->get('login'):$request->request->get('login');
        $pass = $request->query->get('pass')?$request->query->get('pass'):$request->request->get('pass');
		$emailAdmin = $em->getRepository(Configuration::class)->findOneByName('AZ_ADMIN_EMAIL')->getValue();
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
        
        $user_account = $em->getRepository(User::class)->findOneBy(array('email'=>$login));
        if(!$user_account){
            $result = array('code' => 4007, 'description' => "Unexisting email.");
            return new JsonResponse($result, 400);
        }
        
        if($user_account->getConnectStatus() == 1){
            $result = array('code' => 4024, 'description' => "You're still log on.");
            return new JsonResponse($result, 400);
        }
        
        if($user_account->getState() == 0){
            /** Sending code by email **/
			$url = $this->generateUrl('activate_account', array('code' => $user_account->getGeneratedCode()), UrlGeneratorInterface::ABSOLUTE_URL);
                $message = (new \Swift_Message('Validation de compte'))
                    ->setFrom($emailAdmin)
                    ->setTo($user_account->getEmail());
                    $htmlBody = $this->renderView(
                        'emails/registration.html.twig',
                            array('name' => $user_account->getFirstname(), 'code' => $user_account->getCode(), 'link' => $url)
                    );

                    $context['titre'] = 'Validation de compte';
                    $context['contenu_mail'] = $htmlBody;
                    $message->setBody(
                        $this->renderView('mail/default.html.twig', $context),
                        'text/html'
                    );
                    $message->setCharset('utf-8');


                $mailer->send($message);
        }
        
        $encoded = $this->container->get("security.password_encoder")->isPasswordValid($user_account, $pass);
        //var_dump($encoded, $pass, $user_account2->getPassword(), $user_account->getPassword());
        if($encoded != $user_account->getPassword()) {
            $result = array('code' => 4008, 'description' => "Bad credentials.");
            return new JsonResponse($result, 403);
        }
        
        $role = "S";
        if(in_array('ROLE_DELIVER', $user_account->getRoles()))
            $role = "D";
        if(in_array('ROLE_CLIENT', $user_account->getRoles()))
            $role = "C";
        if(in_array('ROLE_ADMIN', $user_account->getRoles()))
            $role = "A";
        
        
        $banks = $user_account->getBankCards();
        $cards = [];
        if($banks){
            foreach ($banks as $k=>$v){
                $cards[$k]['card_id'] = $v->getId();
                $cards[$k]['card_number'] = $v->getCardNumber();
            }
        }
        
        // Status connected and set last time logging
        $user_account->setConnectStatus(1);
        $user_account->setLastLogin(new \DateTime());
        
        
        $conn = new ConnexionLog();
        $conn->setUser($user_account);
        $conn->setRole($role);
        $conn->setConnectStatus(1);
        $conn->setDateCreated(new \DateTime());
        $conn->setstartDatetime(time());
        $conn->setendDatetime(time());
        $em->persist($conn);
        $em->flush();
        
        
        return new JsonResponse(array(
            'code' => 200,
            'data' => array(
                'id' => $user_account->getId(),
                'username' => $user_account->getusername(),
                'lastname' => $user_account->getlastname(),
                'email' => $user_account->getemail(),
                'phone' => $user_account->getPhonenumber(),
                'role' => $user_account->getroles(),
                'active' => $user_account->getstate(),
                'avatar' => $user_account->getAvatar() ? $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL)."images/avartars/{$user_account->getId()}/".$user_account->getAvatar() : $this->generateUrl('homepage', array(), UrlGeneratorInterface::ABSOLUTE_URL)."images/avartars/default.png",
                'cards' => $cards
            )
        ));
    }
    
}
