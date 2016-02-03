<?php

namespace Catalog\VolkswagenBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogVolkswagenBundle:Default:index.html.twig', array('name' => $name));
    }
}
