<?php
namespace Catalog\HyundaiBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Catalog\HyundaiBundle\Components\HyundaiConstants;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogHyundaiBundle:Catalog';
    }

    public function model()
    {
        return $this->get('hyundai.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\HyundaiBundle\Components\HyundaiConstants';
    }



    public function complectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $articul = null, $token = null)
    {
        $data = $this->get('my_token_info')->getStatus($token);

        if(empty($data) & !empty($token)){
            return $this->errorBilling('Сервис не оплачен');
        }

        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        $complectations = $this->model()->getComplectations($regionCode, $modelCode, $modificationCode);

        $complectationsForForm = $this->model()->getComplectationsForForm($complectations);

        $form = $this->createForm($this->get('hyundai.form'), $complectationsForForm);


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
            'parameters' => $parameters,
            'form' => $form->createView()
        ));
    }

    public function complectationFormValidValueAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $priznakAllSelect = $request->get('priznakAllSelect');
            $selectorD = $request->get('selectorD');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = urldecode($request->get('modelCode'));
            $token = $request->get('token');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'token' => $token
            );
            $regions = $this->model()->getRegions();
            $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
            $models = $this->model()->getModels($regionCode);
            $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
            $modifications = $this->model()->getModifications($regionCode, $modelCode);
            $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
            $complectations = $this->model()->getComplectationsFormValidValue($regionCode, $modelCode, $modificationCode, $selectorD, $priznakAllSelect);

            return new Response(json_encode($complectations));
        }
    }
    public function subgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $articul = null, $token = null)
    {
        $data = $this->get('my_token_info')->getStatus($token);

        if(empty($data) & !empty($token)){
            return $this->errorBilling('Сервис не оплачен');
        }

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


        if(empty($subgroups))
            return $this->error($request, 'Подгруппы не найдены.');

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setSchemas(Factory::createCollection($groupSchemas, Factory::createSchema()))
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($subgroups, Factory::createGroup()))
            );

        if (($filterResult = $this->filter($oContainer)) instanceof RedirectResponse) {
            return $filterResult;
        };

        $subgroupsKeys = array_keys($oContainer->getActiveGroup()->getSubgroups());
        if (1 == count($subgroupsKeys)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('subgroups', 'schemas', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'subGroupCode' => $subgroupsKeys[0]
                        )
                    )
                ), 301
            );
        };

        return $this->render($this->bundle() . ':05_subgroups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }


    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode, $token = null)
    {
        $groupSubgroupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);
        $groupCode = $groupSubgroupCode['major_sect'];
        $subGroupCode = $groupSubgroupCode['minor_sect'];
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());
        $parameters['subGroupCode'] = $subGroupCode;

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
} 