<?php
namespace Catalog\MazdaBundle\Controller;

use Catalog\CommonBundle\Controller\VinController as BaseController;

class VinController extends BaseController{

    public function bundle()
    {
        return 'CatalogMazdaBundle:Vin';
    }

    public function model()
    {
        return $this->get('mazda.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MazdaBundle\Components\MazdaConstants';
    }

    public function prodDateFilter($oContainer, $parameters)
    {
        return $oContainer;
    }

    public function indexAction()
    {
        return $this->render($this->bundle().':01_index.html.twig');
    }
} 