<?php

namespace Catalog\AudiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogAudiBundle:Default:index.html.twig', array('name' => $name));
    }
}
