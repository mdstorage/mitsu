<?php
namespace Catalog\SubaruBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\SubaruBundle\Controller\Traits\SubaruVinFilters;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class VinController extends BaseController{
   use SubaruVinFilters;
    public function bundle()
    {
        return 'CatalogSubaruBundle:Vin';
    }

    public function model()
    {
        return $this->get('subaru.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SubaruBundle\Components\SubaruConstants';
    }
    public function result1Action(Request $request)
    {
        
       
        if ($request->isXmlHttpRequest()) {

            $vin = $request->get('vin'); var_dump($this->model()); die; 
            $result = $this->model()->getVinFinderResult($vin);
           
            if (!$result) {
                return $this->render($this->bundle().':empty.html.twig');
            }

           setcookie(Constants::PROD_DATE, $result[Constants::PROD_DATE]);
            
            return $this->render($this->bundle().':02_result.html.twig', array(
                'result' => $result
            ));
        }
    }

    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $prodDate = $request->cookies->get(Constants::PROD_DATE);
            $this->addFilter('prodDateFilter', array(Constants::PROD_DATE => $prodDate));
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
            'modificationCode' => $modificationCode,
            'complectationCode' => substr($complectationCode, 0, 4),
            'subComplectationCode' => substr($complectationCode, 3, 3),
            'subGroupCode' => $subGroupCode
        ));

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode);
    }
} 