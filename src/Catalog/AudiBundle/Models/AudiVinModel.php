<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\AudiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\AudiBundle\Components\AudiConstants;

class AudiVinModel extends AudiCatalogModel {

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

        $sqlExtColor = "
        SELECT lex_code_1
        FROM cats_0_extcolor
        WHERE ext_color_1 = :ext_color_1
        ";

        $query = $this->conn->prepare($sqlExtColor);
        $query->bindValue('ext_color_1', $aDataDescription['ext_color']);
        $query->execute();

        $aDataExtColor = $query->fetch();

        $sqlIntColor = "
        SELECT lex_code
        FROM cats_0_intcolor
        WHERE int_color = :int_color
        ";

        $query = $this->conn->prepare($sqlIntColor);
        $query->bindValue('int_color', $aDataDescription['int_color']);
        $query->execute();

        $aDataIntColor = $query->fetch();

        $sqlRegion = "
        SELECT *
        FROM cats_0_nation
        WHERE country = :country
        AND region = :region
        ";

        $query = $this->conn->prepare($sqlRegion);
        $query->bindValue('country', $aDataDescription['country']);
        $query->bindValue('region', $aDataDescription['region']);
        $query->execute();

        $aDataRegion = $query->fetch();

        if ($aDataModif['previous_region'])
        {
            $region_for_groups = str_replace('|', '', $aDataModif['previous_region']);
        }
        else
        {
            $region_for_groups = substr($aDataModif['data_regions'], 0, 3);
        }





        $result = array();

        if ($aData) {
            $result = array(
                'model_for_groups' => urlencode($aDataModif['family']),
                'model' => $aDataModif['catalog_name'],
                'modif' => $aDataModif['catalog_code'].'_'.$aDataModif['catalog_folder'],
                'compl' => $complectations[$aData['model_index']]['options']['option1'],
                Constants::PROD_DATE => $aDataDescription['date_output'] ,
                'region' => '('.$aDataDescription['region'].') '.$aDataRegion['region_name'],
                'country' => '('.$aDataDescription['country'].') '.$aDataRegion['country_name'],
                'wheel' => $aDataRegion['wheel_location'],
                'ext_color' => '('.$aDataDescription['ext_color'].') '.$this->getDesc($aDataExtColor['lex_code_1'], 'RU'),
                'int_color' => '('.$aDataDescription['int_color'].') '.$this->getDesc($aDataIntColor['lex_code'], 'RU'),
                'compl_for_groups' => $aData['model_index'],
                'region_for_groups' => $region_for_groups,
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