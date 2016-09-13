<?php
namespace Catalog\BmwMotoBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Catalog\BmwMotoBundle\Controller\Traits\BmwMotoArticulFilters;
use Catalog\CommonBundle\Components\Factory;

class ArticulController extends BaseController{
use BmwMotoArticulFilters;
    public function bundle()
    {
        return 'CatalogBmwMotoBundle:Articul';
    }

    public function model()
    {
        return $this->get('bmwmoto.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\BmwMotoBundle\Components\BmwMotoConstants';
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {

        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        return $this->redirect(
            $this->generateUrl(
                str_replace('group', 'schemas', $this->get('request')->get('_route')),
                array_merge($parameters, array(
                        'groupCode' => $groupCode
                    )
                )
            ), 301
        );



    }

   /* public function modificationsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $articul = $request->cookies->get(Constants::ARTICUL);
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $token = $request->get('token');
            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'token' => $token
            );

            $modifications = $this->model()->getArticulModifications($articul, $regionCode, $modelCode);

            if(empty($modifications))
                return $this->error($request, 'Модификации не найдены.');

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
    }*/
    
       
    public function bmwmotoArticulcomplectations1Action(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $articul = null, $token = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        $complectations = $this->model()->getArticulRole($articul, $regionCode, $modelCode, $modificationCode);


        if(empty($complectations))
            return $this->error($request, 'Комплектации не найдены.');

        $oContainer = Factory::createContainer()
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode]
                ->setComplectations(Factory::createCollection($complectations, Factory::createComplectation())));
        unset($complectations);
        $this->filter($oContainer);


        return $this->render($this->bundle() . ':03_complectations.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }


    public function bmwmotoArticulcomplectation_korobkaAction(Request $request)
    {

        if ($request->isXmlHttpRequest()) {

            $articul = $request->get('articul');
            $role = $request->get('role');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $token = $request->get('token');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'role' => $role,
                'token' => $token
            );

            $regions = $this->model()->getRegions();
            $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
            $models = $this->model()->getModels($regionCode);
            $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
            $modifications = $this->model()->getModifications($regionCode, $modelCode);
            $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
            $complectations = $this->model()->getArticulComplectationsKorobka($articul, $role, $modificationCode);

            if(empty($complectations))
                return $this->error($request, 'Комплектации не найдены.');

            $oContainer = Factory::createContainer()
                ->setActiveRegion($regionsCollection[$regionCode])
                ->setActiveModel($modelsCollection[$modelCode])
                ->setActiveModification($modificationsCollection[$modificationCode]
                    ->setComplectations(Factory::createCollection($complectations, Factory::createComplectation())));
            unset($complectations);
            $this->filter($oContainer);




            return $this->render($this->bundle().':03_complectation_korobka.html.twig', array(
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }

    }

    public function bmwmotoArticulcomplectation_yearAction(Request $request)
    {

        if ($request->isXmlHttpRequest()) {

            $articul = $request->get('articul');
            $role = $request->get('role');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $korobka= $request->get('korobka');
            $token = $request->get('token');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'role' => $role,
                'korobka' => $korobka,
                'token' => $token
            );



            $result = $this->model()->getArticulComplectationsYear($articul, $role, $modificationCode, $korobka);


            return $this->render($this->bundle().':03_complectation_year.html.twig', array(
                'result' => $result,
                'parameters' => $parameters
            ));
        }
    }

    public function bmwmotoArticulcomplectation_monthAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $articul = $request->get('articul');
            $role = $request->get('role');
            $year = $request->get('year');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $korobka = $request->get('korobka');
            $token = $request->get('token');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'korobka' => $korobka,
                'token' => $token,
                'articul' => $articul
            );




            $regions = $this->model()->getRegions();
            $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
            $models = $this->model()->getModels($regionCode);
            $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
            $modifications = $this->model()->getModifications($regionCode, $modelCode);
            $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
            $complectations = $this->model()->getArticulComplectationsMonth($articul, $role, $modificationCode, $year, $korobka);

            if(empty($complectations))
                return $this->error($request, 'Комплектации не найдены.');

            $oContainer = Factory::createContainer()
                ->setActiveRegion($regionsCollection[$regionCode])
                ->setActiveModel($modelsCollection[$modelCode])
                ->setActiveModification($modificationsCollection[$modificationCode]
                    ->setComplectations(Factory::createCollection($complectations, Factory::createComplectation())));
            unset($complectations);
            $this->filter($oContainer);




            $result = $this->model()->getComplectationsMonth($role, $modificationCode, $year, $korobka);


            return $this->render($this->bundle().':03_complectation_month.html.twig', array(
                'result' => $result,
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }
    }



    public function bmwmotoArticulGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $articul = null, $token = null)
    {

        $articulGroups = $this->model()->getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode);

        $this->addFilter('articulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $articul, $token);
    }

    public function bmwmotoArticulSubgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $articul = null, $token = null)
    {
        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $this->addFilter('articulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $articul, $token);
        
    }

    public function bmwmotoArticulSchemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $articul = null, $token = null)
    {

        $articulSchemas = $this->model()->getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
		
		
        $this->addFilter('articulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $articul, $token);
    }

    public function bmwmotoArticulSchemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null, $articul = null, $token = null)
    {

        $articulPncs = $this->model()->getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);

        $this->addFilter('articulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $articul, $token);
    }
} 