<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 16.01.15
 * Time: 17:25
 */

namespace Catalog\AudiBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Constants;

trait AudiVinFilters {

    public function prodDateFilter($oContainer, $parameters)
    {

        return $oContainer;
    }
    
    public function articulDescFilter($oContainer, $parameters)
    {
        

        return $oContainer; 
    }

    public function vinGroupFilter($oContainer, $parameters)
    {
        

        return $oContainer;
    }

    public function vinSubGroupFilter($oContainer, $parameters)
    {
        $subgroups = $this->model()->getVinSubgroups($parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode'], $parameters['groupCode'], $parameters['vin']);

        foreach ($oContainer->getActiveGroup()->getSubGroups() as $subGroup) {


            if (!in_array($subGroup->getCode(), $subgroups)) {
                $oContainer->getActiveGroup()->removeSubgroup($subGroup->getCode());
            }
        }

        return $oContainer;
    }

    public function vinSchemasFilter($oContainer, $parameters)
    {
        

        return $oContainer;
    }

} 