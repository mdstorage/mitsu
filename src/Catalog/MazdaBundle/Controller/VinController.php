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
        $prodDate = $parameters[Constants::PROD_DATE];
        foreach ($oContainer->getActivePnc()->getArticuls() as $key => $articul) {
            if ($articul->getOption(Constants::START_DATE) > $prodDate || $articul->getOption(Constants::END_DATE) < $prodDate) {
                $oContainer->getActivePnc()->removeArticul($key);
            }
        }

        return $oContainer;
    }

    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $prodDate = $request->cookies->get(Constants::PROD_DATE);
            $this->addFilter('prodDateFilter', array(Constants::PROD_DATE => $prodDate));
            return parent::articulsAction($request);
        }
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode);
    }
} 