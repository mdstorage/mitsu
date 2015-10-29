<?php

namespace Catalog\BmvBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogBmvBundle:Default:index.html.twig', array('name' => $name));
    }
}
