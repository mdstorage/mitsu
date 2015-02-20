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

        $articulComplectations = $this->model()->getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode);
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

        $this->addFilter('articulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        $this->addFilter('catalogSubGroupsFilter', array(
            'regionCode'        => $regionCode,
            'modelCode'         => $modelCode,
            'modificationCode'  => $modificationCode,
            'complectationCode' => $complectationCode,
            'groupCode'         => $groupCode
        ));

        $sanums = $this->model()->getArticulSanums($articul);

        $this->addFilter('articulSaSubgroupFilter', array(
            'sanums' => $sanums
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

        $this->addFilter('articulSchemasFilter', array(
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

        $this->addFilter('articulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);
    }

    public function saFirstLevelSubgroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $sanum)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $saFLSubGroups = $this->model()->getSaFirstLevelSubgroups($complectationCode, $sanum);

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        if(empty($groups))
            return $this->error($request, 'Группы не найдены.');

        $oContainer = Factory::createContainer();
        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        if ($complectationCode) {
            $complectations = $this->model()->getComplectations($regionCode, $modelCode, $modificationCode);
            $complectationsCollection = Factory::createCollection($complectations, Factory::createComplectation())->getCollection();
            $oContainer->setActiveComplectation($complectationsCollection[$complectationCode]);
        }

        $saSubGroups = $this->model()->getSaSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($saSubGroups, Factory::createGroup())))
            ->setGroups(Factory::createCollection($saFLSubGroups, Factory::createGroup()));

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':051_sa1subgroups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function saSchemasAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $sanum)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());
        $articul = $request->cookies->get(Constants::ARTICUL);
        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $saSubGroups = $this->model()->getSaSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $schemas = $this->model()->getArticulSaSchemas($articul, $sanum);

        if(empty($schemas))
            return $this->error($request, 'Схемы не найдены.');

        $oContainer = Factory::createContainer();
        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        if ($complectationCode) {
            $complectations = $this->model()->getComplectations($regionCode, $modelCode, $modificationCode);
            $complectationsCollection = Factory::createCollection($complectations, Factory::createComplectation())->getCollection();
            $oContainer->setActiveComplectation($complectationsCollection[$complectationCode]);
        }

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($saSubGroups, Factory::createGroup())))
            ->setSchemas(Factory::createCollection($schemas, Factory::createSchema()));

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':061_schemas.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

} 