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

} 