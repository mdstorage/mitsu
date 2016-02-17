<?php
namespace Catalog\FiatBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\CatalogController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends BaseController{

    public function bundle()
    {
        return 'CatalogFiatBundle:Catalog';
    }

    public function model()
    {
        return $this->get('fiat.catalog.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\FiatBundle\Components\FiatConstants';
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
    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $regionCode = $request->get('regionCode');
            $modelCode = $request->get('modelCode');
            $modificationCode = $request->get('modificationCode');
            $complectationCode = $request->get('complectationCode');
            $groupCode = $request->get('groupCode');
            $subGroupCode = $request->get('subGroupCode');
            $schemaCode = $request->get('schemaCode');
            $pncCode = $request->get('pncCode');
            $options = $request->get('options');

            $parameters = array(
                'regionCode' => $regionCode,
                'modificationCode' => $modificationCode,
                'options' => json_decode($options, true),
                'subGroupCode' => $subGroupCode,
                'pncCode' => $pncCode
            );

            $articuls = $this->model()->getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $pncCode, json_decode($options, true));

            $oContainer = Factory::createContainer()
                ->setActivePnc(Factory::createPnc($pncCode, $pncCode)
                    ->setArticuls(Factory::createCollection($articuls, Factory::createArticul()))
                );

            $this->filter($oContainer);

            return $this->render($this->bundle() . ':08_articuls.html.twig', array(
                'oContainer' => $oContainer,
                'parameters' => $parameters
            ));
        }
    }
} 