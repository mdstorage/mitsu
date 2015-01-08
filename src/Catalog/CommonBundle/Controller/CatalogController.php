<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class CatalogController extends BaseController{

    abstract function bundle();

    abstract function model();

    abstract function bundleConstants();

    public function __construct()
    {
        Factory::setConstants($this->bundleConstants());
    }

    public function regionsModelsAction($regionCode)
    {
        /*
         * Выборка регионов из базы данных для конкретного артикула
         */
        $aRegions = $this->model()->getRegions();

        if(empty($aRegions)){
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Регионы не найдены.'));
        } else {
            $oActiveRegion = Factory::createRegion();
            /*
             * Если регионы найдены, они помещаются в контейнер
             */
            $oContainer = Factory::createContainer()
                ->setRegions(Factory::createCollection($aRegions, $oActiveRegion));

            /*
             * Если пользователь задал регион, то этот регион становится активным
             */
            if (!is_null($regionCode)){
                $oActiveRegion->setCode($regionCode);
            } else{
                /*
                 * Если пользователь не задавал регион, то в качестве активного выбирается первый из списка регионов объект
                 */
                $regions = $oContainer->getRegions();
                $oActiveRegion->setCode(reset($regions)->getCode());
            }

            $oActiveRegion->setName($oActiveRegion->getCode());
            /*
             * Выборка моделей из базы для данного артикула и региона
             */

            $models = $this->model()->getModels($oActiveRegion->getCode());

            if(empty($models)){
                return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Модели не найдены.'));
            } else {
                $oActiveRegion->setModels(Factory::createCollection($models, Factory::createModel()));
            }

            $oContainer->setActiveRegion($oActiveRegion);
        }

        return $this->render($this->bundle() . ':Catalog:01_regions_models.html.twig', array(
            'oContainer' => $oContainer
        ));
    }

    public function modificationsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode
            );

            $modifications = $this->model()->getModifications($regionCode, $modelCode);

            if(empty($modifications))
                return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Модификации не найдены.'));

            $oContainer = Factory::createContainer()
                ->setActiveModel(Factory::createModel($modelCode)
                    ->setModifications(Factory::createCollection($modifications, Factory::createModification())
                    )
                );

            return $this->render($this->bundle() . ':Catalog:02_modifications.html.twig', array(
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }
    }

    public function complectationsAction($regionCode, $modelCode, $modificationCode)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $complectations = $this->model()->getComplectations($regionCode, $modelCode, $modificationCode);

        if(empty($complectations))
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Комплектации не найдены.'));

        $oContainer = Factory::createContainer()
            ->setActiveRegion(Factory::createRegion($regionCode, $regionCode))
            ->setActiveModel(Factory::createModel($modelCode, $modelCode))
            ->setActiveModification(Factory::createModification($modificationCode, $modificationCode)
                ->setComplectations(Factory::createCollection($complectations, Factory::createComplectation())));

        return $this->render($this->bundle() . ':Catalog:03_complectations.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function groupsAction($regionCode, $modelCode, $modificationCode)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode);

        if(empty($groups))
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Группы не найдены.'));

        $oContainer = Factory::createContainer()
            ->setActiveRegion(Factory::createRegion($regionCode, $regionCode))
            ->setActiveModel(Factory::createModel($modelCode, $modelCode))
            ->setActiveModification(Factory::createModification($modificationCode, $modificationCode))
            ->setGroups(Factory::createCollection($groups, Factory::createGroup()));

        return $this->render($this->bundle() . ':Catalog:04_groups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function subgroupsAction($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groupSchemas = $this->model()->getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode);
        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode);
        $subgroups = $this->model()->getSubgroups($regionCode, $modelCode, $modificationCode, $groupCode);

        $schemas = Factory::createCollection($groupSchemas, Factory::createSchema())->getCollection();

        if(empty($subgroups))
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Подгруппы не найдены.'));

        $oContainer = Factory::createContainer()
            ->setActiveRegion(Factory::createRegion($regionCode, $regionCode))
            ->setActiveModel(Factory::createModel($modelCode, $modelCode))
            ->setActiveModification(Factory::createModification($modificationCode, $modificationCode))
            ->setActiveSchema(reset($schemas)?:Factory::createSchema())
            ->setActiveGroup(Factory::createGroup($groupCode, $groups[$groupCode][Constants::NAME], $groups[$groupCode][Constants::OPTIONS])
                ->setSubGroups(Factory::createCollection($subgroups, Factory::createGroup()))
            );

        return $this->render($this->bundle() . ':Catalog:05_subgroups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function schemasAction($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $subgroups = $this->model()->getSubgroups($regionCode, $modelCode, $modificationCode, $groupCode);
        $schemas = $this->model()->getSchemas($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode);

        if(empty($schemas))
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Схемы не найдены.'));

        $oContainer = Factory::createContainer()
            ->setActiveRegion(Factory::createRegion($regionCode, $regionCode))
            ->setActiveModel(Factory::createModel($modelCode, $modelCode))
            ->setActiveModification(Factory::createModification($modificationCode, $modificationCode))
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($subgroups, Factory::createGroup())))
            ->setSchemas(Factory::createCollection($schemas, Factory::createSchema()));

        return $this->render($this->bundle() . ':Catalog:06_schemas.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function schemaAction($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $subgroups = $this->model()->getSubgroups($regionCode, $modelCode, $modificationCode, $groupCode);

        $schema = $this->model()->getSchema($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode);
        $schemaCollection = Factory::createCollection($schema, Factory::createSchema())->getCollection();
        $oActiveSchema = $schemaCollection[$schemaCode];

        $pncs = $this->model()->getPncs($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $oActiveSchema->getOption(Constants::CD));
        $commonArticuls = $this->model()->getCommonArticuls($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $oActiveSchema->getOption(Constants::CD));
        $refGroups = $this->model()->getReferGroups($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $oActiveSchema->getOption(Constants::CD));

        $oActiveSchema
            ->setPncs(Factory::createCollection($pncs, Factory::createPnc()))
            ->setCommonArticuls(Factory::createCollection($commonArticuls, Factory::createArticul()))
            ->setRefGroups(Factory::createCollection($refGroups, Factory::createGroup()));

        $oContainer = Factory::createContainer()
            ->setActiveRegion(Factory::createRegion($regionCode, $regionCode))
            ->setActiveModel(Factory::createModel($modelCode, $modelCode))
            ->setActiveModification(Factory::createModification($modificationCode, $modificationCode))
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($subgroups, Factory::createGroup())))
            ->setActiveSchema($oActiveSchema);

        return $this->render($this->bundle() . ':Catalog:07_schema.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $regionCode = $request->get('regionCode');
            $modificationCode = $request->get('modificationCode');
            $cd = $request->get('cd');
            $subGroupCode = $request->get('subGroupCode');
            $pncCode = $request->get('pncCode');

            $parameters = array(
                'regionCode' => $regionCode,
                'modificationCode' => $modificationCode,
                'cd' => $cd,
                'subGroupCode' => $subGroupCode,
                'pncCode' => $pncCode
            );

            $articuls = $this->model()->getArticuls($regionCode, $cd, $modificationCode, $subGroupCode, $pncCode);
            $oContainer = Factory::createContainer()
                ->setActivePnc(Factory::createPnc($pncCode, $pncCode)
                    ->setArticuls(Factory::createCollection($articuls, Factory::createArticul()))
                );

            return $this->render($this->bundle() . ':Catalog:08_articuls.html.twig', array(
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }
    }
} 