<?php
namespace Catalog\MazdaBundle\Controller;


use Catalog\CommonBundle\Controller\CatalogController as BaseController;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogMazdaBundle';
    }

    public function model()
    {
        return $this->get('mazda.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MazdaBundle\Components\MazdaConstants';
    }
} 