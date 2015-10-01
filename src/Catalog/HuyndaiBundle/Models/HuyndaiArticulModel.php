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
				$regions[] = explode("|", $item1['data_regions']);
			}
        		
        		
		}
		}
     print_r(array_unique($regions)); die;
        return array_unique($regions);

    }

    public function getArticulModels($articul, $regionCode)
    {
    	
    	$aData = array();
    	$sql = "
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes1 where npartgenu = :articulCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes2 where npartgenu = :articulCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes3 where npartgenu = :articulCode)
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
        SELECT cmodnamepc
        FROM dba_pmotyt
        WHERE cmftrepc = :regionCode
        AND hmodtyp = :hmodtyp
        AND npl = :npl
        ";

        $query = $this->conn->prepare($sqlCatalog);
        
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('hmodtyp', $item['hmodtyp']);
        $query->bindValue('npl', $item['npl']);
        
        $query->execute();

        $aDataCatalog[] = $query->fetchAll(); 
		}
		
		$models = array();
		
		foreach($aDataCatalog as $item)
		{
			foreach($item as $item1)
			{
			$models[]=rawurlencode($item1['cmodnamepc']);	
			}
			
		}
	}
		
 
        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = rawurldecode($modelCode);
        $sql = "
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes1 where npartgenu = :articulCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes2 where npartgenu = :articulCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes3 where npartgenu = :articulCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT dmodyr
        FROM dba_pmotyt
        WHERE npl = :npl
        AND hmodtyp = :hmodtyp
        AND cmftrepc = :regionCode
        AND cmodnamepc = :modelCode
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('npl', $item['npl']);
        $query->bindValue('hmodtyp', $item['hmodtyp']);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}
		
		$modifications = array();
		foreach($aDataCatalog as $item)
		{
			foreach($item as $item1)
			{
			$modifications[]=$item1['dmodyr'];	
			}
			
		}

        return array_unique($modifications);
    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
    	$modelCode = rawurldecode($modelCode);
    	$sql = "
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes1 where npartgenu = :articulCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes2 where npartgenu = :articulCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes3 where npartgenu = :articulCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT  cmodtypfrm, hmodtyp
        FROM dba_pmotyt
        WHERE npl = :npl
        AND hmodtyp = :hmodtyp
        AND cmftrepc = :regionCode
        AND cmodnamepc = :modelCode
        AND dmodyr = :modificationCode
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('npl', $item['npl']);
        $query->bindValue('hmodtyp', $item['hmodtyp']);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}
		
             
        $complectations = array();
        
		foreach($aDataCatalog as $item2)
		{
			foreach($item2 as $item1)
			{
			$complectations[]=$item1['hmodtyp'];	
			}
			
		}
		
		
        return ($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $modelCode = rawurldecode($modelCode);
        
        $sql = "
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes1 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes2 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes3 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['hmodtyp'];
    	$NPL = $aData['npl'];
        
        $sql = "
        (select nplblk from dba_vw_blockpartsmodeltypes1 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND npartgenu = :articulCode)
        UNION
        (select nplblk from dba_vw_blockpartsmodeltypes2 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND npartgenu = :articulCode)
        UNION
        (select nplblk from dba_vw_blockpartsmodeltypes3 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND npartgenu = :articulCode)
        ";
        

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('npl', $NPL);
        $query->bindValue('hmodtyp', $hmodtyp);
        $query->execute();

        $aDataSecGroups = $query->fetchAll(); 
       
       $aDataGroups = array();
       foreach($aDataSecGroups as $item)
       {
	   	 $sqlGroups = "
        SELECT NPLGRP
        FROM dba_pblokt
        WHERE NPL = :NPL
        AND NPLBLK = :NPLBLK
        ";

        $query = $this->conn->prepare($sqlGroups);
        $query->bindValue('NPL', $NPL);
        $query->bindValue('NPLBLK', $item['nplblk']);
        $query->execute();

        $aDataGroups[] = $query->fetchAll();
	   } 
    	$groups = array();

        foreach($aDataGroups as $item)
		{
			foreach($item as $item1)
			{
			$groups[]=$item1['NPLGRP'];	
			}
			
		}
        return array_unique($groups);
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $sql = "
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes1 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes2 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes3 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['hmodtyp'];
    	$NPL = $aData['npl'];
        
        $sql = "
        (select nplblk from dba_vw_blockpartsmodeltypes1 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND npartgenu = :articulCode)
        UNION
        (select nplblk from dba_vw_blockpartsmodeltypes2 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND npartgenu = :articulCode)
        UNION
        (select nplblk from dba_vw_blockpartsmodeltypes3 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND npartgenu = :articulCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('npl', $NPL);
        $query->bindValue('hmodtyp', $hmodtyp);
        $query->execute();

        $aDataSecGroups = $query->fetchAll();
        
        $subgroups = array();
        foreach($aDataSecGroups as $item)
        {
			 $subgroups[] = $item['nplblk'];
		}
    	return array_unique($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
      $sql = "
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes1 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes2 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes3 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetch();
    	$NPL = $aData['npl'];
	   
	   $schemas = array();
	   
			$schemas[] = $aData['npl'];
		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
    	$sql = "
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes1 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes2 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        UNION
        (select npl, hmodtyp from dba_vw_blockpartsmodeltypes3 where npartgenu = :articulCode AND hmodtyp =:complectationCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetch();
        $NPL = $aData['npl'];
        $hmodtyp = $aData['hmodtyp'];
        
        $sqlPnc = "
        (select nplpartref from dba_vw_blockpartsmodeltypes1 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND nplblk  = :nplblk
        AND npartgenu = :articul)
        UNION
        (select nplpartref from dba_vw_blockpartsmodeltypes2 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND nplblk  = :nplblk
        AND npartgenu = :articul)
        UNION
        (select nplpartref from dba_vw_blockpartsmodeltypes3 where npl = :npl
        AND hmodtyp = :hmodtyp
        AND nplblk  = :nplblk
        AND npartgenu = :articul)
        ";
        
        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('articul', $articul);
        $query->bindValue('npl', $NPL);
        $query->bindValue('nplblk', $subGroupCode);
        $query->bindValue('hmodtyp', $hmodtyp);
        $query->execute();
$pncs = array();
        $pncs = $this->array_column($query->fetchAll(), 'nplpartref');

        return $pncs; 
    }
} 