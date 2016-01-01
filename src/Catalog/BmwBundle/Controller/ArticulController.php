<?php
namespace Catalog\BmwBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Catalog\BmwBundle\Controller\Traits\BmwArticulFilters;
use Catalog\CommonBundle\Components\Factory;

class ArticulController extends BaseController{
use BmwArticulFilters;
    public function bundle()
    {
        return 'CatalogBmwBundle:Articul';
    }

    public function model()
    {
        return $this->get('bmw.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\BmwBundle\Components\BmwConstants';
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
    
       
    public function bmwArticulcomplectations1Action(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
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


    public function bmwArticulcomplectation_korobkaAction(Request $request)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        if ($request->isXmlHttpRequest()) {

            $role = $request->get('role');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'role' => $role
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

    public function bmwArticulcomplectation_yearAction(Request $request)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        if ($request->isXmlHttpRequest()) {

            $role = $request->get('role');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $korobka= $request->get('korobka');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'role' => $role,
                'korobka' => $korobka
            );



            $result = $this->model()->getArticulComplectationsYear($articul, $role, $modificationCode, $korobka);


            return $this->render($this->bundle().':03_complectation_year.html.twig', array(
                'result' => $result,
                'parameters' => $parameters
            ));
        }
    }

    public function bmwArticulcomplectation_monthAction(Request $request)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        if ($request->isXmlHttpRequest()) {

            $role = $request->get('role');
            $year = $request->get('year');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $korobka = $request->get('korobka');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'korobka' => $korobka
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



    public function bmwArticulGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
      
        $articulGroups = $this->model()->getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode);

        $this->addFilter('articulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode);
    }

    public function bmwArticulSubgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL); 
        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $this->addFilter('articulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
        
    }

    public function bmwArticulSchemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulSchemas = $this->model()->getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
		
		
        $this->addFilter('articulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
    }

    public function bmwArticulSchemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulPncs = $this->model()->getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);

        $this->addFilter('articulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);
    }
} 