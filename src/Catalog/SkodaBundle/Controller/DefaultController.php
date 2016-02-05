<?php

namespace Catalog\SkodaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogSkodaBundle:Default:index.html.twig', array('name' => $name));
    }
}
