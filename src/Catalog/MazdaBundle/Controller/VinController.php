<?php
namespace Catalog\MazdaBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class VinController extends BaseController{

    public function bundle()
    {
        return 'CatalogMazdaBundle:Vin';
    }

    public function model()
    {
        return $this->get('mazda.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MazdaBundle\Components\MazdaConstants';
    }

    public function prodDateFilter($oContainer, $parameters)
    {
        return $oContainer;
    }

    public function articulsAction(Request $request)
    {
        $prodDate = $request->cookies->get('mazdaProdDate');
        $this->addFilter('prodDateFilter', array('prodDate' => $prodDate));
        return parent::articulsAction($request);
    }
} 