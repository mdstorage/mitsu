<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 20.01.15
 * Time: 9:20
 */

namespace Catalog\MercedesBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Factory;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait CatalogFilters {

    public function catalogGroupsFilter($oContainer, $parameters)
    {
        $aggregates = $this->model()->getComplectationAgregats($parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode'], $parameters['complectationCode']);
        $aggCollection = Factory::createCollection($aggregates, Factory::createModification());

        $oContainer->getActiveModel()->setModifications($aggCollection);

        $bm = count($oContainer->getGroups());
        $groupCode = array_keys($oContainer->getGroups())[0];

        if (
            (1 == $bm && !$aggregates)
        ) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('groups', 'subgroups', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'groupCode' => $groupCode
                        )
                    )
                ), 301
            );
        }

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


        $bm = count($oContainer->getGroups());
        $sa = count($oContainer->getActiveGroup()->getSubGroups());
        $subGroupCode = 1 == $bm ? array_keys($oContainer->getGroups())[0] : array_keys($oContainer->getActiveGroup()->getSubGroups())[0];

        if (
            (1 == $bm && 0 == $sa) ||
            (0 == $bm && 1 == $sa)
        ) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('subgroups', 'schemas', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'subGroupCode' => $subGroupCode
                        )
                    )
                ), 301
            );
        }

        return $oContainer;
    }
} 