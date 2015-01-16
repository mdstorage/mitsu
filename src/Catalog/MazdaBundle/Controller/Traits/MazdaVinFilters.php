<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 16.01.15
 * Time: 17:25
 */

namespace Catalog\MazdaBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Constants;

trait MazdaVinFilters {

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

    public function vinSchemasFilter($oContainer, $parameters)
    {
        $schemas = $this->model()->getVinSchemas($parameters['regionCode'], $parameters['modificationCode'], $parameters['complectationCode'], $parameters['subComplectationCode'], $parameters['subGroupCode']);

        foreach ($oContainer->getSchemas() as $schema) {
            if (!in_array($schema->getCode(), $schemas, true)) {
                $oContainer->removeSchema($schema->getCode());
            }
        }

        return $oContainer;
    }

} 