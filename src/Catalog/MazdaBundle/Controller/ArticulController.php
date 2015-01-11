<?php
namespace Catalog\MazdaBundle\Controller;


use Catalog\CommonBundle\Controller\ArticulController as BaseController;

class ArticulController extends BaseController{

    public function bundle()
    {
        return 'CatalogMazdaBundle:Articul';
    }

    public function model()
    {
        return $this->get('mazda.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MazdaBundle\Components\MazdaConstants';
    }
} 