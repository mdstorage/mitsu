<?php
namespace Catalog\HondaEuropeBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogHondaEuropeBundle:Catalog';
    }

    public function model()
    {
        return $this->get('hondaeurope.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\HondaEuropeBundle\Components\HondaEuropeConstants';
    }
    

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);
       
        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
        
    }
} 