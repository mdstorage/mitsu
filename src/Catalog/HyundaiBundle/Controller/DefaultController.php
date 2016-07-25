<?php

namespace Catalog\HyundaiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogHyundaiBundle:Default:index.html.twig', array('name' => $name));
    }
}
