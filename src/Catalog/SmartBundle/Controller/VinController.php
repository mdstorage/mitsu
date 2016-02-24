<?php
namespace Catalog\SmartBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\SmartBundle\Controller\Traits\CatalogFilters;
use Catalog\SmartBundle\Controller\Traits\CommonControllerTrait;
use Catalog\SmartBundle\Controller\Traits\VinFilters;
use Symfony\Component\HttpFoundation\Request;

class VinController extends BaseController{
    use VinFilters;
    use CatalogFilters;
    use CommonControllerTrait;

    public function bundle()
    {
        return 'CatalogSmartBundle:Vin';
    }

    public function model()
    {
        return $this->get('smart.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SmartBundle\Components\SmartConstants';
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