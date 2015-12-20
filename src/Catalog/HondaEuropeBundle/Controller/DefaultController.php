<?php

namespace Catalog\HondaEuropeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogHondaEuropeBundle:Default:index.html.twig', array('name' => $name));
    }
}
