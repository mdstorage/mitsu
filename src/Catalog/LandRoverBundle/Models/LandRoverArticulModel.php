<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\LandRoverBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\LandRoverBundle\Components\LandRoverConstants;

class LandRoverArticulModel extends LandRoverCatalogModel{

    public function getArticulRegions($articulCode){





        $regions = array();

            $regions = ['EU'];

        return $regions;


    }

    public function getArticulModels ($articul, $regionCode)
    {



            $sql = "
        SELECT lrec.model_id, lrec.engine_type
        FROM mcpart1
        INNER JOIN coordinates_names ON (coordinates_names.num_index = mcpart1.pict_index)
        INNER JOIN lrec ON (lrec.model_id = coordinates_names.model_id)
        WHERE mcpart1.detail_code = :articulCode
        ";



        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);

        $query->execute();

        $aData = $query->fetchAll();

		
		$models = array();

        foreach($aData as $item)
        {
            $models[] = $item['model_id'].'_'.(ctype_alpha($item['engine_type'])?'GC'.$item['engine_type']:$item['engine_type']);

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        if ($regionCode !== 'JP')
        {
            $sql = "
        SELECT cdindex.SHASHU
        FROM catalog
        LEFT JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.ShashuCD = catalog.MDLDIR)
        LEFT JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = destcnt.SHASHU and cdindex.SHASHUKO = :modelCode)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode
        ORDER by SHASHUKO
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('articulCode', $articul);
            $query->bindValue('regionCode', $regionCode);
            $query->bindValue('modelCode', $modelCode);
        }

        else
        {
            $sql = "
        SELECT catalog.CATALOG, cdindex.SHASHU
        FROM catalog
        left JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.ShashuCD = catalog.MDLDIR)
        left JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = destcnt.SHASHU)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode
        ORDER by SHASHU

        ";
            $query = $this->conn->prepare($sql);
            $query->bindValue('articulCode', $articul);
            $query->bindValue('regionCode', $regionCode);

        }



        $query->execute();

        $aData = $query->fetchAll();
        $modifications = array();

        foreach($aData as $item)
        {
            $modifications[] = $item['SHASHU'];

        }

        return array_unique($modifications);


    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {

        $sql = "
        SELECT posname.NNO, posname.MDLDIR, posname.DATA1
        FROM catalog
        LEFT JOIN posname ON (posname.CATALOG = catalog.CATALOG AND posname.MDLDIR = catalog.MDLDIR)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode


        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();




            $complectations = array();

            foreach ($aData as $item) {

                    $complectations[] = str_pad($item['MDLDIR'], 3, "0", STR_PAD_LEFT) . '_' . $item['NNO']. '_' .$item['DATA1'];

            }


        return array_unique($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));

        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));




        $sql = "
           SELECT param2
           FROM mcpart1

           WHERE mcpart1.detail_code = :articulCode
           ";



        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();

        foreach($aData as $item)
		{

			$groups[]= substr(substr(substr($item['param2'], 0, strpos($item['param2'], '!')),-3),0,1);

		}

        return array_unique($groups);

    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));

        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));




        $sql = "
           SELECT param2
           FROM mcpart1

           WHERE mcpart1.detail_code = :articulCode
           ";



        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

    	$subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]= substr(substr($item['param2'], 0, strpos($item['param2'], '!')),-3);


        }



        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));

        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));




        $sql = "
           SELECT pict_index
           FROM mcpart1

           WHERE mcpart1.detail_code = :articulCode AND SUBSTRING_INDEX(param2, '!', 1) LIKE CONCAT('%', :subGroupCode)
           ";



        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();
	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = $item['pict_index'];


        }


        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {


        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));
        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));


        $aPncs = array();


        if (strlen($pictureFolder) == 2) {

            $sql = "

           SELECT ABS(coordinates.label_name) as label_name
           FROM coordinates
           INNER JOIN mcpart1 ON (mcpart1.pict_index = coordinates.num_index
           AND mcpart1.detail_code = :articulCode AND SUBSTRING_INDEX(param2, '!', 1) LIKE CONCAT('%', :subGroupCode)

           AND coordinates.label_name = REPLACE(SUBSTRING_INDEX(mcpart1.param1, '.', 1), '1:', '20')-1 AND coordinates.label_name >= 10)


           WHERE coordinates.num_index = :schemaCode

           UNION

           SELECT ABS(coordinates.label_name) as label_name
           FROM coordinates
           INNER JOIN mcpart1 ON (mcpart1.pict_index = coordinates.num_index
           AND mcpart1.detail_code = :articulCode AND SUBSTRING_INDEX(param2, '!', 1) LIKE CONCAT('%', :subGroupCode)

           AND coordinates.label_name = SUBSTRING_INDEX(mcpart1.param1, '-', 1))

           WHERE coordinates.num_index = :schemaCode
           ORDER BY (1)
           ";
        }

        else {

            $sql = "

           SELECT mcpart3.detail_code as label_name
           FROM mcpart1
           INNER JOIN mcpart3 ON (mcpart3.param1_offset = mcpart1.param1_offset and mcpart3.pict_index = :schemaCode
           AND SUBSTRING_INDEX(mcpart3.param2, '!', 1) LIKE CONCAT('%', :subGroupCode))

           WHERE mcpart1.pict_index = :schemaCode
           AND mcpart1.detail_code = :articulCode AND SUBSTRING_INDEX(mcpart1.param2, '!', 1) LIKE CONCAT('%', :subGroupCode)
           ";
        }

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('subGroupCode', $subGroupCode);


        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {

                $pncs[] = $item['label_name'];


        }


        return array_unique($pncs);
    }

    public function getArticulDesc($articul, $regionCode, $modelCode, $modificationCode)
    {




            $sql = "
        SELECT catalog.REC3
        FROM catalog
        INNER JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = :modificationCode)
        INNER JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.SHASHU = cdindex.SHASHU and destcnt.ShashuCD = catalog.MDLDIR)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode

        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);

        $query->execute();

        $aData = $query->fetchAll();



        return $aData;
    }
} 