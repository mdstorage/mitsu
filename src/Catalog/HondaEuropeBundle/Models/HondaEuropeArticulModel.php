<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\HondaEuropeBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\HondaEuropeBundle\Components\HondaEuropeConstants;

class HondaEuropeArticulModel extends HondaEuropeCatalogModel{

    public function getArticulRegions($articulCode){

        
        $sql = "
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode

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
        SELECT carea
        FROM pmotyt
        WHERE npl = :npl
        AND hmodtyp = :hmodtyp
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('npl', $item['npl']);
        $query->bindValue('hmodtyp', $item['hmodtyp']);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}
		 
		foreach($aDataCatalog as $item)
        {
        	foreach($item as $item1)
        	{
				$regions[] = trim($item1['carea']);
			}
        		
        		
		}
		}
     
        return array_unique($regions);

    }

    public function getArticulModels($articul, $regionCode)
    {
    	
    	$aData = array();
        $sql = "
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode

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
        FROM pmotyt
        WHERE carea = :regionCode
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
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT dmodyr
        FROM pmotyt
        WHERE npl = :npl
        AND hmodtyp = :hmodtyp
        AND carea = :regionCode
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
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT  cmodtypfrm, hmodtyp
        FROM pmotyt
        WHERE npl = :npl
        AND hmodtyp = :hmodtyp
        AND carea = :regionCode
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
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode
        AND pbpmtt.hmodtyp = :complectationCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['hmodtyp'];
    	$NPL = $aData['npl'];
        
        $sql = "
        select nplblk from pblpat, pbpmtt
        where npl = :npl
        and pblpat.hpartplblk = pbpmtt.hpartplblk
        AND pbpmtt.hmodtyp = :hmodtyp
        AND npartgenu = :articulCode
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
        SELECT nplgrp
        FROM pblokt
        WHERE npl = :NPL
        AND nplblk = :NPLBLK
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
			$groups[]=$item1['nplgrp'];
			}
			
		}
        return array_unique($groups);
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {

        $sql = "
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode
        AND pbpmtt.hmodtyp = :complectationCode

        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['hmodtyp'];
    	$NPL = $aData['npl'];

        $sql = "
        select nplblk from pblpat, pbpmtt
        where npl = :npl
        and pblpat.hpartplblk = pbpmtt.hpartplblk
        AND pbpmtt.hmodtyp = :hmodtyp
        AND npartgenu = :articulCode
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
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode
        AND pbpmtt.hmodtyp = :complectationCode

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
        select npl, hmodtyp
        from pblpat, pbpmtt
        where
        pblpat.hpartplblk = pbpmtt.hpartplblk
        and pblpat.npartgenu = :articulCode
        AND pbpmtt.hmodtyp = :complectationCode

        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetch();
        $NPL = $aData['npl'];
        $hmodtyp = $aData['hmodtyp'];
        
        $sqlPnc = "
        select nplpartref from pblpat, pbpmtt
        where npl = :npl
        AND pbpmtt.hmodtyp = :hmodtyp
        AND nplblk  = :nplblk
        AND npartgenu = :articul

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