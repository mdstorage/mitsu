<?php
namespace Catalog\LexusBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\LexusBundle\Components\LexusConstants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\LexusBundle\Controller\Traits\LexusVinFilters;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class VinController extends BaseController{
    use LexusVinFilters;
    public function bundle()
    {
        return 'CatalogLexusBundle:Vin';
    }

    public function model()
    {
        return $this->get('lexus.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\LexusBundle\Components\LexusConstants';
    }

    public function resultAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            $vin = $request->get('vin');

            $result = $this->model()->getVinFinderResult($vin);
            if (!$result) {
                return $this->render($this->bundle().':empty.html.twig');
            }

            /**
             * @deprecated Оставлен для совместимости с маздой
             */
            setcookie(Constants::PROD_DATE, $result[Constants::PROD_DATE]);
            setcookie(LexusConstants::INTCOLOR, $result[ LexusConstants::INTCOLOR]);


            setcookie(Constants::VIN, $vin);

            $brandSlash = $request->server->get('REQUEST_URI');


            $brand = explode('/', $brandSlash)[1];


            $callbackhost = trim($request->get('callbackhost'));



            $domain = trim($request->get('domain'));

            $domain = substr_replace($domain, '', strpos($domain, '.'), 1);



            $headers = $request->server->getHeaders();

            if (stripos($headers['REFERER'], 'callbackhost=') || stripos($headers['REFERER'], 'modelCode'))
            {
                if (!$call = $request->cookies->get(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain))))
                {
                    if ($callbackhost){
                        setcookie(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain)), $callbackhost);
                    }
                }
            }
            else{
                setcookie(Constants::COOKIEHOST.$brand.urlencode(str_replace('.', '', $domain)), "");
            }

            if (stripos($headers['REFERER'], 'domain')|| stripos($headers['REFERER'], 'modelCode'))
            {
                /*if (!$call = $request->cookies->get('DOMAIN'))*/
                {
                    if ($domain){
                        setcookie('DOMAIN', str_replace('.', '', $domain));
                    }

                }
            }
            else {
                setcookie('DOMAIN', "");
            }

            return $this->render($this->bundle().':02_result.html.twig', array(
                'result' => $result
            ));
        }
    }


    public function articulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $prodDate = $request->cookies->get(Constants::PROD_DATE);
            $color = $request->cookies->get(LexusConstants::INTCOLOR);
            $this->addFilter('prodDateFilter', array(Constants::PROD_DATE => $prodDate));
            $this->addFilter('vinArticulFilter', array('regionCode'=>$request->request->get('regionCode'), 'modelCode'=>$request->request->get('modelCode'), 'modificationCode'=>$request->request->get('modificationCode'),
                'complectationCode'=>$request->request->get('complectationCode'), 'color'=>$color));
            return parent::articulsAction($request);

        }
    }

    public function vinGroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $this->addFilter('vinGroupFilter', array(
            'regionCode' => $regionCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => substr($complectationCode, 0, 4)
        ));

        return $this->groupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode);
    }

    public function vinSubgroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $this->addFilter('vinSubGroupFilter', array(
            'regionCode' => $regionCode,
            'modificationCode' => $modificationCode,
            'complectationCode' => substr($complectationCode, 0, 4),
            'subComplectationCode' => substr($complectationCode, 3, 3)
        ));

        return $this->subgroupsAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode);
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


    public function vinGroupBySubgroupAction(Request $request, $regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {
        
        $groupCode = $this->model()->getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode);
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
    public function vinarticulsAction(Request $request)
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