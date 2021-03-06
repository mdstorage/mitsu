<?php
namespace Catalog\SaabBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\SaabBundle\Controller\Traits\SaabVinFilters;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class VinController extends BaseController{
    use SaabVinFilters;
    public function bundle()
    {
        return 'CatalogSaabBundle:Vin';
    }

    public function model()
    {
        return $this->get('saab.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SaabBundle\Components\SaabConstants';
    }


    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return parent::articulsAction($request);

        }
    }

    public function vinGroupsAction(Request $request, $regionCode, $modelCode, $modificationCode)
    {
        $this->addFilter('vinGroupFilter', array(
            'regionCode' => $regionCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => substr($complectationCode, 0, 4)
        ));

        return $this->groupsAction($request, $regionCode, $modelCode, $modificationCode);
    }

    public function vinSubgroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $prodDate = $request->cookies->get(Constants::PROD_DATE);

        $this->addFilter('vinSubGroupFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'groupCode' => $groupCode,
            Constants::PROD_DATE => $prodDate
        ));

        return $this->subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $groupCode);
    }

    public function vinSchemasAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $this->addFilter('vinSchemasFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => substr($modificationCode, 1, 5),
            'subGroupCode' => $subGroupCode
        ));

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);
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