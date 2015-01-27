<?php
namespace Catalog\MercedesBundle\Controller;

use Assetic\Filter\GoogleClosure\BaseCompilerFilter;
use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Catalog\MercedesBundle\Controller\Traits\ArticulFilters;
use Catalog\MercedesBundle\Controller\Traits\CatalogFilters;
use Symfony\Component\HttpFoundation\Request;

class ArticulController extends BaseController{
    use ArticulFilters;
    use CatalogFilters;
    public function bundle()
    {
        return 'CatalogMercedesBundle:Articul';
    }

    public function model()
    {
        return $this->get('mercedes.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MercedesBundle\Components\MercedesConstants';
    }

    public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulGroups = $this->model()->getArticulGroups($articul, $complectationCode);

        $this->addFilter('articulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        $this->addFilter('catalogGroupsFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => $complectationCode
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode);
    }
} 