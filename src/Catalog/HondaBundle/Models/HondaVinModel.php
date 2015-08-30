<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\HondaBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\HondaBundle\Components\HondaConstants;

class HondaVinModel extends HondaCatalogModel {

    public function getVinComplectations($vin)
    {
        
        $sql = "
        SELECT *
        FROM dba_pmotyt
        WHERE nfrmpf = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', substr($vin,0,11));
        $query->execute();

        $aData = $query->fetchAll();
        if (!$aData)
        {
			print_r('Ничего не найдено');die;
		}
       
        
        $complectations = array();
        
		
		
			foreach($aData as $item1)
			{
			$complectations[]=$item1['hmodtyp'];	
			}
			
        return $aData;
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