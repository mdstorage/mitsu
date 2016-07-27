<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 06.03.15
 * Time: 11:20
 */

namespace Catalog\SmartBundle\Controller\Traits;

use Symfony\Component\HttpFoundation\Request;
use Catalog\CommonBundle\Components\Factory;

trait CommonControllerTrait {
    public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $articul = null, $token = null)
    {
        $this->addFilter('catalogGroupsFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => $complectationCode,
            'token' => $token
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $articul, $token);
    }

    public function subGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $articul = null, $token = null)
    {
        $this->addFilter('catalogSubGroupsFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => $complectationCode,
            'groupCode' => $groupCode,
            'articul' => $articul,
            'token' => $token
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $articul, $token);
    }

    public function saFirstLevelSubgroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $sanum, $articul = null, $token = null)
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

        if ($this->filter($oContainer) instanceof RedirectResponse) {
            return $this->filter($oContainer);
        };

        $saSubGroupsCodes = array_keys($oContainer->getGroups());
        if (1 == count($saSubGroupsCodes)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('subgroups', 'schemas', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'schemaCode' => $saSubGroupsCodes[0]
                        )
                    )
                ), 301
            );
        };

        return $this->render($this->bundle() . ':051_sa1subgroups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function saSchemasAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $sanum, $articul = null, $token = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $saSubGroups = $this->model()->getSaSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

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

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($saSubGroups, Factory::createGroup())))
            ->setSchemas(Factory::createCollection($schemas, Factory::createSchema()));

        if ($this->filter($oContainer) instanceof RedirectResponse) {
            return $this->filter($oContainer);
        };

        $saSchemaCodes = array_keys($oContainer->getSchemas());
        if (1 == count($saSchemaCodes)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('schemas', 'schema', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'schemaCode' => $saSchemaCodes[0]
                        )
                    )
                ), 301
            );
        };

        return $this->render($this->bundle() . ':061_schemas.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function saSchemaAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $sanum, $schemaCode, $articul = null, $token = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());
        $parameters['subGroupCode'] = $sanum;

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

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $saSubGroups = $this->model()->getSaSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $schemas = $this->model()->getSaSchemas($sanum);
        $schemaCollection = Factory::createCollection($schemas, Factory::createSchema())->getCollection();
        $oActiveSchema = $schemaCollection[$schemaCode];

        $pncs = $this->model()->getSaPncs($sanum, $schemaCode);
        $commonArticuls = $this->model()->getSaCommonArticuls($sanum);

        $oActiveSchema
            ->setPncs(Factory::createCollection($pncs, Factory::createPnc()))
            ->setCommonArticuls(Factory::createCollection($commonArticuls, Factory::createArticul()));

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($saSubGroups, Factory::createGroup())))
            ->setActiveSchema($oActiveSchema);

        $this->filter($oContainer);
        if (!empty($token))
        {
            $aDataToken = array();
            $aDataToken = $this->get('my_token_info')->getDataToken($token);

            $redirectAdress = $aDataToken['url'];
        }
        else
        {
            $redirectAdress = Constants::FIND_PATH;
        }

        $parameters = array_merge($parameters, array(
            'redirectAdress' => $redirectAdress
        ));

        return $this->render($this->bundle() . ':071_schema.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function saArticulsAction(Request $request)
    {
        $pncCode = $request->get('pncCode');
        $sanum = $request->get('sanum');
        $articul = $request->get('articul');
        $token = $request->get('token');


        if (!empty($token))
        {
            $aDataToken = array();
            $aDataToken = $this->get('my_token_info')->getDataToken($token);

            $redirectAdress = $aDataToken['url'];
        }
        else
        {
            $redirectAdress = Constants::FIND_PATH;
        }

        $articuls = $this->model()->getSaArticuls($sanum, $pncCode);

        $oContainer = Factory::createContainer()
            ->setActivePnc(Factory::createPnc($pncCode, $pncCode)
                    ->setArticuls(Factory::createCollection($articuls, Factory::createArticul()))
            );

        $this->filter($oContainer);

        $parameters = array(
            'articul' => $articul,
            'redirectAdress' => $redirectAdress
        );

        return $this->render($this->bundle() . ':08_articuls.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }
} 