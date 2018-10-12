<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security as SecurityContext;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller {

    /**
     * 
     * @Route("/login", name="adminlogin")
     */
    public function adminLoginAction(Request $request, AuthenticationUtils $authenticationUtils) {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        if (!is_null($error)) {
            $this->addFlash(
                    'error', $error->getMessageKey()
            );
        }


        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/security/login.html.twig', array(
                    'last_username' => $lastUsername,
                    'error' => $error,
        ));
    }

    /**
     * @Method({"GET", "POST"})
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request) {
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        $params = array(
            "last_username" => $session->get(SecurityContext::LAST_USERNAME),
            "error" => $error,
        );

        return $this->render($this->get('theme')->getTheme() . '/login.html.twig', $params);
    }

    

}
