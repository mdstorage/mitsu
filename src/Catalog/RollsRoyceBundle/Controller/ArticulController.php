<?php
namespace Catalog\RollsRoyceBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Catalog\RollsRoyceBundle\Controller\Traits\RollsRoyceVinFilters;

class ArticulController extends BaseController{
use RollsRoyceVinFilters;
    public function bundle()
    {
        return 'CatalogRollsRoyceBundle:Articul';
    }

    public function model()
    {
        return $this->get('rollsroyce.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\RollsRoyceBundle\Components\RollsRoyceConstants';
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
    
       
    public function rollsroyceArticulComplectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null)
    {
       $articul = $request->cookies->get(Constants::ARTICUL);
        $articulComplectations = $this->model()->getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode);

        $this->addFilter('articulComplectationsFilter', array(
            'articulComplectations' => $articulComplectations
        ));

        return parent::complectationsAction($request, $regionCode, $modelCode, $modificationCode);
    }

    public function rollsroyceArticulGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
      
        $articulGroups = $this->model()->getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode);

        $this->addFilter('articulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode);
    }

    public function rollsroyceArticulSubgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL); 
        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $this->addFilter('articulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
        
    }

    public function rollsroyceArticulSchemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulSchemas = $this->model()->getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
		
		
        $this->addFilter('articulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
    }

    public function rollsroyceArticulSchemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null)
    {
        $articul = $request->cookies->get(Constants::ARTICUL);
        $articulPncs = $this->model()->getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);

        $this->addFilter('articulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode);
    }
} 