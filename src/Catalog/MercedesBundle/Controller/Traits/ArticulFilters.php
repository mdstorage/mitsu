<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 27.01.15
 * Time: 17:37
 */

namespace Catalog\MercedesBundle\Controller\Traits;


trait ArticulFilters {

    public function articulGroupsFilter($oContainer, $parameters)
    {
        $articulGroups = $parameters['articulGroups'];

        foreach ($oContainer->getGroups() as $group) {
            if (!in_array($group->getCode(), $articulGroups, true)) {
                $oContainer->removeGroup($group->getCode());
            }
        }

        return $oContainer;
    }

    public function articulAggregatesFilter()
    {
        
    }

} 