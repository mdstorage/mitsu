<?php

namespace Catalog\FiatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogFiatBundle:Default:index.html.twig', array('name' => $name));
    }
}
