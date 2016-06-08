<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\LexusBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\LexusBundle\Components\LexusConstants;

class LexusArticulModel extends LexusCatalogModel{

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

        $groupCode = array();

        foreach ($aData as $item) {

            switch (substr($item['part_group'], 0, 1)) {
                case 0:
                    $groupCode[] = '1';
                    break;
                case 1:
                    $groupCode[] = '2';
                    break;
                case 2:
                    $groupCode[] = '3';
                    break;
                case 3:
                    $groupCode[] = '4';
                    break;
                case 4:
                    $groupCode[] = '4';
                    break;
                case 5:
                    $groupCode[] = '5';
                    break;
                case 6:
                    $groupCode[] = '5';
                    break;
                case 7:
                    $groupCode[] = '5';
                    break;
                case 8:
                    $groupCode[] = '6';
                    break;
                case 9:
                    $groupCode[] = '6';
                    break;

            }
        }


        return array_unique($groupCode);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $modelCode = urldecode($modelCode);

        $sql = "
         SELECT bzi.part_group
          FROM hnb
          INNER JOIN shamei ON (shamei.catalog = hnb.catalog AND shamei.catalog_code = hnb.catalog_code AND shamei.model_name = :modelCode)
          INNER JOIN img_nums ON (img_nums.catalog = hnb.catalog and img_nums.disk = shamei.rec_num AND img_nums.number = hnb.pnc)
          INNER JOIN bzi ON (bzi.catalog = img_nums.catalog AND bzi.catalog_code = hnb.catalog_code AND bzi.pic_code = img_nums.pic_code
          AND bzi.ipic_code IN
          (SELECT ipic_code
          FROM kpt
          WHERE kpt.catalog = hnb.catalog AND kpt.catalog_code = hnb.catalog_code AND kpt.compl_code = :complectationCode)
          )
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
        $query->bindValue('complectationCode', $complectationCode);


        $query->execute();

        $aData = $query->fetchAll();

    	$subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]= $item['part_group'];

        }


        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $modelCode = urldecode($modelCode);

        $sql = "
          SELECT bzi.pic_code
          FROM hnb
          INNER JOIN shamei ON (shamei.catalog = hnb.catalog AND shamei.catalog_code = hnb.catalog_code AND shamei.model_name = :modelCode)
          INNER JOIN img_nums ON (img_nums.catalog = hnb.catalog and img_nums.disk = shamei.rec_num AND img_nums.number = hnb.pnc)
          INNER JOIN bzi ON (bzi.catalog = img_nums.catalog AND bzi.catalog_code = hnb.catalog_code AND bzi.pic_code = img_nums.pic_code AND bzi.part_group = :subGroupCode
          AND bzi.ipic_code IN
          (SELECT ipic_code
          FROM kpt
          WHERE kpt.catalog = hnb.catalog AND kpt.catalog_code = hnb.catalog_code AND kpt.compl_code = :complectationCode)
          )
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
        $query->bindValue('complectationCode', $complectationCode);
        $query->bindValue('subGroupCode', $subGroupCode);

        $query->execute();

        $aData = $query->fetchAll();


	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = strtoupper($item['pic_code']);

        }

        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)

     {

         $modelCode = urldecode($modelCode);

         $sql = "
          SELECT hnb.pnc
          FROM hnb
          INNER JOIN shamei ON (shamei.catalog = hnb.catalog AND shamei.catalog_code = hnb.catalog_code AND shamei.model_name = :modelCode)
          INNER JOIN img_nums ON (img_nums.catalog = hnb.catalog and img_nums.disk = shamei.rec_num AND img_nums.number = hnb.pnc AND img_nums.pic_code = :schemaCode)
          INNER JOIN bzi ON (bzi.catalog = img_nums.catalog AND bzi.catalog_code = hnb.catalog_code AND bzi.pic_code = img_nums.pic_code AND bzi.part_group = :subGroupCode
          AND bzi.ipic_code IN
          (
          SELECT ipic_code
          FROM kpt
          WHERE kpt.catalog = hnb.catalog AND kpt.catalog_code = hnb.catalog_code AND kpt.compl_code = :complectationCode
          )
          )
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
         $query->bindValue('complectationCode', $complectationCode);
         $query->bindValue('subGroupCode', $subGroupCode);
         $query->bindValue('schemaCode', $schemaCode);

         $query->execute();

         $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {

                $pncs[] = $item['pnc'];

        }

        return array_unique($pncs);
     }

} 