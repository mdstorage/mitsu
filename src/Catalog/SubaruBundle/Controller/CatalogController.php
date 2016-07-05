<?php
namespace Catalog\SubaruBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Catalog\SubaruBundle\Form\ComplectationType;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogSubaruBundle:Catalog';
    }

    public function model()
    {
        return $this->get('subaru.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SubaruBundle\Components\SubaruConstants';
    }
    

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $subGroupCode);

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



        $complectationsForForm = $this->model()->getComplectationsForForm($complectations);

        $form = $this->createForm(new ComplectationType(), $complectationsForForm);


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


    public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {

        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $a = array();
        $aForm = array();
        $a = $request->get('ComplectationType');
        unset($a['_token']);

        foreach ($a as $index => $value)
        {
            $aForm[substr($index, strpos($index, 'p'), strlen($index))] = $value;
        }

        $complectationCode = $this->model()->getComplectationCodeFromFormaData($aForm, $regionCode, $modelCode);




        if(empty($complectationCode))
            return $this->error($request, 'Комплектация не найдена.');
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
            $complectations = $this->model()->getComplectations($regionCode, $modelCode, $modificationCode);
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
} 