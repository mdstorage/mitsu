<?php
namespace Catalog\MazdaBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogMazdaBundle:Catalog';
    }

    public function model()
    {
        return $this->get('mazda.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MazdaBundle\Components\MazdaConstants';
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, null, $groupCode, $subGroupCode);
    }
} 