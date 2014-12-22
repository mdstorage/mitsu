<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Factory;

class CatalogController extends BaseController{
    public function regionsModelsAction($articul, $regionCode=null)
    {
        /*
         * Выборка регионов из базы данных для конкретного артикула
         */
        $aRegions = FindArticulModel::getRegions($articul);
        if(empty($aRegions)){
            throw new CHttpException("Запчасть с артикулом " .$articul. " отсутствует в каталоге.");
        } else {
            $oActiveRegion = Factory::createRegion();
            /*
             * Если регионы найдены, они помещаются в контейнер
             */
            $oContainer = Factory::createContainer()
                ->setActiveArticul(Factory::createArticul($articul))
                ->setRegions($aRegions, $oActiveRegion);
            /*
             * Если пользователь задал регион, то этот регион становится активным
             */
            if (!is_null($regionCode)){
                $oActiveRegion->setCode($regionCode);
            } else{
                /*
                 * Если пользователь не задавал регион, то в качестве активного выбирается первый из списка регионов объект
                 */
                $regions = $oContainer->getRegions();
                $oActiveRegion->setCode($regions[0]->getCode());
            }

            $oActiveRegion->setName($oActiveRegion->getCode());
            /*
             * Выборка моделей из базы для данного артикула и региона
             */
            $models = FindArticulModel::getActiveRegionModels($articul, $oActiveRegion->getCode());

            if(empty($models)){
                throw new CHttpException("Ошибка в выборе моделей для региона: " . $oActiveRegion->getRuname());
            } else {
                $oActiveRegion->setModels($models, Factory::createModel());
            }

            $oContainer->setActiveRegion($oActiveRegion);

        }
    }
} 