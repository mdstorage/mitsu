<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\SubaruBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\SubaruBundle\Components\SubaruConstants;

class SubaruArticulModel extends SubaruCatalogModel{

    public function getArticulRegions($articulCode){

        $sql = "
        SELECT catalog, sub_wheel
        FROM part_catalog
        WHERE part_number = :articulCode
        GROUP BY catalog";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[] = $item['catalog'].'_'.$item['sub_wheel'];
        }

        return $regions;

    }

    public function getArticulModels($articul, $regionCode)
    {
    	$sql = "
        SELECT model_code
        FROM part_catalog
        WHERE part_number =:articul
        GROUP BY model_code
        ";
       		
         $query = $this->conn->prepare($sql);
          $query->bindValue('articul', $articul);
        $query->execute();


        $aData = $query->fetchAll();

        $models = $this->array_column($aData, 'model_code'); 

        return $models;
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $sql = "
        SELECT desc_en, change_abb, sdate, edate
        FROM model_changes
        WHERE model_code = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();
        

        $modifications = array();
        foreach($aData as $item){
            $modifications[] = $item['change_abb'].$item['desc_en'];
        }

        return $modifications;
    }
    
    public function getArticulComplectations($articulCode, $regionCode, $modelCode, $modificationCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        $sqlArticul = "
        SELECT body_desc.f1
        FROM part_catalog
        LEFT JOIN body_desc ON body_desc.catalog = part_catalog.catalog
        AND body_desc.model_code = part_catalog.model_code
        WHERE part_catalog.part_number = :articulCode
        AND part_catalog.catalog = :regionCode
        AND part_catalog.sub_wheel = :wheel
        ";



        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articulCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('wheel', $wheel);

        $query->execute();

        $aComplectations = $query->fetchAll();


        $complectations = array();

         foreach  ($aComplectations as $item)
         {
             $complectations[] = $item['f1'];
		 }

        return $complectations;
    }
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlArticul = "

        SELECT part_catalog.pri_group
        FROM part_catalog
        LEFT JOIN body_desc ON body_desc.catalog = part_catalog.catalog
        AND body_desc.model_code = part_catalog.model_code AND body_desc.f1 = :complectationCode
        WHERE part_catalog.part_number = :articulCode
        AND part_catalog.catalog = :regionCode
        AND part_catalog.sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('wheel', $wheel);
        $query->bindValue('complectationCode', $complectationCode);

        $query->execute();
        
        $aArticulDesc = $query->fetchAll();

        $groups = array();

        $groups = $this->array_column($aArticulDesc, 'pri_group');

        return $groups;
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $groupCode)
    {
        $sqlArticul = "
        SELECT sec_group
        FROM part_catalog
        WHERE part_number = :articulCode
        AND catalog = :regionCode
        AND model_code = :modelCode
        AND pri_group = :groupCode
        GROUP BY sec_group
        ";

        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();
        
        $aArticulDesc = $query->fetchAll();
    	$subgroups = array();

        $subgroups = $this->array_column($aArticulDesc, 'sec_group'); 

        return $subgroups;
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $sqlArticul = "
        SELECT page
        FROM part_catalog
        WHERE part_number = :articulCode
        AND catalog = :regionCode
        AND model_code = :modelCode
        AND pri_group = :groupCode
        AND sec_group =:subGroupCode
        ";
        
        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetch();
        
       if ($aData['page']!='')
       { 
       	$sql = "
        SELECT image_file, desc_en
        FROM part_images
        WHERE catalog = :regionCode
        AND model_code = :modelCode
        AND sec_group =:subGroupCode
        AND page =:page
        ";
        
        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('page', $aData['page']);
        $query->execute();
        $aData1 = $query->fetchAll(); 
        }
        else 
        {
        $aNumSchemas = $this->getNumSchemas($articul, $regionCode, $modelCode, $groupCode, $subGroupCode);
        
         
        foreach  ($aNumSchemas as $item)
        {
			
        $sql = "
        SELECT image_file, desc_en
        FROM part_images
        WHERE catalog = :regionCode
        AND model_code = :modelCode
        AND sec_group = :subGroupCode
        AND page = :page
        ";
         
        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('page', $item);
        $query->execute();
        $aData1[] = $query->fetch();
        }  
        }  
       
       
        $schemas = array();
       
        foreach  ($aData1 as $item1)
        {
        	if ((substr_count($item1['desc_en'],'MY')>0)&&(substr_count($item1['desc_en'], substr($modificationCode, 1, 5))!=0)||(substr_count($item1['desc_en'],'MY')==0))
			$schemas[]=$item1['image_file'];
		}
               

        return $schemas;
    }
     public function getNumSchemas($articul, $regionCode, $modelCode, $groupCode, $subGroupCode)
     {
	 	$aArticulPncs = $this->getArticulPncs($articul, $regionCode, $modelCode, $groupCode, $subGroupCode);
	 	
	 	foreach ($aArticulPncs as $item)
	 	{	
	 	$sql = "
        SELECT page
        FROM labels
        WHERE catalog = :regionCode
        AND model_code = :modelCode
        AND sec_group = :subGroupCode
        AND part_code = :part_code
        GROUP BY page
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('part_code', $item);
        $query->execute();
         $groups = array();        
        $aData = $query->fetchAll();
        $groups = $this->array_column($aData, 'page');
        }
       
       return $groups;
	 }
	 
    
     public function getArticulPncs($articul, $regionCode, $modelCode, $groupCode, $subGroupCode)
    {
        $sqlPnc = "
        SELECT part_code
        FROM part_catalog
        WHERE catalog = :regionCode
        AND part_number = :articul 
        AND model_code = :modelCode
        AND pri_group = :groupCode
        AND sec_group = :subGroupCode
        GROUP BY part_code
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('articul', $articul);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();
$pncs = array();
        $pncs = $this->array_column($query->fetchAll(), 'part_code');

        return $pncs; 
    }
} 