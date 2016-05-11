<?php

namespace Catalog\VolvoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogVolvoBundle:Default:index.html.twig', array('name' => $name));
    }
}
