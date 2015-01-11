<?php
namespace Catalog\CommonBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

abstract class ArticulController extends CatalogController{

    public function indexAction()
    {
        return $this->render($this->bundle().':01_index.html.twig');
    }

    public function findByArticulAction(Request $request, $regionCode = null)
    {
        $articul = $request->get('articul');
        $articulRegions = $this->model()->getArticulRegions($articul);
        $articulModels  = $this->model()->getArticulModels($articul);

        $this->addFilter('aticulModelsFilter', array(
            'articulRegions' => $articulRegions,
            'articulModels'  => $articulModels
        ));

        return $this->regionsModelsAction($regionCode);
    }

    public function aticulRegionModelsFilter($oContainer, $parameters)
    {
        return $oContainer;
    }
} 