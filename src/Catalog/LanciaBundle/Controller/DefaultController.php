<?php

namespace Catalog\LanciaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogLanciaBundle:Default:index.html.twig', array('name' => $name));
    }
}
