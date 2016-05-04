<?php

namespace Catalog\CadillacBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogCadillacBundle:Default:index.html.twig', array('name' => $name));
    }
}
