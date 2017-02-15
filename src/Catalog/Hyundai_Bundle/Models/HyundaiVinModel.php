<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\HyundaiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\HyundaiBundle\Components\HyundaiConstants;

class HyundaiVinModel extends HyundaiCatalogModel {

    public function getVinFinderResult($vin)
    {
        
        $sql = "
        SELECT DISTINCT
        vin.model_index,
        hywc.catalog_code,
        hywc.catalog_folder,
        hywc.previous_region,
        hywc.data_regions,
        hywc.catalog_name,
        hywc.family,
        vin_description.region,
        vin_description.country,
        vin_description.ext_color,
        vin_description.int_color,
        vin_description.date_output,
        cats_0_extcolor.lex_code_1,
        cats_0_intcolor.lex_code,
        cats_0_nation.wheel_location,
        cats_0_nation.region_name,
        cats_0_nation.country_name
        FROM vin
        INNER JOIN vin_model ON (vin_model.model_index = vin.model_index)
        INNER JOIN hywc ON (hywc.catalog_code = vin_model.model)
        INNER JOIN vin_description ON (vin_description.vin = :vin)
        INNER JOIN cats_0_extcolor ON (cats_0_extcolor.ext_color_1 = vin_description.ext_color)
        INNER JOIN cats_0_intcolor ON (cats_0_intcolor.int_color = vin_description.int_color)
        INNER JOIN cats_0_nation ON (cats_0_nation.country = vin_description.country AND cats_0_nation.region = vin_description.region)
        WHERE vin.vin = :vin
        ORDER BY hywc.family
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();

        $complectations = $this->getComplectations('','',$aData['catalog_code'].'_'.$aData['catalog_folder']);

       /* $sqlDescription = "
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

        $aDataRegion = $query->fetch();*/

        if ($aData['previous_region'])
        {
            $region_for_groups = str_replace('|', '', $aData['previous_region']);
        }
        else
        {
            $region_for_groups = substr($aData['data_regions'], 0, 3);
        }

        $result = array();

        if ($aData) {
            $result = array(
                'model_for_groups' => urlencode($aData['family']),
                'model' => $aData['catalog_name'],
                'modif' => $aData['catalog_code'].'_'.$aData['catalog_folder'],
                'compl' => $complectations[$aData['model_index']]['options']['option1'],
                Constants::PROD_DATE => $aData['date_output'] ,
                'region' => '('.$aData['region'].') '.$aData['region_name'],
                'country' => '('.$aData['country'].') '.$aData['country_name'],
                'wheel' => $aData['wheel_location'],
                'ext_color' => '('.$aData['ext_color'].') '.$this->getDesc($aData['lex_code_1'], 'RU'),
                'int_color' => '('.$aData['int_color'].') '.$this->getDesc($aData['lex_code'], 'RU'),
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