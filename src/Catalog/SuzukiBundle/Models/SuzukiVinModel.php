<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\SuzukiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\SuzukiBundle\Components\SuzukiConstants;

class SuzukiVinModel extends SuzukiCatalogModel {

    public function getVinFinderResult($vin)
    {
    	
        $sql = "
        SELECT *
        FROM vin
        WHERE VDS = :VDS
        AND SNUMBER = :SNUMBER
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('VDS', substr($vin,0,9));
        $query->bindValue('SNUMBER', substr($vin,9,8));
        $query->execute();

        $aData = $query->fetch();
        if (!$aData)
        {
			print_r('Ничего не найдено');die;
		}
        
        $sqlColor = "
        SELECT COLORDESC
        FROM color
        WHERE COLOR = :COLOR
        AND CATCODE = :catcode
        
        ";

        $query = $this->conn->prepare($sqlColor);
        $query->bindValue('COLOR', $aData['COLOR']);
        $query->bindValue('catcode', $aData['CATALOG']);
        $query->execute();

        $aColor = $query->fetch();
        
        
        $sqlMMCode = "
        SELECT *
        FROM mmcode
        WHERE CATCODE = :CATCODE
        ";

        $query = $this->conn->prepare($sqlMMCode);
        $query->bindValue('CATCODE', $aData['CATALOG']);
        $query->execute();
        $aMMCode = $query->fetchAll();
        
        $aModelDesc = array();
       $expr = substr($aData['model_code'], 0, 7);
        foreach ($aMMCode as $item)
        {
			 if (($item['VALUE']) && (substr_count(substr($expr, $item['POS']-1, strlen($item['VALUE'])),$item['VALUE'])))
			 {
			 $aModelDesc[]= $item['VALUE'].' - '.$item['MMCODEDESC'].';';
			 }
			  
		}
        
        $sqlModelType = "
        SELECT SNUMBER, MODEL, TYPE
        FROM vinrnge
        WHERE VDS = :VDS
        AND CATALOG = :catcode
        ";

        $query = $this->conn->prepare($sqlModelType);
        $query->bindValue('VDS', substr($vin,0,9));
        $query->bindValue('catcode', $aData['CATALOG']);
        $query->execute();

        $aDataModelAndType = $query->fetchAll();
        
        foreach ($aDataModelAndType as $index => $value)
        {
        	
        	if ($index < count($aDataModelAndType)-1)
        	{
        		$a = $index+1;
        	} 
        	else 
        	{
				$a = $index;
			}
			if ((substr($vin,9,8) > $aDataModelAndType[$index]['SNUMBER']) && (substr($vin,9,8) < $aDataModelAndType[$a]['SNUMBER'])) 
			{
			$a = $index;	
			}
			
		}
		
		if ($aDataModelAndType[$a]['MODEL']!='')
		{
		$sqlModel = "
        SELECT MODEL
        FROM model_cat_name
        WHERE CATNAME = :CATNAME
        ";

        $query = $this->conn->prepare($sqlModel);
        $query->bindValue('CATNAME', $aDataModelAndType[$a]['MODEL']);
        $query->execute();

        $aModel = $query->fetch();
        
        $model = $aModel['MODEL']; 
        
        $sqlModif = "
        SELECT *
        FROM model_series
        WHERE CATNAME = :CATNAME
        AND CATCODE = :CATCODE
        AND E_CODES LIKE :E_CODES
        AND ABBREV <> 'AR'
        ";
        
        $query = $this->conn->prepare($sqlModif);
        $query->bindValue('CATNAME', $aDataModelAndType[$a]['MODEL']);
        $query->bindValue('CATCODE', $aData['CATALOG']);
        $query->bindValue('E_CODES', '%'.substr($aData['model_code'], -2).'%');
        $query->execute();
        $aModif = $query->fetch();
        $modif = $aDataModelAndType[$a]['MODEL'].' '.$aDataModelAndType[$a]['TYPE'].', E '.$aModif['E_CODES'];
        
        $sqlCatalog = "
        SELECT *
        FROM catalog
        WHERE CATCODE = :catcode
        AND CATKEYS = :CATKEY
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('catcode', $aData['CATALOG']);
        $query->bindValue('CATKEY', $aDataModelAndType[$a]['MODEL'].','.$aDataModelAndType[$a]['TYPE']);
        $query->execute();

        $aDataCatalog = $query->fetch();
        
        
		}
		
		else
		{
		$sqlModelType = "
        SELECT CATKEYS, CATNAME
        FROM catalog
        WHERE CATCODE = :catcode
        ";

        $query = $this->conn->prepare($sqlModelType);
        $query->bindValue('catcode', $aData['CATALOG']);
        $query->execute();

        $aDataModelAndType = $query->fetchAll();
       $CATKEY = NULL;
       if (count($aDataModelAndType)>1)
       {
	   
        $aSort = array();
        foreach ($aDataModelAndType as $index => $value)
        {
			$aSort[] = $value['CATKEYS'];
		}
        
       usort($aSort,"max");
        function max($a, $b)
			{
   			 if (strlen($a) == strlen($b)) 
   			 	{
        	return 0;
    			}
    		return (strlen($a) > strlen($b)) ? -1 : 1;
			}

		
        
         foreach ($aSort as $index => $value)
         {
         	foreach ($aModelDesc as $index1 => $value1)
         	{
         		if (substr_count(strtolower(trim($value1)), strtolower(trim($value))))
         		
				{
				$CATKEY = trim($value);
				}
			}
		 	
		 }
		
		$sqlCatalog = "
        SELECT *
        FROM catalog
        WHERE CATCODE = :catcode
        AND CATKEYS = :CATKEY
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('catcode', $aData['CATALOG']);
        $query->bindValue('CATKEY', $CATKEY);
        $query->execute();

        $aDataCatalog = $query->fetch();
        }
        else
        {
			$sqlCatalog = "
        SELECT *
        FROM catalog
        WHERE CATCODE = :catcode
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('catcode', $aData['CATALOG']);
        $query->execute();

        $aDataCatalog = $query->fetch();
		}
			
			
        
        $sqlModif = "
        SELECT *
        FROM model_series
        WHERE CATNAME = :CATNAME
        AND CATCODE = :CATCODE
        AND E_CODES LIKE :E_CODES
        AND ABBREV <> 'AR'
        ";
        
        $query = $this->conn->prepare($sqlModif);
        $query->bindValue('CATNAME', $aDataCatalog['CATNAME']);
        $query->bindValue('CATCODE', $aData['CATALOG']);
        $query->bindValue('E_CODES', '%'.substr($aData['model_code'], -2).'%');
        $query->execute();
        $aModif = $query->fetch();
        $modif = $CATKEY.' E '.$aModif['E_CODES'];
        
        
        $sqlModel = "
        SELECT MODEL
        FROM model_cat_name
        WHERE CATNAME = :CATNAME
        AND ABBREV =:ABBREV
        ";

        $query = $this->conn->prepare($sqlModel);
        $query->bindValue('CATNAME', $aDataCatalog['CATNAME']);
        $query->bindValue('ABBREV', $aModif['ABBREV']);
        $query->execute();

        $aModel = $query->fetch(); $model = $aModel['MODEL'];
			
		} 
		
		
		
		
		$sqlCountry = "
        SELECT DEFINITION
        FROM scodes
        WHERE CODE = :CODE
        ";

        $query = $this->conn->prepare($sqlCountry);
        $query->bindValue('CODE', 'E'.substr($aData['model_code'], -2));
        $query->execute();
        $aCountry = $query->fetch();
        
        $sql = "
        SELECT CATSER
        FROM catalog
        WHERE CATCODE = :CATCODE
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('CATCODE', $aData['CATALOG']);
        $query->execute();
        $aCompl = $query->fetch();
        
        
		       
        
        $result = array();

        if ($aData) {
            $result = array(
                'model' => $model,
                'modif' => $modif,
                'engine' => $aData['ENGINE'],
                'trans' => $aData['TRANS'],
                'color' => '('.$aData['COLOR'].') '.$aColor['COLORDESC'],                
                'color2' => $aData['SUBCOLR'],
                'model_code' => $aData['model_code'],
                'desc_model_code' => $aModelDesc,
                'country' => substr($aData['model_code'], -2).' - '.'E'.substr($aData['model_code'], -2).' '.$aCountry['DEFINITION'],
                'complectation' => $aData['CATALOG'].'.'.$aDataCatalog['CATSER'],
                'modification' => $aDataCatalog['CATNAME'],
                'region' =>$aModif['ABBREV'],
                Constants::PROD_DATE => '01.01.2001',
            );
        }

        return $result;
    }
    
     public function getVinCompl($regionCode, $modelCode, $complectationCode)
     {
	 	 $sql = "
        SELECT *
        FROM body_desc
        WHERE catalog = :regionCode 
        AND model_code = :model_code
        AND f1 = :f1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('f1', $complectationCode);
        $query->execute();

        $aCompl = $query->fetch();
        return $aCompl;
	 }
    
    public function getVinSchemas($regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $sqlSchemas = "
        SELECT *
        FROM part_images
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND sec_group = :subGroupCode
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        
        foreach($aData as $item){
		
		 if ((substr_count($item['desc_en'],'MY')>0)&&(substr_count($item['desc_en'], $modificationCode)!=0)||(substr_count($item['desc_en'],'MY')==0))
		           
                $schemas[] = $item['image_file'];
        }

        return $schemas;
    }
   
   
        
} 