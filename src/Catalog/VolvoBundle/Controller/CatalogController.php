<?php
namespace Catalog\VolvoBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Catalog\VolvoBundle\Form\ComplectationType;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogVolvoBundle:Catalog';
    }

    public function model()
    {
        return $this->get('volvo.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\VolvoBundle\Components\VolvoConstants';
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

    public function complectations1Action(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null)
    {
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        $complectations = $this->model()->getEngine($regionCode, $modelCode, $modificationCode);



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

            $engine = $request->get('engine');
            $modificationCode = $request->get('modificationCode');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'engine' => $engine
            );


            $regions = $this->model()->getRegions();
            $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
            $models = $this->model()->getModels($regionCode);
            $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
            $modifications = $this->model()->getModifications($regionCode, $modelCode);
            $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
            $complectations = $this->model()->getComplectationsKorobka($regionCode, $modelCode, $modificationCode, $engine);

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




            return $this->render($this->bundle().':03_complectation_korobka.html.twig', array(
                'oContainer' => $oContainer,
                'parameters' => $parameters,
                'form' => $form->createView()
            ));
        }
    }

    public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {
        /*   $complectations = $this->model()->getComplectations($regionCode, $modelCode, $modificationCode);


          $form = $this->createForm(new ComplectationType(), $complectations);
           $form->handleRequest($this->get('request'));
                $username = $form->get('title')->getData();
                     $Positive_Territories = $form->get('title')->getData();*/






        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $engine = array('titleEN', $parameters['complectationCode']);

        $complectationCode = base64_decode(base64_encode(implode('|',(array_merge($request->get('ComplectationType'), $engine)))));

        var_dump($complectationCode); die;

        $parameters['complectationCode'] = $complectationCode;

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);

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
            $complectations = $this->model()->getComplectation($complectationCode);
            $complectationsCollection = Factory::createCollection($complectations, Factory::createComplectation())->getCollection();
            $oContainer->setActiveComplectation($complectationsCollection[$complectationCode]);
        }

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setGroups(Factory::createCollection($groups, Factory::createGroup()));

        if (($filterResult = $this->filter($oContainer)) instanceof RedirectResponse) {
            return $filterResult;
        };

        $groupsKeys = array_keys($oContainer->getGroups());
        if (1 == count($groupsKeys)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('groups', 'subgroups', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'groupCode' => $groupsKeys[0]
                        )
                    )
                ), 301
            );
        };

        return $this->render($this->bundle() . ':04_groups.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
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
    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $modificationCode = $request->get('modificationCode');
            $complectationCode = $request->get('complectationCode');
            $groupCode = $request->get('groupCode');
            $subGroupCode = $request->get('subGroupCode');
            $schemaCode = $request->get('schemaCode');
            $pncCode = $request->get('pncCode');
            $options = $request->get('options');

            $parameters = array(
                'regionCode' => $regionCode,
                'modificationCode' => $modificationCode,
                'options' => json_decode($options, true),
                'subGroupCode' => $subGroupCode,
                'pncCode' => $pncCode
            );

            $articuls = $this->model()->getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $pncCode, json_decode($options, true));

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