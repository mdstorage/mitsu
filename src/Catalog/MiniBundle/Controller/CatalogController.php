<?php
namespace Catalog\MiniBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogMiniBundle:Catalog';
    }

    public function model()
    {
        return $this->get('mini.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\MiniBundle\Components\MiniConstants';
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


    public function regionsModelsAction(Request $request, $regionCode = null, $token = null)
    {
        foreach($request->cookies->keys() as $index => $value)
        {
            if (stripos($value, Constants::COOKIEHOST) !== false)
            {
                setcookie($value, "");
            }
        }

        $data = $this->get('my_token_info')->getStatus($token);

        if(empty($data) & !empty($token)){
            return $this->errorBilling('Сервис не оплачен');
        }

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

            $brandSlash = $request->server->get('REQUEST_URI');


            $brand = explode('/', $brandSlash)[1];


            $callbackhost = trim($request->get('callbackhost'));
            $domain = trim($request->get('domain'));
            $domain = substr_replace($domain, '', strpos($domain, '.'), 1);


            $headers = $request->server->getHeaders();

            if (stripos($headers['REFERER'], 'callbackhost='))
            {
                if (!$call = $request->cookies->get(Constants::COOKIEHOST.$brand.urlencode($domain)))
                {
                    if ($callbackhost){
                        setcookie(Constants::COOKIEHOST.$brand.urlencode($domain), $callbackhost);
                    }
                }
            }
            else{
                setcookie(Constants::COOKIEHOST.$brand.urlencode($domain), "");
            }

            if (stripos($headers['REFERER'], 'domain'))
            {
                if (!$call = $request->cookies->get('DOMAIN'))
                {
                    if ($domain){
                        setcookie('DOMAIN', $domain);
                    }

                }
            }
            else {
                setcookie('DOMAIN', "");
            }

            $articul = $request->get('articul');

            if (!empty($articul))
            {
                $parameters = array_merge($parameters, array('articul' => $request->get('articul')));
            }

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
            $token = $request->get('token');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'role' => $role,
                'korobka' => $korobka,
                'token' => $token
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
            $token = $request->get('token');

            $parameters = array(
                'regionCode' => $regionCode,
                'modelCode' => $modelCode,
                'modificationCode' => $modificationCode,
                'korobka' => $korobka,
                'token' => $token
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

  /*  public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $articul = null, $token = null)
    {
        $data = $this->get('my_token_info')->getStatus($token);

        if(empty($data) & !empty($token)){
            return $this->errorBilling('Сервис не оплачен');
        }
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

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

        $schemas = Factory::createCollection($groupSchemas, Factory::createSchema())->getCollection();

        if(empty($subgroups))
            return $this->error($request, 'Подгруппы не найдены.');

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveSchema(reset($schemas)?:Factory::createSchema())
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

    public function schemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $token = null)
    {
        $data = $this->get('my_token_info')->getStatus($token);

        if(empty($data) & !empty($token)){
            return $this->errorBilling('Сервис не оплачен');
        }
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $subgroups = $this->model()->getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
        $schemas = $this->model()->getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);

        if(empty($schemas))
            return $this->error($request, 'Схемы не найдены.');

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
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($subgroups, Factory::createGroup())))
            ->setSchemas(Factory::createCollection($schemas, Factory::createSchema()));

        if (($filterResult = $this->filter($oContainer)) instanceof RedirectResponse) {
            return $filterResult;
        };

        $schemaCodes = array_keys($oContainer->getSchemas());
        if (1 == count($schemaCodes)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('schemas', 'schema', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'schemaCode' => $schemaCodes[0]
                        )
                    )
                ), 301
            );
        };

        return $this->render($this->bundle() . ':06_schemas.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
    }

    public function schemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null, $token = null)
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

        $groups = $this->model()->getGroups($regionCode, $modelCode, $modificationCode, $complectationCode);
        $groupsCollection = Factory::createCollection($groups, Factory::createGroup())->getCollection();

        $subgroups = $this->model()->getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $schemas = $this->model()->getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
        $schemaCollection = Factory::createCollection($schemas, Factory::createSchema())->getCollection();
        $oActiveSchema = $schemaCollection[$schemaCode];

        $pncs = $this->model()->getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $oActiveSchema->getOptions());
        $commonArticuls = $this->model()->getCommonArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $oActiveSchema->getOptions());
        $refGroups = $this->model()->getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $oActiveSchema->getOptions());

        $oActiveSchema
            ->setPncs(Factory::createCollection($pncs, Factory::createPnc()))
            ->setCommonArticuls(Factory::createCollection($commonArticuls, Factory::createArticul()))
            ->setRefGroups(Factory::createCollection($refGroups, Factory::createGroup()));

        $oContainer
            ->setActiveRegion($regionsCollection[$regionCode])
            ->setActiveModel($modelsCollection[$modelCode])
            ->setActiveModification($modificationsCollection[$modificationCode])
            ->setActiveGroup($groupsCollection[$groupCode]
                ->setSubGroups(Factory::createCollection($subgroups, Factory::createGroup())))
            ->setActiveSchema($oActiveSchema);

        $this->filter($oContainer);

        return $this->render($this->bundle() . ':07_schema.html.twig', array(
            'oContainer' => $oContainer,
            'parameters' => $parameters
        ));
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
            $pncCode = $request->get('pncCode');
            $options = $request->get('options');
            $token = $request->get('token');



            if (!empty($token))
            {
                $aDataToken = array();
                $aDataToken = $this->get('my_token_info')->getDataToken($token);

                $redirectAdress = $aDataToken['url'];
            }
            else
            {
                $redirectAdress = Constants::FIND_PATH;
            }



            $parameters = array(
                'regionCode' => $regionCode,
                'modificationCode' => $modificationCode,
                'options' => json_decode($options, true),
                'subGroupCode' => $subGroupCode,
                'pncCode' => $pncCode,
                'redirectAdress' => $redirectAdress
            );

            $articuls = $this->model()->getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode, json_decode($options, true));

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
  */

} 