<?php

namespace Catalog\HummerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogHummerBundle:Default:index.html.twig', array('name' => $name));
    }
}
