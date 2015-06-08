<?php
namespace Catalog\MercedesBundle\Controller;

use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Catalog\MercedesBundle\Controller\Traits\CatalogFilters;
use Catalog\MercedesBundle\Controller\Traits\CommonControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Catalog\CommonBundle\Components\Factory;

class CatalogController extends BaseController{
    use CatalogFilters;
    use CommonControllerTrait;
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