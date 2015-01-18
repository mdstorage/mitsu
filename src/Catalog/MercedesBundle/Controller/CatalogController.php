<?php
namespace Catalog\MercedesBundle\Controller;

use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class CatalogController extends BaseController{
    public function bundle()
    {
        return 'CatalogMercedesBundle:Catalog';
    }

    public function model()
    {
        return $this->get('mercedes.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MercedesBundle\Components\MercedesConstants';
    }
} 