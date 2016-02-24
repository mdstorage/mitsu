<?php
namespace Catalog\SmartBundle\Controller;

use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Catalog\SmartBundle\Controller\Traits\CatalogFilters;
use Catalog\SmartBundle\Controller\Traits\CommonControllerTrait;
use Symfony\Component\HttpFoundation\Request;
use Catalog\CommonBundle\Components\Factory;

class CatalogController extends BaseController{
    use CatalogFilters;
    use CommonControllerTrait;
    public function bundle()
    {
        return 'CatalogSmartBundle:Catalog';
    }

    public function model()
    {
        return $this->get('smart.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SmartBundle\Components\SmartConstants';
    }
} 