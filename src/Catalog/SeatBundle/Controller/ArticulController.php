<?php
namespace Catalog\SeatBundle\Controller;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Controller\ArticulController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Catalog\SeatBundle\Controller\Traits\SeatVinFilters;

class ArticulController extends BaseController{
use SeatVinFilters;
    public function bundle()
    {
        return 'CatalogSeatBundle:Articul';
    }

    public function model()
    {
        return $this->get('seat.articul.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SeatBundle\Components\SeatConstants';
    }

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode);
    }
    
       
    public function seatArticulComplectationsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $articul = null, $token = null)
    {
        $articulComplectations = $this->model()->getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode);

        $this->addFilter('articulComplectationsFilter', array(
            'articulComplectations' => $articulComplectations
        ));

        return parent::complectationsAction($request, $regionCode, $modelCode, $modificationCode, $articul, $token);
    }

    public function seatArticulGroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $articul = null, $token = null)
    {
      
        $articulGroups = $this->model()->getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode);

        $this->addFilter('articulGroupsFilter', array(
            'articulGroups' => $articulGroups
        ));

        return parent::groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $articul, $token);
    }

    public function seatArticulSubgroupsAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $articul = null, $token = null)
    {
        $articulSubGroups = $this->model()->getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);

        $this->addFilter('articulSubGroupsFilter', array(
            'articulSubGroups' => $articulSubGroups
        ));

        return parent::subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $articul, $token);
        
    }

    public function seatArticulSchemasAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $articul = null, $token = null)
    {
        $articulSchemas = $this->model()->getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
		
		
        $this->addFilter('articulSchemasFilter', array(
            'articulSchemas' => $articulSchemas
        ));

        return parent::schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $articul, $token);
    }

    public function seatArticulSchemaAction(Request $request, $regionCode = null, $modelCode = null, $modificationCode = null, $complectationCode = null, $groupCode = null, $subGroupCode = null, $schemaCode = null, $articul = null, $token = null)
    {
        $articulPncs = $this->model()->getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);

        $this->addFilter('articulPncsFilter', array(
            'articulPncs' => $articulPncs
        ));

        return parent::schemaAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $articul, $token);
    }
} 