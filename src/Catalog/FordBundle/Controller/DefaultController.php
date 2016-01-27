<?php

namespace Catalog\FordBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogFordBundle:Default:index.html.twig', array('name' => $name));
    }
}
