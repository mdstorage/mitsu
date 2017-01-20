<?php
namespace Catalog\KiaBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Catalog\KiaBundle\Components\KiaConstants;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogKiaBundle:Catalog';
    }

    public function model()
    {
        return $this->get('kia.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\KiaBundle\Components\KiaConstants';
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
        $complectations = $this->model()->getComplectations1($regionCode, $modelCode, $modificationCode);

        $complectationsForForm = $this->model()->getComplectationsForForm($complectations);

        $form = $this->createForm($this->get('kia.form'), $complectationsForForm);


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

    public function complectation_korobkaAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $selectorD = $request->get('selectorD');
            $select_name = $request->get('select_name');
            $select_value = $request->get('select_value');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = urldecode($request->get('modelCode'));
            $token = $request->get('token');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'priznak' => $select_name,
                'engine' => $select_value,
                'token' => $token
            );
            $regions = $this->model()->getRegions();
            $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
            $models = $this->model()->getModels($regionCode);
            $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
            $modifications = $this->model()->getModifications($regionCode, $modelCode);
            $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
            $complectations = $this->model()->getComplectationsKorobka($regionCode, $modelCode, $modificationCode, $select_name, $select_value, $selectorD);

            return new Response(json_encode($complectations));
        }
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode, $token = null)
    {
        $subGroupCode = (substr_count($subGroupCode, '-') > 1) ? substr($subGroupCode, 0, strripos($subGroupCode, '-')+1): $subGroupCode;
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);
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