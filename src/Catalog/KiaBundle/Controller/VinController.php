<?php
namespace Catalog\KiaBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\KiaBundle\Controller\Traits\KiaVinFilters;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class VinController extends BaseController{
    use KiaVinFilters;
    public function bundle()
    {
        return 'CatalogKiaBundle:Vin';
    }

    public function model()
    {
        return $this->get('kia.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\KiaBundle\Components\KiaConstants';
    }


    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $prodDate = $request->cookies->get(Constants::PROD_DATE);
            $this->addFilter('prodDateFilter', array(Constants::PROD_DATE => $prodDate));
            $this->addFilter('articulDescFilter', array('regionCode'=>$request->request->get('regionCode'), 'modelCode'=>$request->request->get('modelCode'), 'complectationCode'=>$request->request->get('complectationCode')));
            return parent::articulsAction($request);

        }
    }

    public function vinGroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $this->addFilter('vinGroupFilter', array(
            'regionCode' => $regionCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => substr($complectationCode, 0, 4)
        ));

        return $this->groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode);
    }

    public function vinSubgroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $this->addFilter('vinSubGroupFilter', array(
            'regionCode' => $regionCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => substr($complectationCode, 0, 4),
            'subComplectationCode' => substr($complectationCode, 3, 3)
        ));

        return $this->subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
    }

    public function vinSchemasAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $this->addFilter('vinSchemasFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => substr($modificationCode, 1, 5),
            'subGroupCode' => $subGroupCode
        ));

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
    }


    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);

    }
} 