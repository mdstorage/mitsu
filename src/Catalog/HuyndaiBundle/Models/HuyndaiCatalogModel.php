<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\HuyndaiBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\HuyndaiBundle\Components\HuyndaiConstants;

class HuyndaiCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT data_regions
        FROM hywc
        GROUP BY data_regions
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        foreach($aData as $item)
        {

            $pieces = explode("|", $item['data_regions']);
            foreach($pieces as $item1)
            {
                if($item1 != ''){$reg[] = $item1;}
            }

        }

        $regions = array();
        foreach(array_unique($reg) as $item)
        {
            $regions[trim($item)] = array(Constants::NAME=>$item, Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT *
        FROM hywc
        WHERE data_regions LIKE :regionCode
        ORDER BY family
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[($item['family'])] = array(Constants::NAME=>strtoupper($item['family']),
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sql = "
        SELECT *
        FROM hywc
        WHERE data_regions LIKE :regionCode
        AND family = :modelCode
        ORDER BY catalog_name
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['catalog_code'].'_'.$item['catalog_folder']] = array(
                Constants::NAME     => $item['catalog_name'],
                Constants::OPTIONS  => array('option1'=>strtolower($item['catalog_code'])));

        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {   $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $modelCode = rawurldecode($modelCode);
       $sql = "
        SELECT *
        FROM vin_model
        WHERE model =:modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();
             

        $aIndexes = array('body_type', 'engine_capacity', 'engine_type', 'fuel_type', 'transaxle', 'field14');
        foreach($aData as &$item)
        {
        foreach($item as $index => $value)
            {
        if (in_array($index, $aIndexes))
                {
                    $item[str_pad((array_search($index, $aIndexes)+1), 2, "0", STR_PAD_LEFT)] = $value;

                }

		    }
        }

        $complectations = array();

        foreach ($aData as &$item) {
            $aData1 = array();
            $aOptions = array();
            foreach ($item as $index => $value)
            {
                $sql = "
        SELECT ucc_type, ucc_type_code, ucc_code_short
        FROM cats_0_ucc
        WHERE model =:modificationCode
        AND ucc = :value
        AND ucc_type = :index
        ";

                $query = $this->conn->prepare($sql);
                $query->bindValue('modificationCode', $modificationCode);
                $query->bindValue('index', $index);
                $query->bindValue('value', $value);
                $query->execute();

                $aData1[] = $query->fetch();
            }
            foreach ($aData1 as $index1 => $value1)
            {
                if ($value1 == '')
                {
                    unset ($aData1[$index1]);
                }
            }

            $aProm = array();
            foreach ($aData1 as $item1)
            {
                $aProm[$item1['ucc_type']] = $item1;

            }


            foreach ($aProm as &$item2)
            {
                foreach ($item2 as &$item3)
                {

                    $sql = "
                    SELECT lex_name
                    FROM hywlex
                    WHERE lex_code =:item3
                    AND lang = 'EN'
                    ";

                    $query = $this->conn->prepare($sql);
                    $query->bindValue('item3', $item3);
                    $query->execute();
                    $sData2 = $query->fetch();
                    if ($sData2)
                    {
                        $item3 = $sData2['lex_name'];
                    }

                }

            }

            foreach ($aProm as $item4)
            {
                $aOptions[$item['model_index']][] = ($item4['ucc_type_code'].': '.$item4['ucc_code_short']);
            }


            $complectations[$item['model_index']] = array(
                Constants::NAME => $item['model_code'],
                Constants::OPTIONS => array(

                    'option1' => $aOptions[$item['model_index']],
                    Constants::START_DATE   => $item['start_data'],
                    Constants::END_DATE   => $item['finish_data'],
                )
            );
        }

         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
         $sql = "
        SELECT *
        FROM cats_maj
        WHERE CATALOG_NAME = :catCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catCode', $catCode);
        $query->execute();
        $aData = $query->fetchAll();

        $groups = array();

        foreach ($aData as &$item2)
        {
            foreach ($item2 as &$item3)
            {

                $sql = "
                    SELECT lex_name
                    FROM hywlex
                    WHERE lex_code =:item3
                    AND lang = 'EN'
                    ";

                $query = $this->conn->prepare($sql);
                $query->bindValue('item3', $item3);
                $query->execute();
                $sData2 = $query->fetch();
                if ($sData2)
                {
                    $item3 = $sData2['lex_name'];
                }

            }

        }var_dump($aData); die;
        foreach($aData as $item){
            $groups[$item['nplgrp']] = array(
                Constants::NAME     => $item['xplgrp'],
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
        WHERE hmodtyp =:complectationCode
        AND cmodnamepc = :modelCode 
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['hmodtyp'];
    	$NPL = $aData['npl'];
    	
    	    
        $sqlSecGroups = "
        SELECT dba_pblmtt.NPLBLK, dba_pblmtt.NPL, dba_pblokt.NPLBLKEDIT, dba_pbldst.xplblk
		FROM (dba_pblokt INNER JOIN dba_pblmtt ON (dba_pblokt.NPLBLK = dba_pblmtt.NPLBLK) 
		AND (dba_pblokt.NPL = dba_pblmtt.NPL)) INNER JOIN dba_pbldst ON (dba_pblmtt.NPLBLK = dba_pbldst.nplblk) 
		AND (dba_pblmtt.NPL = dba_pbldst.npl)
		WHERE (((dba_pblmtt.NPL)=:NPL) AND (dba_pblokt.NPLGRP=:groupCode) AND ((dba_pblmtt.HMODTYP)=:hmodtyp ))
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
        WHERE hmodtyp =:complectationCode
        AND cmodnamepc = :modelCode 
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetch();
    	$hmodtyp = $aData['hmodtyp'];
    	$NPL = $aData['npl']; 
    	
    	$sqlTableNumber = "
        SELECT disc_no
		FROM dba_contain
		WHERE pl_no =:pl_no
        ";

        $query = $this->conn->prepare($sqlTableNumber);
        $query->bindValue('pl_no', $NPL);
        $query->execute();

        $DiscNumber = $query->fetch();
        
        switch ($DiscNumber['disc_no']){
            case 1:
                $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes1
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp 
        ";
                break;
            case 2:
                $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes2
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp 
        ";
                break;
            case 3:
                $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes3
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp 
        ";
                break;
        } 
        
        
        
    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('NPL', $NPL);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('hmodtyp', $hmodtyp);
        $query->execute();

        $aPncs = $query->fetchAll();
    	
    	foreach ($aPncs as &$aPnc)
    	{
    		
    	$sqlSchemaLabels = "
        SELECT min_x, min_y, max_x, max_y
        FROM dba_hotspots
        WHERE npl = :NPL
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
    	$hmodtyp = $aData['hmodtyp'];
    	$NPL = $aData['npl'];  
        
        
        $sqlTableNumber = "
        SELECT disc_no
		FROM dba_contain
		WHERE pl_no =:pl_no
        ";

        $query = $this->conn->prepare($sqlTableNumber);
        $query->bindValue('pl_no', $NPL);
        $query->execute();

        $DiscNumber = $query->fetch();
        
        switch ($DiscNumber['disc_no']){
            case 1:
                $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes1
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp
            AND nplpartref= :pncCode 
        ";
                break;
            case 2:
                $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes2
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp
            AND nplpartref= :pncCode 
        ";
                break;
            case 3:
                $sqlPnc = "
        SELECT *
        FROM dba_vw_blockpartsmodeltypes3
        WHERE nplblk = :subGroupCode
        	AND npl = :NPL
            AND hmodtyp = :hmodtyp
            AND nplpartref= :pncCode 
        ";
                break;
        } 
        
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