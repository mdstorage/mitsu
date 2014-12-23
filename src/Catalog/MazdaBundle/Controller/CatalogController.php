<?php
namespace Catalog\MazdaBundle\Controller;


use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Catalog\MazdaBundle\Models\MazdaCatalogModel;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogMazdaBundle';
    }

    public function model()
    {
        return $this->get('mazda.catalog.model');
    }
} 