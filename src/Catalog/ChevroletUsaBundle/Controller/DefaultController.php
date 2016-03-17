<?php

namespace Catalog\ChevroletUsaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CatalogChevroletUsaBundle:Default:index.html.twig', array('name' => $name));
    }
}
