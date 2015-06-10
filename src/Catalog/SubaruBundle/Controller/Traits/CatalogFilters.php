<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 20.01.15
 * Time: 9:20
 */

namespace Catalog\SubaruBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Factory;
use Symfony\Component\HttpFoundation\RedirectResponse;

trait CatalogFilters {

    public function catalogGroupsFilter($oContainer, $parameters)
    {
        $groups = $this->model()->getGroups($parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode'], $parameters['complectationCode']);
        $groupCode = array_keys($oContainer->getGroups())[0];
        $schemaCodes = array_keys($groups);
        if (1 == count($schemaCodes)) {
            return $this->redirect(
                $this->generateUrl(
                    str_replace('groups', 'subgroups', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'groupCode' => $groupCode
                        )
                    )
                ), 301
            );
        };

        return $oContainer;
    }

    public function catalogSubGroupsFilter($oContainer, $parameters)
    {
       var_dump($parameters); die;
        $subgroups = $this->model()->getSubgroups($parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode'], $parameters['complectationCode'], $parameters['groupCode']); 
        $subGroupCode = array_keys($oContainer->getActiveGroup()->getSubGroups())[0];
        $sa = count($oContainer->getActiveGroup()->getSubGroups());
        $schemaCodes = array_keys($groups);
        if (1 == $sa) {
             return $this->redirect(
                $this->generateUrl(
                    str_replace('subgroups', 'schemas', $this->get('request')->get('_route')),
                    array_merge($parameters, array(
                            'subGroupCode' => $subGroupCode
                        )
                    )
                ), 301
            );
        };

        return $oContainer;
    }
} 