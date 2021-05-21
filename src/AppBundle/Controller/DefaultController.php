<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->render('default/indexAdmin.html.twig');
        }
        else {
            return $this->render( 'default/index.html.twig');
        }
    }
}
