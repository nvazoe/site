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
use App\Entity\PaymentMode;

/**
 * Description of PaymentModeController
 *
 * @author user
 */
class PaymentModeController extends Controller {
    //put your code here
    
    /**
     * @Get("/api/payment-mode")
     * 
     * *@SWG\Response(
     *      response=200,
     *      description="Get payment mode list"
     * )
     * 
     * 
     * @SWG\Tag(name="Payment Mode")
     */
    public function getPaymentAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $rows = $em->getRepository(PaymentMode::class)->findAll();
        $array = [];
        if($rows){
            foreach ($rows as $k=>$v){
                $array[$k]['id'] = $v->getId();
                $array[$k]['name'] = $v->getName();
            }
        }
        
        $result['code'] = 200;
        $result['data'] = $array;
        $result['total'] = count($array);
        
        return new JsonResponse($result);
    }
}
