<?php

namespace Acme\BillingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $request = new Request();
        $uri = $_GET;

        var_dump($request); die;

        return $this->render('AcmeBillingBundle:Default:index.html.twig', array('name' => $uri));
    }
}
