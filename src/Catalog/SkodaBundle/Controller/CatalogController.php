<?php
namespace Catalog\SkodaBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogSkodaBundle:Catalog';
    }

    public function model()
    {
        return $this->get('skoda.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SkodaBundle\Components\SkodaConstants';
    }
    

    public function getGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {

        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode);
        $parameters = $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());

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