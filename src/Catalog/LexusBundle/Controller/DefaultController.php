<?php

namespace Catalog\LexusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogLexusBundle:Default:index.html.twig', array('name' => $name));
    }
}
