<?php

namespace Catalog\HondaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogHondaBundle:Default:index.html.twig', array('name' => $name));
    }
}
