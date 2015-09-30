<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\HuyndaiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\HuyndaiBundle\Components\HuyndaiConstants;

class HuyndaiVinModel extends HuyndaiCatalogModel {

    public function getVinFinderResult($vin)
    {
        
        $sql = "
        SELECT *
        FROM vin
        WHERE vin = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();

        $sqlCompl = "
        SELECT *
        FROM vin_model
        WHERE model_index = :model_index
        ";

        $query = $this->conn->prepare($sqlCompl);
        $query->bindValue('model_index', $aData['model_index']);
        $query->execute();
        $aDataCompl = $query->fetch();

        $sqlmodif = "
        SELECT *
        FROM hywc
        WHERE catalog_code = :model
        ORDER BY family
        ";

        $query = $this->conn->prepare($sqlmodif);
        $query->bindValue('model', $aDataCompl['model']);
        $query->execute();

        $aDataModif = $query->fetch();

        $complectations = $this->getComplectations('','',$aDataModif['catalog_code'].'_'.$aDataModif['catalog_folder']);

     /*  print_r($complectations[$aData['model_index']]['options']['option1']); die;*/
        $sqlDescription = "
        SELECT *
        FROM vin_description
        WHERE vin = :vin
        ";

        $query = $this->conn->prepare($sqlDescription);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aDataDescription = $query->fetch();


        $result = array();

        if ($aData) {
            $result = array(
                'model' => $aDataModif['family'],
                'modif' => $aDataModif['catalog_code'].'_'.$aDataModif['catalog_folder'],
                'compl' => $complectations[$aData['model_index']]['options']['option1'],
                Constants::PROD_DATE => $aDataDescription['date_output'] ,
                'region' => $aDataDescription['country'],
                'ext_color' => $aDataDescription['ext_color'],
                'int_color' => $aDataDescription['int_color'],
                'compl_for_groups' => $aData['model_index'],
                'region_for_groups' => str_replace('|', '', $aDataModif['previous_region']),
            );
        }

        return $result;
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