<?php

namespace Catalog\ToyotaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogToyotaBundle:Default:index.html.twig', array('name' => $name));
    }
}
