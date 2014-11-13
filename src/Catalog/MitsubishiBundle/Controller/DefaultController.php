<?php

namespace Catalog\MitsubishiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function catalogsListAction()
    {
        $catalogsList = $this->get('catalog_mitsubishi.repository.models')->getCatalogsList();

        return $this->render('CatalogMitsubishiBundle:Default:catalogs_list.html.twig', array('catalogsList'=>$catalogsList));
    }

    public function vncsListAction(Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $catalog = $request->get('catalog');
            $vncsList = $this->get('catalog_mitsubishi.repository.models')->getCatalogNumsListByCatalog($catalog);
            return $this->render('CatalogMitsubishiBundle:Default:vncs_list.html.twig', array('vncsList'=>$vncsList, 'catalog'=>$catalog));
        }
    }

    public function modelsListAction(Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $catalog = $request->get('catalog');
            $catalogNum = $request->get('catalogNum');
            $modelsList = $this->get('catalog_mitsubishi.repository.models')->getModelsListByCatalogNum($catalog, $catalogNum);
            return $this->render('CatalogMitsubishiBundle:Default:models_list.html.twig', array(
                'modelsList'=>$modelsList,
                'catalog'=>$catalog,
                'catalogNum'=>$catalogNum));
        }
    }

    public function classificationsListAction($catalog, $catalogNum, $model)
    {
        $classificationsList = $this->get('catalog_mitsubishi.repository.models')->getClassificationsListByModel($catalog, $catalogNum, $model);

        return $this->render('CatalogMitsubishiBundle:Default:classifications_list.html.twig', array(
            'classificationsList'=>$classificationsList,
            'catalog'=>$catalog,
            'catalogNum'=>$catalogNum,
            'model'=>$model));
    }

    public function mainGroupsListAction($catalog, $catalogNum, $model, $classification)
    {
        $illustration = $this->get('catalog_mitsubishi.repository.mgroup')->getMgroupsIllustration($catalog, $catalogNum, $model, $classification);
        $mgroups = $this->get('catalog_mitsubishi.repository.mgroup')->getMgroupsByModel($catalog, $catalogNum, $model, $classification);
        $mgroup = $this->get('catalog_mitsubishi.repository.pictures')->getGroupsByPicture($catalog, $illustration);

        return $this->render('CatalogMitsubishiBundle:Default:main_groups_list.html.twig', array(
            'illustration'=>$illustration,
            'mgroup'=>$mgroup,
            'mgroups'=>$mgroups,
            'catalog'=>$catalog,
            'catalogNum'=>$catalogNum,
            'model'=>$model,
            'classification'=>$classification
        ));
    }

    public function subGroupsListAction($catalog, $catalogNum, $model, $mainGroup, $classification)
    {
        $sgroups = $this->get('catalog_mitsubishi.repository.sgroup')->getSgroupsByMgroup($catalog, $catalogNum, $model, $mainGroup, $classification);

        return $this->render('CatalogMitsubishiBundle:Default:sub_groups_list.html.twig', array(
            'sgroups'=>$sgroups,
            'catalog'=>$catalog,
            'catalogNum'=>$catalogNum,
            'model'=>$model,
            'mainGroup'=>$mainGroup,
            'classification'=>$classification
        ));
    }

    public function bGroupsListAction($catalog, $catalogNum, $model, $mainGroup, $subGroup, $classification)
    {
        $bgroups = $this->get('catalog_mitsubishi.repository.bgroup')->getBgroupsBySgroup($catalog, $catalogNum, $model, $mainGroup, $subGroup, $classification);

        return $this->render('CatalogMitsubishiBundle:Default:b_groups_list.html.twig', array(
            'bgroups'=>$bgroups,
            'catalog'=>$catalog,
            'catalogNum'=>$catalogNum,
            'model'=>$model,
            'mainGroup'=>$mainGroup,
            'classification'=>$classification
        ));
    }

    public function pncsListAction($catalog, $illustration)
    {
        $pncs = $this->get('catalog_mitsubishi.repository.pictures')->getGroupsByPicture($catalog, $illustration);

        return $this->render('CatalogMitsubishiBundle:Default:pncs_list.html.twig', array(
            'pncs'=>$pncs,
            'catalog'=>$catalog,
            'illustration'=>$illustration
        ));
    }
}
