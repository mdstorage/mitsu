<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 16.01.15
 * Time: 17:25
 */

namespace Catalog\SubaruBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Constants;

trait SubaruVinFilters {

    public function prodDateFilter($oContainer, $parameters)
    {
        $prodDate = $parameters[Constants::PROD_DATE];
        foreach ($oContainer->getActivePnc()->getArticuls() as $key => $articul) {
            if ($articul->getOption(Constants::START_DATE) > $prodDate || $articul->getOption(Constants::END_DATE) < $prodDate) {
                $oContainer->getActivePnc()->removeArticul($key);
            }
        }

        return $oContainer;
    }
    
    public function articulDescFilter($oContainer, $parameters)
    {
     /*   $aCompl = $this->model()->getVinCompl($parameters['regionCode'], $parameters['modelCode'], $parameters['complectationCode']);
        foreach ($oContainer->getActivePnc()->getArticuls() as $key => $articul) 
        
        {        	
            if ((substr_count($articul->getRuname(), $aCompl['body'])==0) &&
            (substr_count($articul->getRuname(), $aCompl['engine1'])==0)&&
            (substr_count($articul->getRuname(), $aCompl['train'])==0)&&
            (substr_count($articul->getRuname(), $aCompl['trans'])==0)&&
            (substr_count($articul->getRuname(), $aCompl['grade'])==0)&&
            (substr_count($articul->getRuname(), $aCompl['sus'])==0)
            ){
                $oContainer->getActivePnc()->removeArticul($key);
            }
            
        }if (count($oContainer->getActivePnc()->getArticuls())==0) {print_r('Выбранная запчасть на данную модель не устанавливается');die;}*/

        return $oContainer; 
    }

    public function vinGroupFilter($oContainer, $parameters)
    {
        $groups = $this->model()->getVinGroups($parameters['regionCode'], $parameters['modificationCode'], $parameters['complectationCode']);

        foreach ($oContainer->getGroups() as $group) {
            if (!in_array($group->getCode(), $groups, true)) {
                $oContainer->removeGroup($group->getCode());
            }
        }

        return $oContainer;
    }

    public function vinSubgroupFilter($oContainer, $parameters)
    {
        $subgroups = $this->model()->getVinSubGroups($parameters['regionCode'], $parameters['modificationCode'], $parameters['complectationCode'], $parameters['subComplectationCode']);

        foreach ($oContainer->getActiveGroup()->getSubGroups() as $subGroup) {
            if (!in_array($subGroup->getCode(), $subgroups, true)) {
                $oContainer->getActiveGroup()->removeSubgroup($subGroup->getCode());
            }
        }

        return $oContainer;
    }

    public function vinSchemasFilter($oContainer, $parameters)
    {
        $schemas = $this->model()->getVinSchemas($parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode'], $parameters['subGroupCode']);

        foreach ($oContainer->getSchemas() as $schema) {
            if (!in_array($schema->getCode(), $schemas, true)) {
                $oContainer->removeSchema($schema->getCode());
            }
        }

        return $oContainer;
    }

} 