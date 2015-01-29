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

    public function mercedesArticulGroupsAction(
        Request $request,
        $regionCode = null,
        $modelCode = null,
        $modificationCode = null,
        $complectationCode = null)
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

        $articulModifications = $this->model()->getArticulModifications($articul, $regionCode, $modelCode);
        $this->addFilter('articulModificationsFilter', array(
            'articulModifications' => $articulModifications
        ));

        $articulComplectations = $this->model()->getArticulComplectations($articul);
        $this->addFilter('articulAggregatesFilter', array(
            'articulComplectations' => $articulComplectations
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode);
    }

    public function mercedesArticulSubgroupsAction(
        Request $request,
        $regionCode = null,
        $modelCode = null,
        $modificationCode = null,
        $complectationCode = null,
        $groupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $complectationCode, $groupCode);

        $this->addFilter('aticulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
    }

    public function mercedesArticulSchemasAction(
        Request $request,
        $regionCode = null,
        $modelCode = null,
        $modificationCode = null,
        $complectationCode = null,
        $groupCode = null,
        $subGroupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulSchemas = $this->model()->getArticulSchemas($articul, $complectationCode, $groupCode, $subGroupCode);

        $this->addFilter('aticulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
    }

    public function mercedesArticulSchemaAction(
        Request $request,
        $regionCode = null,
        $modelCode = null,
        $modificationCode = null,
        $complectationCode = null,
        $groupCode = null,
        $subGroupCode = null,
        $schemaCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulPncs = $this->model()->getArticulPncs($articul, $complectationCode, $groupCode, $subGroupCode);

        $this->addFilter('aticulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);
    }
} 