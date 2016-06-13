<?php
namespace Catalog\ToyotaBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Catalog\ToyotaBundle\Form\ComplectationType;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogToyotaBundle:Catalog';
    }

    public function model()
    {
        return $this->get('toyota.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\ToyotaBundle\Components\ToyotaConstants';
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
        $complectations = $this->model()->getComplectations1($regionCode, $modelCode, $modificationCode);

        $form = $this->createForm(new ComplectationType(), $complectations);


        if(empty($complectations))
            return $this->error($request, 'Комплектации не найдены.');

        $oContainer = Factory::createContainer()
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode]
                ->setComplectations(Factory::createCollection($complectations, Factory::createComplectation())));
        unset($complectations);
        $this->filter($oContainer);

        $complectationsKeys = array_keys($oContainer->getActiveModification()->getComplectations());
        if (1 == count($complectationsKeys)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('complectations', 'groups', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'complectationCode' => $complectationsKeys[0]
                        )
                    )
                ), 301
            );
        };

        return $this->render($this->bundle() . ':03_complectations.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters,
            'form' => $form->createView()
        ));
    }


    public function complectation_korobkaAction(Request $request)
    {

        if ($request->isXmlHttpRequest()) {


            $priznak = $request->get('priznak_agregata');
            $engine = $request->get('engine');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = urldecode($request->get('modelCode'));

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'priznak' => $priznak,
                'engine' => $engine
            );


            $regions = $this->model()->getRegions();
            $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
            $models = $this->model()->getModels($regionCode);
            $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
            $modifications = $this->model()->getModifications($regionCode, $modelCode);
            $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
            $complectations = $this->model()->getComplectationsKorobka($regionCode, $modelCode, $modificationCode, $priznak, $engine);


            return new Response(json_encode($complectations));
        }
    }

    

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {


        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode);
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

} 