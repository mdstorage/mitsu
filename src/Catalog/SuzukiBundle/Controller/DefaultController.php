<?php

namespace Catalog\SuzukiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogSuzukiBundle:Default:index.html.twig', array('name' => $name));
    }
}
