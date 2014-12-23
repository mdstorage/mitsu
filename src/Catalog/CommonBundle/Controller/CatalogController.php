<?php
namespace Catalog\CommonBundle\Controller;


use Catalog\CommonBundle\Components\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class CatalogController extends BaseController{

    abstract function bundle();

    abstract function model();

    public function regionsModelsAction($regionCode)
    {
        /*
         * Выборка регионов из базы данных для конкретного артикула
         */
        $aRegions = $this->model()->getRegions();

        if(empty($aRegions)){
            return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Регионы не найдены.'));
        } else {
            $oActiveRegion = Factory::createRegion();
            /*
             * Если регионы найдены, они помещаются в контейнер
             */
            $oContainer = Factory::createContainer()
                ->setRegions(Factory::createCollection($aRegions, $oActiveRegion));

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

            $models = $this->model()->getModels($oActiveRegion->getCode());

            if(empty($models)){
                return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array('message'=>'Модели не найдены.'));
            } else {
                $oActiveRegion->setModels(Factory::createCollection($models, Factory::createModel()));
            }

            $oContainer->setActiveRegion($oActiveRegion);
        }

        return $this->render($this->bundle() . ':Catalog:01_regions_models.html.twig', array(
            'oContainer' => $oContainer
        ));
    }
} 