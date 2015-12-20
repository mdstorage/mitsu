<?php

namespace Catalog\SaabBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogSaabBundle:Default:index.html.twig', array('name' => $name));
    }
}
