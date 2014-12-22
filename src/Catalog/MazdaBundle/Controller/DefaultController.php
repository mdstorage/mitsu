<?php

namespace Catalog\MazdaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catalog\CommonBundle\Components\Factory;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        $region = Factory::createRegion();
        $region->setName('EU');

        return $this->render('CatalogMazdaBundle:Default:index.html.twig', array('name' => $region->getRuname()));
    }
}
