<?php

namespace Catalog\MazdaBundle\Controller;

class DefaultController extends BaseController
{
    public function indexAction($name)
    {
        $this->clearParams();
        $this->addParam('name', $name);
        return $this->render('CatalogMazdaBundle:Default:index.html.twig', array('params' => $this->getParams()));
    }
}
