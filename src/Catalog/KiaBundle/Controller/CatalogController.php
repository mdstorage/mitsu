<?php
namespace Catalog\KiaBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogKiaBundle:Catalog';
    }

    public function model()
    {
        return $this->get('kia.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\KiaBundle\Components\KiaConstants';
    }
    

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode, $token = null)
    {
        $subGroupCode = (substr_count($subGroupCode, '-') > 1) ? substr($subGroupCode, 0, strripos($subGroupCode, '-')+1): $subGroupCode;
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());
        $parameters['subGroupCode'] = $subGroupCode;

        return $this->redirect(
            $this->generateUrl(
                str_replace('group', 'schemas', $this->get('request')->get('_route')),
                array_merge($parameters, array(
                        'groupCode' => $groupCode
                    )
                )
            ), 301
        );
    }
} 