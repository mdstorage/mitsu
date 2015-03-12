<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 27.01.15
 * Time: 17:37
 */

namespace Catalog\MercedesBundle\Controller\Traits;


trait ArticulFilters {

    public function articulAggregatesFilter($oContainer, $parameters)
    {
        $articulComplectations = $parameters['articulComplectations'];
        $modifications = $oContainer->getActiveModel()->getModifications();
        foreach ($modifications as $modificationCode => $modification) {
            $complectations = $modification->getOption('COMPLECTATIONS');
            foreach ($complectations as $complectationCode => $complectationRuname) {
                if (!in_array($complectationCode, $articulComplectations)) {
                    unset($complectations[$complectationCode]);
                }
            }
            if (empty($complectations)) {
                $oContainer->getActiveModel()->removeModification($modificationCode);
            }
        }

        return $oContainer;
    }

    public function articulSaSubgroupFilter($oContainer, $parameters)
    {
        $sanums = $parameters['sanums'];

        if (1 == count($sanums)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('subgroups', 'schemas', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'subGroupCode' => $sanums[0]
                        )
                    )
                ), 301
            );
        };

        foreach ($oContainer->getGroups() as $group) {
            if (!in_array($group->getCode(), $sanums)) {
                $oContainer->removeGroup($group->getCode());
            }
        }

        return $oContainer;
    }
} 