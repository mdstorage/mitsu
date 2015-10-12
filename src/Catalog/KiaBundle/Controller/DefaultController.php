<?php

namespace Catalog\KiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogKiaBundle:Default:index.html.twig', array('name' => $name));
    }
}
