<?php
namespace Catalog\BmwBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogBmwBundle:Catalog';
    }

    public function model()
    {
        return $this->get('bmw.catalog.model');
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

    public function complectations1Action(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        $complectations = $this->model()->getRole($regionCode, $modelCode, $modificationCode);


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

    public function complectation_dataAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $role = $request->get('role');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode
            );



            $result = $this->model()->getComplectationsData($role, $modificationCode);

            return $this->render($this->bundle().':03_complectations_data.html.twig', array(
                'result' => $result,
                'parameters' => $parameters
            ));
        }
    }

    public function complectation_catalog_dataAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $role = $request->get('role');
            $year = $request->get('year');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode
            );






            $regions = $this->model()->getRegions();
            $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
            $models = $this->model()->getModels($regionCode);
            $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
            $modifications = $this->model()->getModifications($regionCode, $modelCode);
            $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
            $complectations = $this->model()->getComplectationsCatalogData($role, $modificationCode, $year);

            if(empty($complectations))
                return $this->error($request, 'Комплектации не найдены.');

            $oContainer = Factory::createContainer()
                ->setActiveRegion($regionsCollection[$regionCode])
                ->setActiveModel($modelsCollection[$modelCode])
                ->setActiveModification($modificationsCollection[$modificationCode]
                    ->setComplectations(Factory::createCollection($complectations, Factory::createComplectation())));
            unset($complectations);
            $this->filter($oContainer);




            $result = $this->model()->getComplectationsCatalogData($role, $modificationCode, $year);


            return $this->render($this->bundle().':03_complectation_catalog_data.html.twig', array(
                'result' => $result,
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }
    }
} 