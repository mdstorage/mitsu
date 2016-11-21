<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Symfony\Component\HttpFoundation\Request;

abstract class ArticulController extends CatalogController{

    public function indexAction($error_message = null, $token = null)
    {
        $brandSlash = $this->get('request')->server->get('REQUEST_URI');

        $brand = explode('/', $brandSlash)[1];
        setcookie(Constants::ARTICUL, '');


        $headers = $this->get('request')->server->getHeaders();

        foreach($this->get('request')->cookies->keys() as $index => $value)
        {
            if (!empty($headers['REFERER'])){
                if (stripos($headers['REFERER'], 'vincat.ru') == false){
                    if (stripos($value, Constants::COOKIEHOST) !== false)
                    {
                        setcookie($value, "");
                    }
                }

            }

        }

        return $this->render($this->bundle().':01_index.html.twig', array('error_message' => $error_message));
    }

    public function findByArticulAction(Request $request, $token = null, $regionCode = null)
    {

        if (!$articul = $request->cookies->get(Constants::ARTICUL)) {
            if ($articul = trim($request->get('articul'))) {
                setcookie(Constants::ARTICUL, $articul);
            } else {
                return $this->indexAction('Запчасть с таким артикулом не найдена.', $token);
            }
        }
        if (strlen($articul)<7)
        {
            return $this->indexAction('Запчасть с таким артикулом не найдена.', $token);
        }

        $articul = trim($request->get('articul'));
        $articulRegions = $this->model()->getArticulRegions($articul);

        if (empty($articulRegions)) {
            setcookie(Constants::ARTICUL, '');
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

    public function articulRegionModelsFilter($oContainer, $parameters)
    {
        $articulModels = $parameters['articulModels'];
        $articulRegions = $parameters['articulRegions'];
        $regionCode = $parameters['regionCode'];

        foreach ($oContainer->getRegions() as $region) {
            if (!in_array($region->getCode(), $articulRegions, true)) {
                $oContainer->removeRegion($region->getCode());
            }
        }

        $regionsList = $oContainer->getRegions();

        if (!is_null($regionCode)){
            $oActiveRegion = $regionsList[$regionCode];
        } else{
            /*
             * Если пользователь не задавал регион, то в качестве активного выбирается первый из списка регионов объект
             */
            $oActiveRegion = reset($regionsList);
        }
        $models = $this->model()->getModels($oActiveRegion->getCode());

        $oContainer->setActiveRegion($oActiveRegion
            ->setModels(Factory::createCollection($models, Factory::createModel()))
        );

        foreach ($oContainer->getActiveRegion()->getModels() as $model) {
            if (!in_array($model->getCode(), $articulModels, true)) {
                $oContainer->getActiveRegion()->removeModel($model->getCode());
            }
        }

        return $oContainer;
    }

    public function modificationsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $articul = $request->get('articul');
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');

            $brandSlash = $request->server->get('REQUEST_URI');


            $brand = explode('/', $brandSlash)[1];


            $callbackhost = trim($request->get('callbackhost'));
            $domain = trim($request->get('domain'));
            $domain = substr_replace($domain, '', strpos($domain, '.'), 1);


            $headers = $request->server->getHeaders();

            if (stripos($headers['REFERER'], 'callbackhost=') || stripos($headers['REFERER'], 'modelCode'))
            {
                if (!$call = $request->cookies->get(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain))))
                {
                    if ($callbackhost){
                        setcookie(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain)), $callbackhost);
                    }
                }
            }
            else{
                setcookie(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain)), "");
            }

            if (stripos($headers['REFERER'], 'domain')|| stripos($headers['REFERER'], 'modelCode'))
            {
                if (!$call = $request->cookies->get('DOMAIN'))
                {
                    if ($domain){
                        setcookie('DOMAIN', str_replace('.', '', $domain));
                    }

                }
            }
            else {
                setcookie('DOMAIN', "");
            }

            $articulModifications = $this->model()->getArticulModifications($articul, $regionCode, $modelCode);




            $this->addFilter('articulModificationsFilter', array(
                'articulModifications' => $articulModifications
            ));

            return parent::modificationsAction($request);
        }
    }

    public function articulModificationsFilter($oContainer, $parameters)
    {
        $articulModifications = $parameters['articulModifications'];

        foreach ($oContainer->getActiveModel()->getModifications() as $modification) {
            if (!in_array($modification->getCode(), $articulModifications)) {
                $oContainer->getActiveModel()->removeModification($modification->getCode());
            }
        }

        return $oContainer;
    }

    public function complectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $articul = null,  $token = null)
    {
        $articulComplectations = $this->model()->getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode);

        $this->addFilter('articulComplectationsFilter', array(
            'articulComplectations' => $articulComplectations
        ));

        return parent::complectationsAction($request, $regionCode, $modelCode, $modificationCode, $articul, $token);
    }

    public function articulComplectationsFilter($oContainer, $parameters)
    {
        $articulComplectations = $parameters['articulComplectations'];

        foreach ($oContainer->getActiveModification()->getComplectations() as $complectation) {
            if (!in_array($complectation->getCode(), $articulComplectations, true)) {
                $oContainer->getActiveModification()->removeComplectation($complectation->getCode());
            }
        }

        return $oContainer;
    }

    public function articulGroupsFilter($oContainer, $parameters)
    {
        $articulGroups = $parameters['articulGroups'];

        foreach ($oContainer->getGroups() as $group) {
            if (!in_array($group->getCode(), $articulGroups, true)) {
                $oContainer->removeGroup($group->getCode());
            }
        }

        return $oContainer;
    }

    public function articulSubGroupsFilter($oContainer, $parameters)
    {
        $articulSubGroups = $parameters['articulSubGroups'];
        $ar = array();
        foreach ($oContainer->getActiveGroup()->getSubGroups() as $subgroup) {
$ar[]=$subgroup->getCode();
            if (!in_array($subgroup->getCode(), $articulSubGroups, true)) {
                $oContainer->getActiveGroup()->removeSubGroup($subgroup->getCode());
            }
        }

        return $oContainer;
    }

    public function articulSchemasFilter($oContainer, $parameters)
    {
        $articulSchemas = $parameters['articulSchemas'];

        foreach ($oContainer->getSchemas() as $schema) {
            if (!in_array($schema->getCode(), $articulSchemas, true)) {
                $oContainer->removeSchema($schema->getCode());
            }
        }

        return $oContainer;
    }

    public function articulPncsFilter($oContainer, $parameters)
    {
        $articulPncs = $parameters['articulPncs'];

        foreach ($oContainer->getActiveSchema()->getPncs() as $pnc) {
            if (in_array($pnc->getCode(), $articulPncs)) {
                $oContainer->getActiveSchema()->setActivePnc($pnc->getCode());
            }
        }

        return $oContainer;
    }
} 