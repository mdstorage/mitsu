<?php
namespace Catalog\ToyotaBundle\Controller;

use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Catalog\ToyotaBundle\Controller\Traits\ToyotaArticulFilters;
use Catalog\ToyotaBundle\Form\ComplectationType;

class ArticulController extends BaseController
{

    use ToyotaArticulFilters;

    public function bundle()
    {
        return 'CatalogToyotaBundle:Articul';
    }

    public function model()
    {
        return $this->get('toyota.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\ToyotaBundle\Components\ToyotaConstants';
    }


    public function index_tokenAction($error_message = null, $token = null)
    {
        setcookie(Constants::ARTICUL_TOKEN, '');
        return $this->render($this->bundle().':01_index.html.twig', array('error_message' => $error_message));
    }

    public function findByArticulTokenAction(Request $request, $token = null, $regionCode = null)
    {
        if (!$articul = $request->cookies->get(Constants::ARTICUL_TOKEN)) {
            if ($articul = trim($request->get('articul'))) {
                setcookie(Constants::ARTICUL_TOKEN, $articul);
            } else {
                return $this->indexAction('Запчасть с таким артикулом не найдена.', $token);
            }
        }
        if (strlen($articul)<7)
        {
            return $this->indexAction('Запчасть с таким артикулом не найдена.', $token);
        }
        $articulRegions = $this->model()->getArticulRegions($articul);

        if (empty($articulRegions)) {
            setcookie(Constants::ARTICUL_TOKEN, '');
            return $this->indexAction('Запчасть с таким артикулом не найдена.', $token);
        }

        if (is_null($regionCode)){
            $regionCode = $articulRegions[0];
        }

        $articulModels  = $this->model()->getArticulModels($articul, $regionCode);

        $this->addFilter('articulRegionModelsFilter', array(
            'articulRegions' => $articulRegions,
            'articulModels'  => $articulModels,
            'regionCode' => $regionCode
        ));

        return $this->regionsModelsAction($request, $regionCode, $token);
    }

    public function modificationsTokenAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $articul = $request->cookies->get(Constants::ARTICUL_TOKEN);
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $articulModifications = $this->model()->getArticulModifications($articul, $regionCode, $modelCode);

            $this->addFilter('articulModificationsFilter', array(
                'articulModifications' => $articulModifications
            ));

            return parent::modificationsAction($request);
        }
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode);
    }
    
       
    public function toyotaArticulComplectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $token = null)
    {
        $data = $this->get('my_token_info')->getStatus($token);

        if(empty($data) & !empty($token)){
            return $this->errorBilling('Сервис не оплачен');
        }

        $articul = $request->cookies->get(Constants::ARTICUL);


        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $regions = $this->model()->getRegions();
        $regionsCollection = Factory::createCollection($regions, Factory::createRegion())->getCollection();
        $models = $this->model()->getModels($regionCode);
        $modelsCollection = Factory::createCollection($models, Factory::createModel())->getCollection();
        $modifications = $this->model()->getModifications($regionCode, $modelCode);
        $modificationsCollection = Factory::createCollection($modifications, Factory::createModification())->getCollection();
        $complectations = $this->model()->getComplectations1($regionCode, $modelCode, $modificationCode);


        $articulComplectations = $this->model()->getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode);

        $this->addFilter('articulComplectationsFilter', array(
            'articulComplectations' => $articulComplectations
        ));



        $complectationsForForm = $this->model()->getComplectationsForForm(array_intersect_key($complectations, array_flip($articulComplectations)));

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

    public function toyotaArticulGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $token = null)
    {
        $data = $this->get('my_token_info')->getStatus($token);

        if(empty($data) & !empty($token)){
            return $this->errorBilling('Сервис не оплачен');
        }

        $articul = $request->cookies->get(Constants::ARTICUL);


        $a = array();
        $aForm = array();
        $a = $request->get('ComplectationType');
        unset($a['_token']);

        foreach ($a as $index => $value)
        {
            $aForm[substr($index, strpos($index, 'f'), strlen($index))] = $value;
        }

        $complectationCode = $this->model()->getComplectationCodeFromFormaData($aForm, $regionCode, $modificationCode);

        if(empty($complectationCode))
            return $this->error($request, 'Комплектация не найдена.');
        $parameters['complectationCode'] = $complectationCode;


        $articulGroups = $this->model()->getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode);


        $this->addFilter('articulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $token);
    }

    public function toyotaArticulSubgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $token = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL); 
        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $this->addFilter('articulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $token);
        
    }

    public function toyotaArticulSchemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $token = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulSchemas = $this->model()->getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);


        $this->addFilter('articulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $token);
    }

    public function toyotaArticulSchemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null, $token = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulPncs = $this->model()->getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);

        $this->addFilter('articulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $token);
    }
    public function toyotaArticularticulsAction(Request $request)
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