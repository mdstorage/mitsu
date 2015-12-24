<?php
namespace Catalog\BmwBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogBmwBundle:Catalog';
    }

    public function model()
    {
        return $this->get('bmw.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\BmwBundle\Components\BmwConstants';
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

    public function complectation_dataAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $role = $request->get('role');
            $modificationCode = $request->get('modificationCode');


            $result = $this->model()->getComplectationsData($role, $modificationCode);

            return $this->render($this->bundle().':03_complectations_data.html.twig', array(
                'result' => $result
            ));
        }
    }

    public function complectation_catalog_dataAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $role = $request->get('role');
            $modificationCode = $request->get('modificationCode');
            $year = $request->get('year');


            $result = $this->model()->getComplectationsCatalogData($role, $modificationCode, $year);

            return $this->render($this->bundle().':03_complectation_catalog_data.html.twig', array(
                'result' => $result
            ));
        }
    }
} 