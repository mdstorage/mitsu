<?php
namespace Catalog\MercedesBundle\Controller;

use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Catalog\MercedesBundle\Controller\Traits\CatalogFilters;
use Symfony\Component\HttpFoundation\Request;

class CatalogController extends BaseController{
    use CatalogFilters;
    public function bundle()
    {
        return 'CatalogMercedesBundle:Catalog';
    }

    public function model()
    {
        return $this->get('mercedes.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MercedesBundle\Components\MercedesConstants';
    }

    public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {
        $this->addFilter('catalogGroupsFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => $complectationCode
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode);
    }
} 