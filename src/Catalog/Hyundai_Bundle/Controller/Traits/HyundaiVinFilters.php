<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 16.01.15
 * Time: 17:25
 */

namespace Catalog\HyundaiBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Constants;

trait HyundaiVinFilters {

    public function prodDateFilter($oContainer, $parameters)
    {
        $prodDate = $parameters[Constants::PROD_DATE];
        foreach ($oContainer->getActivePnc()->getArticuls() as $key => $articul) {
            if ($articul->getOption(Constants::START_DATE) > $prodDate || $articul->getOption(Constants::END_DATE) <= $prodDate + 1) {
                $oContainer->getActivePnc()->removeArticul($key);
            }
        }
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

    public function vinSubgroupFilter($oContainer, $parameters)
    {
        

        return $oContainer;
    }

    public function vinSchemasFilter($oContainer, $parameters)
    {
        

        return $oContainer;
    }

} 