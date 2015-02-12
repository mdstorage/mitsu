<?php
namespace Catalog\MercedesBundle\Controller;

use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Catalog\MercedesBundle\Controller\Traits\CatalogFilters;
use Symfony\Component\HttpFoundation\Request;
use Catalog\CommonBundle\Components\Factory;

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

    public function subGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null)
    {
        $this->addFilter('catalogSubGroupsFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => $complectationCode,
            'groupCode' => $groupCode
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
    }

    public function saFirstLevelSubgroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $sanum)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getSaFirstLevelSubgroups($complectationCode, $sanum);

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
        $saSubGroupsCollection = Factory::createCollection($saSubGroups, Factory::createGroup())->getCollection();

        //var_dump($saSubGroups);die;

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveGroup($saSubGroupsCollection[$sanum])
            ->setGroups(Factory::createCollection($groups, Factory::createGroup()));

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':051_sa1subgroups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function saSchemasAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $sanum)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getSaFirstLevelSubgroups($complectationCode, $sanum);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $schemas = $this->model()->getSaSchemas($sanum);

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
        var_dump($schemas);die;
        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveGroup($groupsCollection[$sanum])
            ->setSchemas(Factory::createCollection($schemas, Factory::createSchema()));

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':06_schemas.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }
} 