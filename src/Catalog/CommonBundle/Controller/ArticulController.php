<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Symfony\Component\HttpFoundation\Request;

abstract class ArticulController extends CatalogController{

    public function indexAction()
    {
        setcookie(Constants::ARTICUL, "");
        return $this->render($this->bundle().':01_index.html.twig');
    }

    public function findByArticulAction(Request $request, $regionCode = null)
    {
        if (!$articul = $request->cookies->get(Constants::ARTICUL)) {
            if ($articul = $request->get('articul')) {
                setcookie(Constants::ARTICUL, $articul);
            } else {
                return $this->render($this->bundle().':01_index.html.twig');
            }
        }

        $articulRegions = $this->model()->getArticulRegions($articul);
        $articulModels  = $this->model()->getArticulModels($articul);

        $this->addFilter('aticulRegionModelsFilter', array(
            'articulRegions' => $articulRegions,
            'articulModels'  => $articulModels
        ));

        return $this->regionsModelsAction($request, $regionCode);
    }

    public function aticulRegionModelsFilter($oContainer, $parameters)
    {
        $articulModels = $parameters['articulModels'];

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
            $articul = $request->cookies->get(Constants::ARTICUL);
            $articulModifications = $this->model()->getArticulModifications($articul);

            $this->addFilter('aticulModificationsFilter', array(
                'articulModifications' => $articulModifications
            ));

            return parent::modificationsAction($request);
        }
    }

    public function aticulModificationsFilter($oContainer, $parameters)
    {
        $articulModifications = $parameters['articulModifications'];

        foreach ($oContainer->getActiveModel()->getModifications() as $modification) {
            if (!in_array($modification->getCode(), $articulModifications, true)) {
                $oContainer->getActiveModel()->removeModification($modification->getCode());
            }
        }

        return $oContainer;
    }

    public function groupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulGroups = $this->model()->getArticulGroups($articul, $modificationCode);

        $this->addFilter('aticulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode);
    }

    public function aticulGroupsFilter($oContainer, $parameters)
    {
        $articulGroups = $parameters['articulGroups'];

        foreach ($oContainer->getGroups() as $group) {
            if (!in_array($group->getCode(), $articulGroups, true)) {
                $oContainer->removeGroup($group->getCode());
            }
        }

        return $oContainer;
    }

    public function subgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $modificationCode);

        $this->addFilter('aticulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
    }

    public function aticulSubGroupsFilter($oContainer, $parameters)
    {
        $articulSubGroups = $parameters['articulSubGroups'];

        foreach ($oContainer->getActiveGroup()->getSubGroups() as $subgroup) {
            if (!in_array($subgroup->getCode(), $articulSubGroups, true)) {
                $oContainer->getActiveGroup()->removeSubGroup($subgroup->getCode());
            }
        }

        return $oContainer;
    }

    public function schemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulSchemas = $this->model()->getArticulSchemas($articul, $modificationCode, $subGroupCode);

        $this->addFilter('aticulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
    }

    public function aticulSchemasFilter($oContainer, $parameters)
    {
        $articulSchemas = $parameters['articulSchemas'];

        foreach ($oContainer->getSchemas() as $schema) {
            if (!in_array($schema->getCode(), $articulSchemas, true)) {
                $oContainer->removeSchema($schema->getCode());
            }
        }

        return $oContainer;
    }

    public function schemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulPncs = $this->model()->getArticulPncs($articul, $modificationCode, $subGroupCode);

        $this->addFilter('aticulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);
    }

    public function aticulPncsFilter($oContainer, $parameters)
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