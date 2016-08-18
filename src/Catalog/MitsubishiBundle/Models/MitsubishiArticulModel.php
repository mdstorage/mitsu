<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\MitsubishiBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\MitsubishiBundle\Components\MitsubishiConstants;

class MitsubishiArticulModel extends MitsubishiCatalogModel{

    public function getArticulRegions($articulCode)
    {
        $sql = "
        SELECT catalog
        FROM part_catalog
        WHERE PartNumber = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();


        $regions = array();

        foreach($aData as $index => $value)
        {
           $regions[] = $value['catalog'];
        }

        return array_unique($regions);

    }

    public function getArticulModels ($articul, $regionCode)
    {

        $sql = "
        SELECT m.Catalog_Num
        FROM models m
        INNER JOIN part_catalog p ON (p.catalog = m.catalog AND p.Model = m.Model AND (p.Classification = m.Classification OR p.Classification = '') AND p.PartNumber = :articulCode)
        WHERE m.catalog = :regionCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);

        $query->execute();

        $aData = $query->fetchAll();

        $models = array();

        foreach($aData as $item){

            $models[] = $item['Catalog_Num'];

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {

        $sql = "
        SELECT p.Model
        FROM models m
        INNER JOIN part_catalog p ON (p.catalog = m.catalog AND p.Model = m.Model AND (p.Classification = m.Classification OR p.Classification = '') AND p.PartNumber = :articulCode)
        WHERE m.catalog = :regionCode
        AND m.Catalog_Num = :modelCode
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();

        $first_year = array();
        $last_year = array();

        foreach($aData as $item)
        {
                $modifications[] = $item['Model'];
        }


        return array_unique($modifications);


    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
        $sql = "
        SELECT m.Classification
        FROM models m
        INNER JOIN part_catalog p ON (p.catalog = m.catalog AND p.Model = m.Model AND (p.Classification = m.Classification OR p.Classification = '') AND p.PartNumber = :articulCode)
        WHERE m.catalog = :regionCode
        AND m.Catalog_Num = :modelCode
        AND m.Model = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

            $complectations = array();

        foreach($aData as $item){

            $complectations[] = $item['Classification'];

        }


        return array_unique($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $sql = "
        SELECT p.MainGroup
        FROM models m
        INNER JOIN part_catalog p ON (p.catalog = m.catalog AND p.Model = m.Model AND (p.Classification = m.Classification OR p.Classification = '') AND p.PartNumber = :articulCode)
        WHERE m.catalog = :regionCode
        AND m.Catalog_Num = :modelCode
        AND m.Model = :modificationCode
        AND m.Classification = :complectationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{

			$groups[]= $item['MainGroup'];

			
		}


        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {

        $sql = "
        SELECT p.SubGroup
        FROM models m
        INNER JOIN part_catalog p ON (p.catalog = m.catalog AND p.Model = m.Model AND (p.Classification = m.Classification OR p.Classification = '') AND p.PartNumber = :articulCode
        AND p.MainGroup = :groupCode)
        WHERE m.catalog = :regionCode
        AND m.Catalog_Num = :modelCode
        AND m.Model = :modificationCode
        AND m.Classification = :complectationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('groupCode',  $groupCode);

        $query->execute();

        $aData = $query->fetchAll();

    	$subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]= $item['SubGroup'];

        }

        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $sql = "
        SELECT
          bg.Illustration as illustration
        FROM
          `bgroup` bg

        INNER JOIN part_catalog p ON (p.catalog = bg.catalog AND p.Model = bg.Model AND (p.Classification = bg.Classicfication OR p.Classification = '') AND p.PartNumber = :articulCode)
        WHERE bg.catalog = :regionCode
        AND bg.Catalog_Num = :modelCode
        AND bg.Model = :modificationCode
        AND bg.MainGroup = :groupCode
        AND bg.SubGroup = :subgroupCode
        AND (bg.Classicfication = :complectationCode OR bg.Classicfication = '')
        GROUP BY bg.Illustration

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('subgroupCode',  $subGroupCode);

        $query->execute();

        $aData = $query->fetchAll();
	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = $item['illustration'];

        }
		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {

        $sql = "
        SELECT
          p.PNC
        FROM
          `bgroup` bg
        INNER JOIN part_catalog p ON (p.catalog = bg.catalog AND p.Model = bg.Model AND (p.Classification = bg.Classicfication OR p.Classification = '') AND p.PartNumber = :articulCode)
        WHERE bg.catalog = :regionCode
        AND bg.Catalog_Num = :modelCode
        AND bg.Model = :modificationCode
        AND bg.MainGroup = :groupCode
        AND bg.SubGroup = :subgroupCode
        AND bg.Illustration = :schemaCode
        AND (bg.Classicfication = :complectationCode OR bg.Classicfication = '')
        GROUP BY p.PNC
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('subgroupCode',  $subGroupCode);
        $query->bindValue('schemaCode',  $schemaCode);
        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        
        foreach($aData as $item)
        {
                $pncs[] = $item['PNC'];

        }

        return array_unique($pncs);
    }
} 