<?php

namespace Catalog\OldsmobileBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogOldsmobileBundle:Default:index.html.twig', array('name' => $name));
    }
}
