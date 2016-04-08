<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 16.01.15
 * Time: 17:25
 */

namespace Catalog\LandRoverBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Constants;

trait LandRoverVinFilters {

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
    
    public function vinArticulFilter($oContainer, $parameters)

    {

        $color = array($parameters['color'], 'C'.$parameters['color']);
        $complectations = $this->model()->getComplectations($parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode']);
        $aComplOptions = explode('.', $complectations[$parameters['complectationCode']]['options']['OPTION9']);

        foreach ($oContainer->getActivePnc()->getArticuls() as $articul)
        {
            /*  $aArticulOptions = explode('.', $articul->getOption('DESCR'));

              if (count($aArticulOptions) != count(array_intersect($aArticulOptions, $aComplOptions)))
              {
                  $oContainer->getActivePnc()->removeArticul($articul->getCode());
              }

              if (($articul->getOption('ColorCode')) && (!in_array($articul->getOption('ColorCode'), $color)))
              {
                  $oContainer->getActivePnc()->removeArticul($articul->getCode());
              }*/


        }

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