<?php

namespace Catalog\InfinitiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogInfinitiBundle:Default:index.html.twig', array('name' => $name));
    }
}
