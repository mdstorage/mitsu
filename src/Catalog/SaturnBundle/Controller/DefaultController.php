<?php

namespace Catalog\SaturnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogSaturnBundle:Default:index.html.twig', array('name' => $name));
    }
}
