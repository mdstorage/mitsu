<?php

namespace Catalog\BmwBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogBmwBundle:Default:index.html.twig', array('name' => $name));
    }
}
