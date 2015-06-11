<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\SuzukiBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\SuzukiBundle\Components\SuzukiConstants;

class SuzukiArticulModel extends SuzukiCatalogModel{

    public function getArticulRegions($articulCode){

        $sql = "
        SELECT *
        FROM parts
        WHERE PRTNAME = :articulCode
        OR PARTNUM = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();
        $regions = array();
        if ($aData)
        {
        
          
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT CATNAME, ABBREV
        FROM model_series
        WHERE CATCODE = :CATCODE
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('CATCODE', $item['CATCODE']);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll(); 
		}
		
		foreach($aDataCatalog as $item)
        {
        	foreach($item as $item1)
        	{
				$regions[] = trim($item1['ABBREV']);
			}
        		
        		
		}
		}
      
		
        return array_unique($regions);

    }

    public function getArticulModels($articul, $regionCode)
    {
    	$sql = "
        SELECT CATCODE
        FROM parts
        WHERE PRTNAME = :articulCode
        OR PARTNUM = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT CATNAME, ABBREV
        FROM model_series
        WHERE CATCODE = :CATCODE
        AND ABBREV = :regionCode
        
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('CATCODE', $item['CATCODE']);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}
		
		$models = array();
		
		foreach($aDataCatalog as $item)
		{
			foreach($item as $item1)
			{
			$aOrigin[]=$item1['CATNAME'];	
			}
			
		}
		
		foreach(array_unique($aOrigin) as $item)
        {
		$sqlCatalogRegion = "
        SELECT MODEL
        FROM model_cat_name
        WHERE CATNAME = :CATNAME
        AND ABBREV = :regionCode
        ";

        $query = $this->conn->prepare($sqlCatalogRegion);
        $query->bindValue('CATNAME', $item);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aDataCatalogRegion[] = $query->fetch();	
        
		}
		$models = array();
		foreach($aDataCatalogRegion as $value)
		{
				$models[]=rawurlencode($value['MODEL']);
		}
 
        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode)
    {
        $sql = "
        SELECT CATCODE
        FROM parts
        WHERE PRTNAME = :articulCode
        OR PARTNUM = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT CATNAME
        FROM model_series
        WHERE CATCODE = :CATCODE
        AND ABBREV = :regionCode
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('CATCODE', $item['CATCODE']);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}
		
		$modifications = array();
		foreach($aDataCatalog as $item)
		{
			foreach($item as $item1)
			{
			$modifications[]=$item1['CATNAME'];	
			}
			
		}

        return array_unique($modifications);
    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
    	$sql = "
        SELECT CATCODE
        FROM parts
        WHERE PRTNAME = :articulCode
        OR PARTNUM = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT CATSER, CATCODE
        FROM model_series
        WHERE CATCODE = :CATCODE
        AND ABBREV = :regionCode
        AND CATNAME =:modificationCode 
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('CATCODE', $item['CATCODE']);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}
		
		$complectations = array();
		foreach($aDataCatalog as $item)
		{
			foreach($item as $item1)
			{
			$complectations[]=$item1['CATCODE'].'.'.$item1['CATSER'];	
			}
			
		}
        return ($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $catcode = substr($complectationCode, 0, strrpos($complectationCode, '.'));
		 
        
        $sqlParts = "
        SELECT sec_group
        FROM parts
        WHERE CATCODE = :CATCODE
        AND PRTNAME = :articulCode
        OR PARTNUM = :articulCode
        AND CATCODE = :CATCODE
        ";

        $query = $this->conn->prepare($sqlParts);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('CATCODE', $catcode);
        $query->execute();

        $aDataSecGroups = $query->fetchAll();
       
       $aDataGroups = array();
       foreach($aDataSecGroups as $item)
       {
	   	 $sqlGroups = "
        SELECT pri_group
        FROM sec_groups
        WHERE CATCODE = :CATCODE
        AND sec_group = :sec_group
        ";

        $query = $this->conn->prepare($sqlGroups);
        $query->bindValue('CATCODE', $catcode);
        $query->bindValue('sec_group', $item['sec_group']);
        $query->execute();

        $aDataGroups[] = $query->fetchAll();
	   } 
    	$groups = array();

        foreach($aDataGroups as $item)
		{
			foreach($item as $item1)
			{
			$groups[]=$item1['pri_group'];	
			}
			
		}
        return array_unique($groups);
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $catcode = substr($complectationCode, 0, strrpos($complectationCode, '.'));
        
        $sqlParts = "
        SELECT sec_group
        FROM parts
        WHERE 
        CATCODE = :CATCODE
        AND PRTNAME = :articulCode
        OR PARTNUM = :articulCode
        AND CATCODE = :CATCODE
        ";

        $query = $this->conn->prepare($sqlParts);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('CATCODE', $catcode);
        $query->execute();

        $aDataSecGroups = $query->fetchAll();
        
        $subgroups = array();
        foreach($aDataSecGroups as $item)
        {
			 $subgroups[] = $item['sec_group'];
		}
    	return array_unique($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
       $catcode = substr($complectationCode, 0, strrpos($complectationCode, '.'));
        
        $aSecGroups = array();
  /*     foreach ($aData as $item)*/
       {
	    $sqlSecGroups = "
        SELECT *
        FROM sec_groups
        WHERE CATCODE = :dataCatcode
        AND sec_group = :subGroupCode
        ";
        
    	$query = $this->conn->prepare($sqlSecGroups);
        $query->bindValue('dataCatcode', $catcode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aSecGroups[] = $query->fetch();
	   }
	   foreach ($aSecGroups as $item)
	   {
	   	$sqlSchemas = "
        SELECT *
        FROM image_cat
        WHERE IMAGE_NAME = :image_full 
        ";
        
        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('image_full', 'SW'.$catcode.str_pad($item['FIGNUM_LJ']*10, 4, "0", STR_PAD_LEFT));
        $query->execute();

        $aSchemas[] = $query->fetch();
	   	
	   }
	   
	   $schemas = array();
	   
	    foreach ($aSchemas as $item)
	    {
			$schemas[] = $item['CRC'];
		}   

        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
    	$catcode = substr($complectationCode, 0, strrpos($complectationCode, '.'));
        
        $sqlPnc = "
        SELECT KEYNUM1
        FROM parts
        WHERE CATCODE = :CATCODE
        AND sec_group = :sec_group
        AND PRTNAME = :PRTNAME
        OR PARTNUM = :PRTNAME
        AND CATCODE = :CATCODE
        AND sec_group = :sec_group
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('CATCODE', $catcode);
        $query->bindValue('sec_group', $subGroupCode);
        $query->bindValue('PRTNAME', $articul);
        $query->execute();
$pncs = array();
        $pncs = $this->array_column($query->fetchAll(), 'KEYNUM1');

        return $pncs; 
    }
} 