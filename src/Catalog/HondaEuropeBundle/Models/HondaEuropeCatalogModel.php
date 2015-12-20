<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\HondaEuropeBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\HondaEuropeBundle\Components\HondaEuropeConstants;

class HondaEuropeCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT *
        FROM dba_pmotyt
        GROUP BY cmftrepc
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[trim($item['CAREA'])] = array(Constants::NAME=>$item['CAREA'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT *
        FROM dba_pmotyt
        WHERE CAREA =:regionCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[rawurlencode($item['CMODNAMEPC'])] = array(Constants::NAME=>$item['CMODNAMEPC'],
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
    	$modelCode = rawurldecode($modelCode);
        $sql = "
        SELECT DMODYR
        FROM dba_pmotyt
        WHERE CAREA =:regionCode
        AND CMODNAMEPC = :modelCode
        ORDER BY DMODYR
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();
        

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['DMODYR']] = array(
                Constants::NAME     => $item['DMODYR'],
                Constants::OPTIONS  => array());
            
        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {   $modelCode = rawurldecode($modelCode);     
       $sql = "
        SELECT *
        FROM dba_pmotyt
        WHERE CAREA =:regionCode
        AND CMODNAMEPC = :modelCode
        AND DMODYR = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();
             
        $complectations = array();
        
        foreach($aData as &$item)
        {
            $sqlOptions = "
        SELECT CMNOPT
		FROM dba_pmtmot
		WHERE HMODTYP =:hmodtyp
        ";

            $query = $this->conn->prepare($sqlOptions);
            $query->bindValue('hmodtyp', $item['HMODTYP']);
            $query->execute();

            $aDataOptions = $query->fetchAll();

        
        $aOriginOptions = array();
        
        foreach($aDataOptions as $item1)
         {
		 	$aOriginOptions[] = $item1['CMNOPT'];
		 }
		$aOriginOptionsDesc = array(); 
		foreach (array_unique($aOriginOptions) as $item2)
		{
		$sqlOptionsDesc = "
        SELECT xmnopt, cmnopt
		FROM dba_pmnopt
		WHERE cmnopt =:item
        ";

        $query = $this->conn->prepare($sqlOptionsDesc);
        $query->bindValue('item', $item2);
        $query->execute();
        $aOriginOptionsDesc[] = $query->fetch();
		 	
		 }
		 $aOriginOptionDescs = array();
		foreach($aOriginOptionsDesc as $item3)
         {
		 	$aOriginOptionDescs[] = '('.$item3['cmnopt'].') '.$item3['xmnopt'];
		 	$aOriginOptionCodes[] = $item3['cmnopt'];
		 }
		
		foreach($aOriginOptionDescs as $index => $value)
        {
        	if (($value == '') || ($value == ' ') || ($value == '() '))
        	{
				unset ($aOriginOptionDescs[$index]);
			}
		}
		 
		$comma_separated = implode("; ", $aOriginOptionDescs);
			 	 
        $item['NFRMPF'] = $comma_separated;
       
		}
		/**
		* 
		* Проверка на наличие в опциях ($aOriginOptionCodes) информации о положении руля для фильтрации при отображении
		* Массив кодов опций $aOriginOptionCodes уйдет в вид только тогда, когда в нем присутсвуют и LH (левый руль), и RH (правый руль)
		* То же самое касается информации о трансмиссиях (массив $transmission)
		* 
		*/
		foreach($aOriginOptionCodes as $index => $value)
		{
			if (($value != 'RH') & ($value != 'LH'))
        	{
				unset ($aOriginOptionCodes[$index]);
			}
		}
        $transmission = array(); 
        
       
        foreach($aData as $item)
        {
			$transmission[] = $item['CTRSMTYP'];
			
		}
        foreach($aData as $item){
        		
            $complectations[$item['HMODTYP']] = array(
                Constants::NAME     => $item['CMODTYPFRM'],
                Constants::OPTIONS  => array('option1'=> $item['XCARDRS'],
                							 'option2'=> $item['CTRSMTYP'],
                							 'option3'=> $item['NENGNPF'].' '.$item['xgradefulnam'],
                							 'option4'=> $item['NFRMPF'],
                							 'option5'=> count(array_unique($transmission))>1?array_unique($transmission):'',
                							 'option6'=> count(array_unique($aOriginOptionCodes))>1?array_unique($aOriginOptionCodes):'',
                							 )
            );  
      }
      
     return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
               
         $sql2 = "
        SELECT *
        FROM dba_pgrout
        WHERE CLANGJAP = 2
        ";

        $query = $this->conn->prepare($sql2);
        $query->execute();
        $aData = $query->fetchAll();

        $groups = array();
        foreach($aData as $item){
            $groups[$item['NPLGRP']] = array(
                Constants::NAME     => $item['XPLGRP'],
                Constants::OPTIONS  => array()
            );
        }
        

        return $groups;
    }

    public function getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode)
    {
  /*      $sqlNumPrigroup = "
        SELECT *
        FROM pri_groups_full
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND pri_group = :groupCode
        ";
    	$query = $this->conn->prepare($sqlNumPrigroup);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetch();  
       
        $sqlNumModel = "
        SELECT num_model
        FROM part_images
        WHERE catalog = :regionCode
            AND model_code =:model_code
        GROUP BY num_model
        ";
    	$query = $this->conn->prepare($sqlNumModel);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->execute();

        $aNumModel = $query->fetch();

        $groupSchemas = array();
    /*    foreach ($aData as $item)*//* {
            $groupSchemas[$aData['num_image']] = array(Constants::NAME => $aData['num_image'], Constants::OPTIONS => array(
              Constants::CD => $aData['catalog'].$aData['sub_dir'].$aData['sub_wheel'],
                    	'num_model' => $aNumModel['num_model'],
                        'num_image' => $aData['num_image']
                ));
        }*/
		$groupSchemas = array();
        return $groupSchemas;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
    	$modelCode = rawurldecode($modelCode);
    	$sql = "
        SELECT *
        FROM dba_pmotyt
        WHERE HMODTYP =:complectationCode
        AND CMODNAMEPC = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['HMODTYP'];
    	$NPL = $aData['npl'];
    	
    	    
        $sqlSecGroups = "
        SELECT dba_pblmtt.NPLBLK, dba_pblmtt.NPL, dba_pblokt.NPLBLKEDIT, dba_pbldst.xplblk
		FROM (dba_pblokt INNER JOIN dba_pblmtt ON (dba_pblokt.NPLBLK = dba_pblmtt.NPLBLK) 
		AND (dba_pblokt.NPL = dba_pblmtt.NPL)) INNER JOIN dba_pbldst ON (dba_pblmtt.NPLBLK = dba_pbldst.nplblk) 
		AND (dba_pblmtt.NPL = dba_pbldst.npl)
		WHERE (((dba_pblmtt.NPL)=:NPL) AND (dba_pblokt.NPLGRP=:groupCode) AND (dba_pblmtt.HMODTYP=:hmodtyp) AND CLANGJAP = 2)
        ";
        
    	$query = $this->conn->prepare($sqlSecGroups);
        $query->bindValue('NPL', $NPL);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('hmodtyp', $hmodtyp);
        $query->execute();

        $aSecGroups = $query->fetchAll();
        foreach($aSecGroups as $index => $value)
        {
        	if (($value['xplblk'] == '̨') || ($value['xplblk'] == ''))
        	{
				unset ($aSecGroups[$index]);
			}
		}

        $subgroups = array();
        foreach($aSecGroups as $item){
            $subgroups[($item['NPLBLK'])] = array(
                Constants::NAME => '('.$item['NPLBLKEDIT'].') '.$item['xplblk'],
                Constants::OPTIONS => array()
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $modelCode = rawurldecode($modelCode);
        $sqlSecGroups = "
        SELECT *
        FROM dba_pmotyt
        WHERE hmodtyp =:complectationCode 
        AND cmodnamepc = :modelCode
        ";

        $query = $this->conn->prepare($sqlSecGroups);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aSecGroups = $query->fetch();
        $NPL = $aSecGroups['npl'];
            
        

        $schemas = array();
        		
		            $schemas[$aSecGroups['npl']] = array(
                    Constants::NAME => $aSecGroups['npl'],
                    Constants::OPTIONS => array()
                );
                            
        

        return $schemas;
    }

    public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {
    	 
    	 
        $sqlSchema = "
        SELECT *
        FROM part_images
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND sec_group = :subGroupCode
            AND image_file = :schemaCode
        ";

        $query = $this->conn->prepare($sqlSchema);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schema = array();
        
        foreach($aData as $item){
			
		            $schema[$item['image_file']] = array(
                    Constants::NAME => $item['desc_en'],
                    Constants::OPTIONS => array(
                        Constants::CD => $item['catalog'].$item['sub_dir'].$item['sub_wheel'].$item['num_model'].$item['page'], 
                        'num_model' => $item['num_model']
                    )
                );
            
        }
        

        return $schema;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
        $modelCode = rawurldecode($modelCode);
        $sql = "
        SELECT *
        FROM dba_pmotyt
        WHERE HMODTYP =:complectationCode
        AND CMODNAMEPC = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['HMODTYP'];
    	$NPL = $aData['npl']; 
    	

        $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes1
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp 
        ";

    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('NPL', $NPL);
        $query->bindValue('hmodtyp', $hmodtyp);
        $query->execute();

        $aPncs = $query->fetchAll();
    	
    	foreach ($aPncs as &$aPnc)
    	{
    		
    	$sqlSchemaLabels = "
        SELECT min_x, min_y, max_x, max_y
        FROM dba_hotspots
        WHERE NPL = :NPL
          AND IllustrationNumber =:subGroupCode
          AND PartReferenceNumber =:PartReferenceNumber
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('NPL', $NPL);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('PartReferenceNumber', $aPnc['nplpartref']);
        $query->execute();
        
        $aPnc['clangjap'] = $query->fetchAll();
		}
        $pncs = array();
      foreach ($aPncs as $item) {
            {
            	foreach ($item['clangjap'] as $item1)
            	{
            	$pncs[$item['nplpartref']][Constants::OPTIONS][Constants::COORDS][$item1['min_x']] = array(
                    Constants::X1 => floor(($item1['min_x'])),
                    Constants::Y1 => $item1['min_y'],
                    Constants::X2 => $item1['max_x'],
                    Constants::Y2 => $item1['max_y']);	
            	
            	}
            
            
                
            }
        }
        
        foreach ($aPncs as $item) {
         	
         	
				$pncs[$item['nplpartref']][Constants::NAME] = $item['xpartext'];
			
			
           
        }
         return $pncs;
    }

    public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
     /*   $sqlSchemaLabels = "
        SELECT p.part_code, p.xs, p.ys, p.xe, p.ye
        FROM pictures p
        WHERE p.catalog = :regionCode
          AND p.cd = :cd
          AND p.pic_name = :schemaCode
          AND p.XC26ECHK = 2
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', $cd);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aDataLabels = $query->fetchAll();

        $articuls = array();
        foreach ($aDataLabels as $item) {
            $articuls[$item['part_code']][Constants::NAME] = $item['part_code'];

            $articuls[$item['part_code']][Constants::OPTIONS][Constants::COORDS][] = array(
                Constants::X1 => $item['xs'],
                Constants::Y1 => $item['ys'],
                Constants::X2 => $item['xe'],
                Constants::Y2 => $item['ye'],
            );
        }*/
$articuls = array();
        return $articuls;
    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
       
        $groups = array();
        return $groups;
    }

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode)
    {
        $modelCode = rawurldecode($modelCode);
        $sql = "
        SELECT *
        FROM dba_pmotyt
        WHERE hmodtyp =:complectationCode
        AND cmodnamepc = :modelCode 
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['HMODTYP'];
    	$NPL = $aData['npl'];
        

        $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes1
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp
            AND nplpartref= :pncCode 
        ";
        
    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('NPL', $NPL);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('hmodtyp', $hmodtyp);
        $query->bindValue('pncCode', $pncCode);
        
        $query->execute();

        $aPncs = $query->fetchAll();
     
        
       
        $articuls = array();
      
        foreach ($aPncs as $item) {
        	 
            
            
				$articuls[$item['npartgenu']] = array(
                Constants::NAME =>$item['xpartext'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['xordergun'],
                    'option1' => ''
                )
            );
            
        }

        return $articuls;
    }

    
} 