<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class CatalogController extends BaseController{

    public function regionsModelsAction(Request $request, $regionCode = null)
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
            $regionsCollection = Factory::createCollection($aRegions, $oActiveRegion);
            $oContainer = Factory::createContainer()
                ->setRegions($regionsCollection);

            /*
             * Если пользователь задал регион, то этот регион становится активным
             */
            $regionsList = $regionsCollection->getCollection();
            if (!is_null($regionCode)){
                $oActiveRegion = $regionsList[$regionCode];
            } else{
                /*
                 * Если пользователь не задавал регион, то в качестве активного выбирается первый из списка регионов объект
                 */
                $oActiveRegion = reset($regionsList);
            }
            
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

            $this->filter($oContainer);
        }

        return $this->render($this->bundle() . ':01_regions_models.html.twig', array(
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

            $this->filter($oContainer);

            return $this->render($this->bundle() . ':02_modifications.html.twig', array(
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }
    }

    public function complectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        $complectations = $this->model()->getComplectations($regionCode, $modelCode, $modificationCode);

        if(empty($complectations))
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Комплектации не найдены.'));

        $oContainer = Factory::createContainer()
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode]
                ->setComplectations(Factory::createCollection($complectations, Factory::createComplectation())));

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':03_complectations.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);

        if(empty($groups))
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Группы не найдены.'));
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
            ->setGroups(Factory::createCollection($groups, Factory::createGroup()));

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':04_groups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function subgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());
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
        $groupSchemas = $this->model()->getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode);
        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();
        $subgroups = $this->model()->getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $schemas = Factory::createCollection($groupSchemas, Factory::createSchema())->getCollection();

        if(empty($subgroups))
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Подгруппы не найдены.'));

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveSchema(reset($schemas)?:Factory::createSchema())
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($subgroups, Factory::createGroup()))
            );

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':05_subgroups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function schemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
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

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':06_schemas.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function schemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
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

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':07_schema.html.twig', array(
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

            $this->filter($oContainer);

            return $this->render($this->bundle() . ':08_articuls.html.twig', array(
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }
    }
} 