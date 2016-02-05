<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\HondaEuropeBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\HondaEuropeBundle\Components\HondaEuropeConstants;

class HondaEuropeVinModel extends HondaEuropeCatalogModel {

    public function getVinComplectations($vin)
    {
        
        $sql = "
        SELECT *
        FROM pmotyt
        WHERE nfrmpf = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', substr($vin,0,11));
        $query->execute();

        $aData = $query->fetchAll();

		
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