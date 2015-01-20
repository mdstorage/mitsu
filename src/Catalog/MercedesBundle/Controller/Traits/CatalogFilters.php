<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 20.01.15
 * Time: 9:20
 */

namespace Catalog\MercedesBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Factory;

trait CatalogFilters {

    public function catalogGroupsFilter($oContainer, $parameters)
    {
        $aggregates = $this->model()->getComplectationAgregats($parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode'], $parameters['complectationCode']);
        $aggCollection = Factory::createCollection($aggregates, Factory::createModel());

        $oContainer->getActiveModel()->setModifications($aggCollection);

        return $oContainer;
    }
} 