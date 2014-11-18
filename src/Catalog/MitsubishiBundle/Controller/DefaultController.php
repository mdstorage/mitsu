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

        setcookie('prodDate', '');

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

    public function findVinAction(Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $vin = $request->get('vin');
            if (isset($vin)){
                $byVin = $this->get('catalog_mitsubishi.repository.vin')->findByVin($vin);

                return $this->byVinProcessing($byVin, $vin, "", "");
            }
        }
    }

    public function findFrameAction(Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $frame1 = $request->get('frame1');
            $frame2 = $request->get('frame2');
            if (isset($frame1) && isset($frame2)){
                $byVin = $this->get('catalog_mitsubishi.repository.vin')->findByVin("", "", $frame1, $frame2);
                return $this->byVinProcessing($byVin, "", $frame1, $frame2);
            }
        }
    }

    private function byVinProcessing($byVin, $vin = "", $frame1 = "", $frame2 = "")
    {
        if($byVin){
            while ($byVin['xref'] !== ""){
                $byVin = $this->get('catalog_mitsubishi.repository.vin')->findByVin($vin, $byVin['xref'], $frame1, $frame2);
            }
            $catalogNum = $this->get('catalog_mitsubishi.repository.models')->getCatalogNumByModelClassification($byVin['catalog'], $byVin['model'], $byVin['classification']);
            $modelName = $this->get('catalog_mitsubishi.repository.models')->getModelNameByModel($byVin['catalog'], $catalogNum['catalogNum'], $byVin['model']);
            $classificationDesc = $this->get('catalog_mitsubishi.repository.models')->getClassificationDesc($byVin['catalog'], $catalogNum['catalogNum'], $byVin['model'], $byVin['classification']);
            setcookie('descCatalogNum', '');
            setcookie('descCatalogNum', $catalogNum['descEn'], 0, '/');
            setcookie($byVin['catalog'].$catalogNum['catalogNum'], json_encode(array($byVin['model']=>$modelName)), 0, '/');
            setcookie('classificationsArray', json_encode(array($byVin['classification']=>$classificationDesc)), 0, '/');
            setcookie('prodDate', $byVin['prodDate'], 0, '/');
            return $this->render('CatalogMitsubishiBundle:Default:find_vin.html.twig', array(
                'byVin'=>$byVin,
                'catalogNum'=>$catalogNum,
                'modelName'=>$modelName,
                'classificationDesc'=>$classificationDesc
            ));
        } else {
            return new Response("По Вашему запросу ничего не найдено.");
        }
    }

    public function modelsListAction(Request $request)
    {
        if ($request->isXmlHttpRequest()){
            $catalog = $request->get('catalog');
            $catalogNum = $request->get('catalogNum');

            $modelsList = $this->get('catalog_mitsubishi.repository.models')->getModelsListByCatalogNum($catalog, $catalogNum);

            $descEnCatalogNum = $this->get('catalog_mitsubishi.repository.modeldesc')->getCatalogNumDesc($catalog, $catalogNum);

            setcookie('descCatalogNum', '');
            setcookie('descCatalogNum', $descEnCatalogNum, 0, '/');

            $modelsArray = array();
            foreach($modelsList as $item){
                $modelsArray[$item['model']] = $item['descEn'];
            }
            setcookie($catalog.$catalogNum, json_encode($modelsArray), 0, '/');

            return $this->render('CatalogMitsubishiBundle:Default:models_list.html.twig', array(
                'modelsList'=>$modelsList,
                'catalog'=>$catalog,
                'catalogNum'=>$catalogNum));
        }
    }

    /**
     * @param $catalog
     * @param $catalogNum
     * @param $model
     * @return Response
     */
    public function classificationsListAction($catalog, $catalogNum, $model)
    {
        $classificationsList = $this->get('catalog_mitsubishi.repository.models')->getClassificationsListByModel($catalog, $catalogNum, $model);

        $classificationsArray = array();
        foreach($classificationsList as $item){
            $classificationsArray[$item['classification']] = $item['descEn'];
        }
        setcookie('classificationsArray', json_encode($classificationsArray), 0, '/');

        return $this->render('CatalogMitsubishiBundle:Default:classifications_list.html.twig', array(
            'classificationsList'=>$classificationsList,
            'catalog'=>$catalog,
            'catalogNum'=>$catalogNum,
            'model'=>$model
        ));
    }

    public function mainGroupsListAction($catalog, $catalogNum, $model, $classification)
    {
        $illustration = $this->get('catalog_mitsubishi.repository.mgroup')->getMgroupsIllustration($catalog, $catalogNum, $model, $classification);
        $mgroups = $this->get('catalog_mitsubishi.repository.mgroup')->getMgroupsByModel($catalog, $catalogNum, $model, $classification);
        $mgroup = $this->get('catalog_mitsubishi.repository.pictures')->getGroupsByPicture($catalog, $illustration);

        setcookie('mgroups', json_encode($mgroups), 0, '/');

        $descEnClassification = $this->get('catalog_mitsubishi.repository.models')->getClassificationDesc($catalog, $catalogNum, $model, $classification);

        setcookie('descClassification', '');
        setcookie('descClassification', $descEnClassification, 0, '/');

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
        $descSgroup = $this->get('catalog_mitsubishi.repository.sgroup')->getSgroupDesc($catalog, $catalogNum, $model, $mainGroup, $subGroup);

        return $this->render('CatalogMitsubishiBundle:Default:b_groups_list.html.twig', array(
            'bgroups'=>$bgroups,
            'catalog'=>$catalog,
            'catalogNum'=>$catalogNum,
            'model'=>$model,
            'mainGroup'=>$mainGroup,
            'subGroup'=>$subGroup,
            'descSubGroup'=>$descSgroup,
            'classification'=>$classification
        ));
    }

    public function pncsListAction($catalog, $model, $catalogNum, $mainGroup, $subGroup, $classification, $illustration)
    {
        $pncCoords = $this->get('catalog_mitsubishi.repository.pictures')->getGroupsByPicture($catalog, $illustration);
        $pncs = $this->get('catalog_mitsubishi.repository.partgroup')->getPncsByModel($catalog, $model, $mainGroup, $subGroup, $classification, isset($_COOKIE['prodDate'])?:"");

        $pncCoordsCodes = array();
        foreach($pncCoords as $code){
            $pncCoordsCodes[$code['code']] = $code['code'];
        }

        $descSgroup = $this->get('catalog_mitsubishi.repository.sgroup')->getSgroupDesc($catalog, $catalogNum, $model, $mainGroup, $subGroup);

        return $this->render('CatalogMitsubishiBundle:Default:pncs_list.html.twig', array(
            'pncCoords'=>$pncCoords,
            'pncs'=>$pncs,
            'catalog'=>$catalog,
            'catalogNum'=>$catalogNum,
            'model'=>$model,
            'mainGroup'=>$mainGroup,
            'subGroup'=>$subGroup,
            'classification'=>$classification,
            'illustration'=>$illustration,
            'descSubGroup'=>$descSgroup,
            'pncCoordsCodes'=>$pncCoordsCodes
        ));
    }
}
