<?php
namespace Catalog\MercedesBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\MercedesBundle\Controller\Traits\CatalogFilters;
use Catalog\MercedesBundle\Controller\Traits\CommonControllerTrait;
use Catalog\MercedesBundle\Controller\Traits\VinFilters;
use Symfony\Component\HttpFoundation\Request;

class VinController extends BaseController{
    use VinFilters;
    use CatalogFilters;
    use CommonControllerTrait;

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

    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $vin = $request->cookies->get(Constants::VIN);
            $this->addFilter('vinArticulFilter', array(Constants::VIN => $vin));
            return parent::articulsAction($request);
        }
    }
} 