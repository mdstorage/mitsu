<?php
namespace Catalog\MercedesBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\MercedesBundle\Controller\Traits\VinFilters;
use Symfony\Component\HttpFoundation\Request;

class VinController extends BaseController{
    use VinFilters;

    public function bundle()
    {
        return 'CatalogMercedesBundle:Vin';
    }

    public function model()
    {
        return $this->get('mercedes.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MercedesBundle\Components\MercedesConstants';
    }

} 