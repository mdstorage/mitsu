<?php
namespace Catalog\LandRoverBundle\Controller;

use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Catalog\LandRoverBundle\Controller\Traits\LandRoverArticulFilters;

class ArticulController extends BaseController{
use LandRoverArticulFilters;
    public function bundle()
    {
        return 'CatalogLandRoverBundle:Articul';
    }

    public function model()
    {
        return $this->get('landrover.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\LandRoverBundle\Components\LandRoverConstants';
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode);
    }
    
       
    public function landroverArticulComplectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null)
    {
       $articul = $request->cookies->get(Constants::ARTICUL);

        $parameters = array();

        $this->addFilter('CFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'articul' => $articul
        ));


        return parent::complectationsAction($request, $regionCode, $modelCode, $modificationCode);
    }

    public function landroverArticulGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $articul = null, $token = null)
    {
      
        $articulGroups = $this->model()->getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode);

        $this->addFilter('articulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $articul, $token);
    }

    public function landroverArticulSubgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $articul = null, $token = null)
    {

        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $this->addFilter('articulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $articul, $token);
        
    }

    public function landroverArticulSchemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $articul = null, $token = null)
    {

        $articulSchemas = $this->model()->getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);


        $this->addFilter('articulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $articul, $token);
    }

    public function landroverArticulSchemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null, $articul = null, $token = null)
    {

        $articulPncs = $this->model()->getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);

        $this->addFilter('articulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $articul, $token);
    }
    public function landroverArticularticulsAction(Request $request)
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