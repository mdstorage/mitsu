<?php

namespace Catalog\BmwMotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogBmwMotoBundle:Default:index.html.twig', array('name' => $name));
    }
}
