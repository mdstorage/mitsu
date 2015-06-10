<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\SuzukiBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\SuzukiBundle\Components\SuzukiConstants;

class SuzukiCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT *
        FROM regions
        GROUP BY ABBREV
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[trim($item['ABBREV'])] = array(Constants::NAME=>$item['REGION'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT NAME
        FROM region_mname
        WHERE ABBREV = :regionCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();
        
        $sql1 = "
        SELECT *
        FROM mname
        ";

        $query = $this->conn->prepare($sql1);
        $query->execute();

        $aData1 = $query->fetchAll();
        $ch = array();
        
        	foreach ($aData1 as $index=>$value)
        {
        	
        	$ch[trim($value['NAME'])] = $index;
     	
		}
	        

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[rawurlencode($item['NAME'])] = array(Constants::NAME=>$item['NAME'], 
            Constants::OPTIONS=>array('option1'=> 'IMAGE'.$ch[$item['NAME']]));
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
    	$modelCode = rawurldecode($modelCode);
        $sql = "
        SELECT *
        FROM model_cat_name
        WHERE ABBREV = :regionCode 
        AND MODEL = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();
        

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['CATNAME']] = array(
                Constants::NAME     => $item['CATNAME'],
                Constants::OPTIONS  => array());
            
        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {        
   /*     $sql = "
        SELECT *
        FROM catalog
        WHERE CATNAME = :modificationCode
        GROUP BY CATSER
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();*/
        
       

         
  /*     foreach($aData as $item) */
       {
       	
       	 
        $sqlDesc = "
        SELECT *
        FROM model_series
        WHERE ABBREV = :regionCode
        AND
        CATNAME = :modificationCode
        ";

        $query = $this->conn->prepare($sqlDesc);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        
        $query->execute();
        $aData1 = $query->fetchAll();
     
        }
        
       
            	
        
foreach ($aData1 as $item1) 
   {     	
        $pieces = explode(",", $item1['E_CODES']); 
        $contries = '';
        $ch = array();
        
         
       foreach($pieces as $index =>$value)	
        {	
        	$sqlAbb = "
        SELECT DEFINITION
        FROM scodes
        WHERE CODE = :value
        ";

        $query = $this->conn->prepare($sqlAbb);
        $query->bindValue('value', 'E'.$value);
        $query->execute();

		
        $aData = $query->fetch();  
        $ch[$value] = $aData['DEFINITION'];
		
		}
		
		
		$countries[$item1['E_CODES']] = implode(", ", array_unique($ch));
		$item1['desc_con'] = $countries[$item1['E_CODES']];
	}
	
	 
	$complectations = array();
        foreach ($aData1 as $item1)
 {
        
            $complectations[$item1['CATCODE'].'.'.$item1['CATSER']] = array(
                Constants::NAME     => $item1['CATSER'],
               Constants::OPTIONS  => array('option1'=> ' E '.str_replace(',', ', ', $item1['E_CODES']).' ('.$countries[$item1['E_CODES']].')',
               								'option2'=>$item1['CATCODE']));//
     
 }
     foreach ($complectations as $index =>$value)
     {
	 	if (($index == '') || ($value == ''))
	 	unset ($complectations[$index]);
	 } 
   
     return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        
        $catcode = substr($complectationCode, 0, strpos($complectationCode, '.'));
        
        
         $sql2 = "
        SELECT *
        FROM pri_groups
        WHERE CATCODE = :CATCODE
        ";

        $query = $this->conn->prepare($sql2);
        $query->bindValue('CATCODE', $catcode);
        $query->execute();
        $aData1 = $query->fetchAll();

        $groups = array();
        foreach($aData1 as $item){
            $groups[$item['pri_group']] = array(
                Constants::NAME     => $item['SECTXT1'],
                Constants::OPTIONS  => array()
            );
        }
        
        
        $numberOfGroups = array('1','2','3','4','5');
        
        foreach($groups as $index => $value)
        {
			if (!in_array($index, $numberOfGroups))
	 	unset ($groups[$index]);
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
    	$catcode = substr($complectationCode, 0, strpos($complectationCode, '.'));
       
        $sqlSecGroups = "
        SELECT *
        FROM sec_groups
        WHERE CATCODE = :dataCatcode
            AND pri_group = :groupCode
            ORDER BY TRIM(sec_group) ASC
        ";
        
    	$query = $this->conn->prepare($sqlSecGroups);
        $query->bindValue('dataCatcode', $catcode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aSecGroups = $query->fetchAll();
        
     

        $subgroups = array();
        foreach($aSecGroups as $item){
            $subgroups[($item['sec_group'])] = array(
                Constants::NAME => $item['FIGTEXT'],
                Constants::OPTIONS => array()
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $catcode = substr($complectationCode, 0, strpos($complectationCode, '.'));
        
        
        $sqlSecGroups = "
        SELECT *
        FROM sec_groups
        WHERE CATCODE = :dataCatcode
            AND pri_group = :groupCode
            AND sec_group = :subGroupCode
        ";
        
    	$query = $this->conn->prepare($sqlSecGroups);
        $query->bindValue('dataCatcode', $catcode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aSecGroups = $query->fetch();
        
        
        $sqlSchemas = "
        SELECT *
        FROM image_cat
        WHERE IMAGE_NAME = :image_full 
        ";
        
        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('image_full', 'SW'.$catcode.str_pad($aSecGroups['FIGNUM_LJ']*10, 4, "0", STR_PAD_LEFT));
        $query->execute();

        $aSchemas = $query->fetch(); 
        
        

        $schemas = array();
        		
		            $schemas[$aSchemas['CRC']] = array(
                    Constants::NAME => $aSchemas['CRC'],
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
    	$catcode = substr($complectationCode, 0, strpos($complectationCode, '.')); 
        
        
        $sqlPnc = "
        SELECT *
        FROM parts
        WHERE CATCODE = :dataCatcode
            AND sec_group = :subGroupCode 
        ";
        
    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('dataCatcode', $catcode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aPncs = $query->fetchAll();
    	$aDataLabels = array();
    	$aDataLabels1 = array();
    	
    	foreach ($aPncs as &$aPnc)
    	{
    		
    	$sqlSchemaLabels = "
        SELECT x,y
        FROM labels
        WHERE image_name = :schemaCode
          AND label =:keynum
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('keynum', $aPnc['KEYNUM1']);
        $query->execute();
        
        $aPnc['NOTE'] = $query->fetchAll();
        
  $aAdd = array();
        foreach ($aPnc['NOTE'] as $index =>$value)
        {
			 	
			 	
				if (in_array($value, $aAdd))
			 	{
					unset ($aPnc['NOTE'][$index]);
				}
			 	$aAdd[] = $value;
			 
			
			
		}

		}
	  
        $pncs = array();
      foreach ($aPncs as $item) {
            {
            	foreach ($item['NOTE'] as $item1)
            	{
            	$pncs[$item['KEYNUM1']][Constants::OPTIONS][Constants::COORDS][$item1['x']] = array(
                    Constants::X1 => floor($item1['x']-10),
                    Constants::Y1 => $item1['y']-10,
                    Constants::X2 => $item1['x']+15,
                    Constants::Y2 => $item1['y']+15);	
            	
            	}
            
            
                
            }
        }
         
        
         foreach ($aPncs as $item) {
         	if ($item['KEYNUM2'] == '00')
         	{
				$pncs[$item['KEYNUM1']][Constants::NAME] = str_replace('.', '', $item['PRTQTY4']);
			}
			else
			{
				 $pncs[$item['KEYNUM1']][Constants::NAME] = str_replace('.', '', $item['PRTNAME']);
			}
           
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
        $catcode = substr($complectationCode, 0, strpos($complectationCode, '.'));  
        
        
        $sqlPnc = "
        SELECT *
        FROM parts
        WHERE CATCODE = :dataCatcode
            AND sec_group = :subGroupCode
            AND KEYNUM1 = :pncCode
        ";
        
    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('dataCatcode', $catcode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pncCode', $pncCode);
        $query->execute();

        $aPncs = $query->fetchAll();
     
        
       
        $articuls = array();
      
        foreach ($aPncs as $item) {
        	 
            if ($item['KEYNUM2'] != '00')
            {
				$articuls[$item['PARTNUM']] = array(
                Constants::NAME =>str_replace('.', '', $item['PRTNAME']),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['PRTQTY1'],
                    'option1' => $item['RMKS']
                )
            );
			}
			else
			{
			$articuls[$item['PRTNAME']] = array(
                Constants::NAME =>str_replace('.', '', $item['PRTQTY4']),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['PRTQTY1'],
                    'option1' => $item['PARTNUM']
                )
            );	
			}
            
        }

        return $articuls;
    }

    
} 