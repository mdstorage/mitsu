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

    public function bmwBillingAction(Request $request, $token = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());
        return $this->redirect($this->generateUrl('acme_billing_homepage', $parameters), 301);

    }

    public function regionsModelsAction(Request $request, $regionCode = null, $token = null)
    {

        /**
         * Выборка регионов из базы данных для конкретного артикула
         */
        $aRegions = $this->model()->getRegions();


        if(empty($aRegions)){
            return $this->error($request, 'Регионы не найдены.');
        } else {
            $oActiveRegion = Factory::createRegion();
            /**
             * Если регионы найдены, они помещаются в контейнер
             */
            $regionsCollection = Factory::createCollection($aRegions, $oActiveRegion);
            $oContainer = Factory::createContainer()
                ->setRegions($regionsCollection);
            unset($aRegions);
            /**
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

            /**
             * Выборка моделей из базы для данного артикула и региона
             */

            $models = $this->model()->getModels($oActiveRegion->getCode());

            if(empty($models)){
                return $this->error($request, 'Модели не найдены.');
            } else {
                $oActiveRegion->setModels(Factory::createCollection($models, Factory::createModel()));
            }

            $oContainer->setActiveRegion($oActiveRegion);

            $this->filter($oContainer);
        }

        return $this->render($this->bundle() . ':01_regions_models.html.twig', array(
            'oContainer' => $oContainer,
            'token' => $token

        ));
    }


    public function modificationsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $token = $request->get('token');
            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'token' => $token
            );

            $modifications = $this->model()->getModifications($regionCode, $modelCode);

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
    }

    public function complectations1Action(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $token = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());var_dump($parameters); die;

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

    public function complectation_korobkaAction(Request $request)
    {
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
            $complectations = $this->model()->getComplectationsKorobka($role, $modificationCode);

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

    public function complectation_yearAction(Request $request)
    {
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



            $result = $this->model()->getComplectationsYear($role, $modificationCode, $korobka);


            return $this->render($this->bundle().':03_complectation_year.html.twig', array(
                'result' => $result,
                'parameters' => $parameters
            ));
        }
    }

    public function complectation_monthAction(Request $request)
    {
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
            $complectations = $this->model()->getComplectationsMonth($role, $modificationCode, $year, $korobka);

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
} 