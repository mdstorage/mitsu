<?php

namespace Catalog\AlfaRomeoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogAlfaRomeoBundle:Default:index.html.twig', array('name' => $name));
    }
}
