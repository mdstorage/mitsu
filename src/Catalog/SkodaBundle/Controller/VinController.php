<?php
namespace Catalog\SkodaBundle\Controller;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;
use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Controller\VinController as BaseController;
use Catalog\SkodaBundle\Controller\Traits\SkodaVinFilters;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class VinController extends BaseController{
    use SkodaVinFilters;
    public function bundle()
    {
        return 'CatalogSkodaBundle:Vin';
    }

    public function model()
    {
        return $this->get('skoda.vin.model');
    }

    public function bundleConstants()
    {
        return 'Catalog\SkodaBundle\Components\SkodaConstants';
    }

    public function regionAction(Request $request, $regionCode = null)
    {
        if ($request->isXmlHttpRequest()) {

            $vin = $request->get('vin');


            $aRegions = $this->model()->getVinRegions($vin);

            if(empty($aRegions)){
                return $this->render($this->bundle().':empty.html.twig');
            } else {
                $oActiveRegion = Factory::createRegion();
                /**
                 * Если регионы найдены, они помещаются в контейнер
                 */
                $regionsCollection = Factory::createCollection($aRegions, $oActiveRegion);
                $oContainer = Factory::createContainer()
                    ->setRegions($regionsCollection);
                unset($aRegions);
                /**
                 * Если пользователь задал регион, то этот регион становится активным
                 */
                $regionsList = $regionsCollection->getCollection();
                if (!is_null($regionCode)){
                    $oActiveRegion = $regionsList[$regionCode];
                } else{
                    /*
                     * Если пользователь не задавал регион, то в качестве активного выбирается первый из списка регионов объект
                     */
                    $oActiveRegion = reset($regionsList);
                }

                /**
                 * Выборка моделей из базы для данного артикула и региона
                 */


                $oContainer->setActiveRegion($oActiveRegion);

                $this->filter($oContainer);
            }





            /**
             * @deprecated Оставлен для совместимости с маздой
             */

            setcookie(Constants::VIN, $vin);


            return $this->render($this->bundle().':02_region.html.twig', array(
                'oContainer' => $oContainer
            ));
        }
    }



    public function resultAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $vin = $request->cookies->get(Constants::VIN);


            $region = $request->get('region');


            $result = $this->model()->getVinFinderResult($vin, $region);

            $aRegions = $this->model()->getVinRegions($vin);

            foreach ($aRegions as $index => $value)
            {
                if ($index!=$region)
                {
                    unset ($aRegions[$index]);
                }
            }

            if(empty($aRegions)){
                return $this->render($this->bundle().':empty.html.twig');
            } else {
                $oActiveRegion = Factory::createRegion();
                /**
                 * Если регионы найдены, они помещаются в контейнер
                 */
                $regionsCollection = Factory::createCollection($aRegions, $oActiveRegion);
                $oContainer = Factory::createContainer()
                    ->setRegions($regionsCollection);
                unset($aRegions);
                /**
                 * Если пользователь задал регион, то этот регион становится активным
                 */
                $regionsList = $regionsCollection->getCollection();
                if (!is_null($region)){
                    $oActiveRegion = $regionsList[$region];
                } else{
                    /*
                     * Если пользователь не задавал регион, то в качестве активного выбирается первый из списка регионов объект
                     */
                    $oActiveRegion = reset($regionsList);
                }

                /**
                 * Выборка моделей из базы для данного артикула и региона
                 */


                $oContainer->setActiveRegion($oActiveRegion);

                $this->filter($oContainer);
            }



            if (!$result) {
                return $this->render($this->bundle().':empty.html.twig');
            }

            setcookie(Constants::PROD_DATE, $result[Constants::PROD_DATE]);


            setcookie(Constants::VIN, $vin);

            return $this->render($this->bundle().':02_result.html.twig', array(
                'result' => $result,
                'oContainer' => $oContainer
            ));
        }
    }


    public function vinArticulsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $prodDate = $request->cookies->get(Constants::PROD_DATE);
            $this->addFilter('vinArticulFilter', array(
                'regionCode'=>$request->request->get('regionCode'),
                'modelCode'=>$request->request->get('modelCode'),
                'modificationCode'=>$request->request->get('modificationCode'),
                'groupCode' => $request->request->get('groupCode'),
                'subGroupCode' => $request->request->get('subGroupCode'),
                'pncCode' => $request->request->get('pncCode'),
                'vin' => $request->cookies->get(Constants::VIN),
            ));
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

    public function vinSubgroupsAction(Request $request, $regionCode, $modelCode, $modificationCode, $groupCode)
    {

        $this->addFilter('vinSubGroupFilter', array(
            'regionCode' => $regionCode,
            'modelCode' => $modelCode,
            'modificationCode' => $modificationCode,
            'groupCode' => $groupCode,
            'vin' => $request->cookies->get(Constants::VIN),
        ));

        return $this->subgroupsAction($request, $regionCode, $modelCode, $modificationCode,$complectationCode = null, $groupCode);
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

        return $this->schemasAction($request, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode);

    }
} 