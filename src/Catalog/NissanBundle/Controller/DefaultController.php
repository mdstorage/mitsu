<?php

namespace Catalog\NissanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogNissanBundle:Default:index.html.twig', array('name' => $name));
    }
}
