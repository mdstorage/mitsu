<?php

namespace Catalog\BuickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogBuickBundle:Default:index.html.twig', array('name' => $name));
    }
}
