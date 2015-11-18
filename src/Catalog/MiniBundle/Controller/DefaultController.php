<?php

namespace Catalog\MiniBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogMiniBundle:Default:index.html.twig', array('name' => $name));
    }
}
