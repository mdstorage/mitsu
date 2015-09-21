<?php
namespace Catalog\HuyndaiBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\HuyndaiBundle\Controller\Traits\HuyndaiVinFilters;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class VinController extends BaseController{
   use HuyndaiVinFilters;
    public function bundle()
    {
        return 'CatalogHuyndaiBundle:Vin';
    }

    public function model()
    {
        return $this->get('huyndai.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\HuyndaiBundle\Components\HuyndaiConstants';
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
    public function index1Action($error_message = null)
    {

        setcookie(Constants::VIN, '');
        return $this->render($this->bundle().':01_index.html.twig', array('error_message' => $error_message));
    }
    
    public function vinComplectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null)
    {
       $vin = $request->get('vin');
        $vinComplectations = $this->model()->getVinComplectations($vin);
        if (empty($vinComplectations)) {
            setcookie(Constants::VIN, "");
            return $this->index1Action('Автомобиль с таким VIN кодом не найден');
        }
        setcookie(Constants::VIN, $vin);
        foreach($vinComplectations as $item)
			{
			$regionCode = $item['cmftrepc'];
			$modelCode =  rawurlencode($item['cmodnamepc']);
			$modificationCode = $item['dmodyr'];
			}

        $this->addFilter('vinComplectationsFilter', array(
            'vinComplectations' => $vinComplectations
        ));

        return parent::complectationsAction($request, $regionCode, $modelCode, $modificationCode);
    }
    
    public function vinComplectationsFilter($oContainer, $parameters)
    {
        $complectations = $parameters['vinComplectations'];
        foreach($complectations as $item)
			{
			$vinComplectations[]=$item['hmodtyp'];	
			}
        foreach ($oContainer->getActiveModification()->getComplectations() as $complectation) {
            if (!in_array($complectation->getCode(), $vinComplectations, true)) {
                $oContainer->getActiveModification()->removeComplectation($complectation->getCode());
            }
        }

        return $oContainer;
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
    	print_r(substr($modificationCode, 1, 5));die;
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