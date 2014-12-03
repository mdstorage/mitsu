<?php

namespace Catalog\MazdaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogMazdaBundle:Default:index.html.twig', array('name' => $name));
    }
}
