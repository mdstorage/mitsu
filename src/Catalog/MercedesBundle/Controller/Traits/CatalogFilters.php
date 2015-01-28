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
        $aggCollection = Factory::createCollection($aggregates, Factory::createModification());

        $oContainer->getActiveModel()->setModifications($aggCollection);

        return $oContainer;
    }

    public function catalogSubGroupsFilter($oContainer, $parameters)
    {
        $saSubGroups = $this->model()->getSaSubgroups(
            $parameters['regionCode'],
            $parameters['modelCode'],
            $parameters['modificationCode'],
            $parameters['complectationCode'],
            $parameters['groupCode']
        );

        $saSubGroupsCollection = Factory::createCollection($saSubGroups, Factory::createGroup());

        $oContainer->setGroups($saSubGroupsCollection);

        return $oContainer;
    }
} 