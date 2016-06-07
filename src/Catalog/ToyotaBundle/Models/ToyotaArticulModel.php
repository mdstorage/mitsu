<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\ToyotaBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\ToyotaBundle\Components\ToyotaConstants;

class ToyotaArticulModel extends ToyotaCatalogModel{

    public function getArticulRegions($articulCode){



        $sql = "
         SELECT *
          FROM hnb
          WHERE hnb.part_code = :articulCode
         ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode',  $articulCode);
        $query->execute();

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $index => $value)
        {
            $regions[] = $value['catalog'];
        }

        return $regions;

    }

    public function getArticulModels ($articul, $regionCode)
    {



        $sql = "
         SELECT shamei.model_name
          FROM hnb
          INNER JOIN shamei ON (shamei.catalog = hnb.catalog AND shamei.catalog_code = hnb.catalog_code)

          WHERE hnb.part_code = :articulCode
          AND hnb.catalog = :regionCode
         ";



        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);

        $query->execute();

        $aData = $query->fetchAll();

		
		$models = array();

        foreach($aData as $item)
        {
            $models[] = urlencode($item['model_name']);

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        $sql = "
         SELECT shamei.catalog_code
          FROM hnb
          INNER JOIN shamei ON (shamei.catalog = hnb.catalog AND shamei.catalog_code = hnb.catalog_code AND shamei.model_name = :modelCode)
          WHERE hnb.part_code = :articulCode
          AND hnb.catalog = :regionCode

         ";

        $query = $this->conn->prepare($sql);
            $query->bindValue('articulCode', $articul);
            $query->bindValue('regionCode', $regionCode);
            $query->bindValue('modelCode', $modelCode);


        $query->execute();

        $aData = $query->fetchAll();
        $modifications = array();

        foreach($aData as $item)
        {
            $modifications[] = $item['catalog_code'];

        }

        return array_unique($modifications);


    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {

        $modelCode = urldecode($modelCode);

        $sql = "
         SELECT kpt.compl_code
          FROM hnb
          INNER JOIN shamei ON (shamei.catalog = hnb.catalog AND shamei.catalog_code = hnb.catalog_code AND shamei.model_name = :modelCode)
          INNER JOIN kpt ON (kpt.catalog = hnb.catalog AND kpt.catalog_code = hnb.catalog_code AND hnb.add_desc LIKE CONCAT('%', kpt.ipic_code, '%'))
          WHERE hnb.part_code = :articulCode
          AND hnb.catalog = :regionCode
          AND hnb.catalog_code = :modificationCode
          AND hnb.field_type = 1

         ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('modificationCode', $modificationCode);

        $query->execute();

        $aData = $query->fetchAll();




            $complectations = array();

            foreach ($aData as $item) {

                    $complectations[] = $item['compl_code'];

            }


        return array_unique($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $modelCode = urldecode($modelCode);

        $sql = "
         SELECT bzi.part_group
          FROM hnb
          INNER JOIN shamei ON (shamei.catalog = hnb.catalog AND shamei.catalog_code = hnb.catalog_code AND shamei.model_name = :modelCode)
          INNER JOIN img_nums ON (img_nums.catalog = hnb.catalog and img_nums.disk = shamei.rec_num AND img_nums.number = hnb.pnc)
          INNER JOIN bzi ON (bzi.catalog = img_nums.catalog AND bzi.catalog_code = hnb.catalog_code AND bzi.pic_code = img_nums.pic_code)
          WHERE hnb.part_code = :articulCode
          AND hnb.catalog = :regionCode
          AND hnb.catalog_code = :modificationCode
          AND hnb.field_type = 1

         ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('modificationCode', $modificationCode);

        $query->execute();

        $aData = $query->fetchAll();

        var_dump($aData); die;



        $groups = array();

        foreach($aData as $item)
		{

			$groups[]= $item['PICGROUP'];

		}


        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");

        if ($regionCode != 'JP')
        {
            $sql = "
        SELECT gsecloc_all.FIGURE
        FROM catalog
        LEFT JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        LEFT JOIN gsecloc_all ON (gsecloc_all.CATALOG = catalog.CATALOG AND gsecloc_all.MDLDIR = catalog.MDLDIR and gsecloc_all.FIGURE = SUBSTRING(pcodenes.FIGURE,1,3)
        AND gsecloc_all.PICGROUP = :groupCode)
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR

        ";
        }

        else{
            $sql = "
        SELECT esecloc_jp.FIGURE
        FROM catalog
        LEFT JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        LEFT JOIN esecloc_jp ON (esecloc_jp.CATALOG = catalog.CATALOG AND esecloc_jp.MDLDIR = catalog.MDLDIR and esecloc_jp.FIGURE = SUBSTRING(pcodenes.FIGURE,1,3)
        AND esecloc_jp.PICGROUP = :groupCode)
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR

        ";
        }



        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('MDLDIR', $MDLDIR);

        $query->execute();

        $aData = $query->fetchAll();
    	$subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]= $item['FIGURE'];

        }



        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");


        $sql = "
        SELECT illnote.PIMGSTR
        FROM catalog
        LEFT JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        LEFT JOIN illnote ON (illnote.CATALOG = catalog.CATALOG AND illnote.MDLDIR = catalog.MDLDIR and illnote.FIGURE = pcodenes.FIGURE AND illnote.SECNO = pcodenes.SECNO)
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('MDLDIR', $MDLDIR);

        $query->execute();

        $aData = $query->fetchAll();

	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = strtoupper($item['PIMGSTR']);


        }

		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {


        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");

        $sql = "
        SELECT PARTCODE
        FROM catalog
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('MDLDIR', $MDLDIR);

        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {

                $pncs[] = $item['PARTCODE'];


        }

        return array_unique($pncs);
    }

} 