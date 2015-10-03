<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\HuyndaiBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\HuyndaiBundle\Components\HuyndaiConstants;

class HuyndaiArticulModel extends HuyndaiCatalogModel{

    public function getArticulRegions($articulCode){

        
        $sql = "
        select * from cats_table
        where detail_code = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();
        $regions = array();
        $aDataCatalog = array();
        
        if ($aData)
        {
                  
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT data_regions
        FROM hywc
        WHERE cutup_code = :cutup_code
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('cutup_code', $item['catalog_code']);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}


		foreach($aDataCatalog as $item)
        {
        	foreach($item as $item1)
        	{
				$regions = explode("|", $item1['data_regions']);
                foreach($regions as $index => $value)
                {
                    if ($value == '')
                    {
                        unset($regions[$index]);
                    }
                    $reg[] = $value;
                }

			}
        		
        		
		}
		}

        return array_unique($reg);

    }

    public function getArticulModels($articul, $regionCode)
    {

        $sql = "
        select * from cats_table
        where detail_code = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataCatalog = array();

        if ($aData)
        {

            foreach($aData as $item)
            {
                $sqlCatalog = "
        SELECT family
        FROM hywc
        WHERE cutup_code = :cutup_code
        ";

                $query = $this->conn->prepare($sqlCatalog);
                $query->bindValue('cutup_code', $item['catalog_code']);
                $query->execute();

                $aDataCatalog[] = $query->fetchAll();
            }

		
		$models = array();
		
		foreach($aDataCatalog as $item)
		{
			foreach($item as $item1)
			{
			$models[]=($item1['family']);
			}
			
		}
	    }
 
        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $sql = "
        select * from cats_table
        where detail_code = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataCatalog = array();

        if ($aData)
        {

            foreach ($aData as $item) {
                $sqlCatalog = "
        SELECT catalog_code, catalog_folder
        FROM hywc
        WHERE cutup_code = :cutup_code
        ";

                $query = $this->conn->prepare($sqlCatalog);
                $query->bindValue('cutup_code', $item['catalog_code']);
                $query->execute();

                $aDataCatalog[] = $query->fetchAll();
            }

            $modifications = array();
            foreach ($aDataCatalog as $item) {
                foreach ($item as $item1) {
                    $modifications[] = $item1['catalog_code'].'_'.$item1['catalog_folder'];
                }

            }
        }

        return array_unique($modifications);
    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        select * from cats_table
        where detail_code = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataCatalog = array();

        if ($aData)
        {

            foreach ($aData as $item) {
                $sqlCatalog = "
        SELECT model_index
        FROM vin_model
        WHERE model =:modificationCode
        ";

                $query = $this->conn->prepare($sqlCatalog);
                $query->bindValue('modificationCode', $modificationCode);
                $query->execute();

                $aDataCatalog[] = $query->fetchAll();
            }


            $complectations = array();

            foreach ($aDataCatalog as $item2) {
                foreach ($item2 as $item1) {
                    $complectations[] = $item1['model_index'];
                }

            }
        }
		
		
        return ($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $sql = "
        SELECT main_part
        FROM cats_table
        WHERE detail_code = :articul

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{
			foreach($item as $item1)
			{
			$groups[]=$item1;
			}
			
		}
        return array_unique($groups);
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $sql = "
        SELECT compl_name
        FROM cats_table
        WHERE detail_code = :articul

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->execute();

        $aDataSecGroups = $query->fetchAll();

        foreach ($aDataSecGroups as $item) {
            $sqlCatalog = "
        SELECT sector_name
        FROM cats_map
        WHERE sector_id =:compl_name
        ";

            $query = $this->conn->prepare($sqlCatalog);
            $query->bindValue('compl_name', $item['compl_name']);
            $query->execute();

            $aDataCatalog[] = $query->fetchAll();
        }

        $subgroups = array();
        foreach($aDataCatalog as $item) {
            foreach ($item as $item1) {
                $subgroups[] = $item1['sector_name'];

            }

        }
        return array_unique($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $sql = "
        SELECT compl_name
        FROM cats_table
        WHERE detail_code = :articul

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->execute();

        $aData = $query->fetchAll();
	   
	   $schemas = array();
        foreach($aData as $item) {
            foreach ($item as $item1) {
                $schemas[] = $item1;

            }

        }
		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $sql = "
        SELECT detail_pnc
        FROM cats_table
        WHERE detail_code = :articul

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {
            foreach ($item as $item1) {
                $pncs[] = $item1;

            }

        }
        
        return array_unique($pncs);
    }
} 