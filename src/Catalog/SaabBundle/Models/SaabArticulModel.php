<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\SaabBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\SaabBundle\Components\SaabConstants;

class SaabArticulModel extends SaabCatalogModel{

    public function getArticulRegions($articulCode){



        $regions = array();
                  

            $regions[] = 'EU';


        return $regions;

    }

    public function getArticulModels($articul, $regionCode)
    {

        $sql = "
        select MODEL_NO from model, textlines
        where PART_NO = :articulCode and CATALOGUE_NO = MODEL_NO
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        $models = array();


        foreach($aData as $item)
        {
            $models[] = $item['MODEL_NO'];

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $sql = "
        SELECT nYear
        FROM vin_year
        LEFT JOIN saab.section ON (nYear BETWEEN saab.section.FROM_YEAR AND saab.section.TO_YEAR)
        LEFT JOIN textlines ON (saab.section.CATALOGUE_NO = textlines.CATALOGUE_NO AND saab.section.GROUP_NO = textlines.GROUP_NO AND saab.section.SECTION_NO = textlines.SECTION_NO)
        WHERE PART_NO = :articulCode
        AND saab.section.CATALOGUE_NO = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        $modifications = array();


        foreach($aData as $item)
        {
            $modifications[] = $item['nYear'];

        }

        return array_unique($modifications);
    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $ghg = $this->getComplectations($regionCode, $modelCode, $modificationCode);
        $test = array();


        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        select * from cats_table
        where detail_code = :articulCode
        AND catalog_code = :catCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('catCode', $catCode);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataCatalog = array();

        if ($aData)
        {

            foreach ($aData as $item) {
                $sqlCatalog = "
        SELECT model_index
        FROM vin_model
        WHERE model =:modificationCode
        ";

                $query = $this->conn->prepare($sqlCatalog);
                $query->bindValue('modificationCode', $modificationCode);
                $query->execute();

                $aDataCatalog[] = $query->fetchAll();
            }


            $complectations = array();

            foreach ($aDataCatalog as $item2) {
                foreach ($item2 as $item1) {
                    $complectations[] = $item1['model_index'];
                }

            }
        }

        foreach ($ghg as $indexCompl => $valueCompl)
        {
            foreach ($aData as $index => $value)
            {
                $value2 = str_replace(substr($value['model_options'], 0, strpos($value['model_options'], '|')), '', $value['model_options']);
                $articulOptions = explode('|', str_replace(';', '', $value2));
            /*  $complectationOptions = $ghg['37454']['options']['option2'];*/
                $complectationOptions = $valueCompl['options']['option2'];

                foreach ($articulOptions as $index1 => $value1) {
                    if (($value1 == '') || ($index1 > (count($complectationOptions)-1))) {
                        unset ($articulOptions[$index1]);
                    }
                }
                $cd = count($articulOptions);
                $cdc = count(array_intersect_assoc($articulOptions, $complectationOptions));

                if ($cd == $cdc)
                {
                  $test[] = $indexCompl;
                }

            }

        }

        return (array_intersect(array_unique($complectations), array_unique($test)));
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $sql = "
        select GROUP_NO from  textlines
        where PART_NO = :articulCode and CATALOGUE_NO = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

            	$groups = array();

        foreach($aData as $item)
		{


			$groups[]=$item['GROUP_NO'];

			
		}

        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $sql = "
        SELECT
HEAD_LINE_1
FROM saab.section, textlines
WHERE PART_NO = :articulCode
AND saab.section.CATALOGUE_NO = :modelCode AND saab.section.GROUP_NO = :groupCode AND saab.section.SECTION_NO = textlines.SECTION_NO
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]=$item['HEAD_LINE_1'];
        }

        return array_unique($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $sql = "
        SELECT
IMAGE_NO
FROM saab.section
LEFT JOIN textlines ON (saab.section.CATALOGUE_NO = textlines.CATALOGUE_NO AND saab.section.GROUP_NO = textlines.GROUP_NO AND saab.section.SECTION_NO = textlines.SECTION_NO)
WHERE textlines.PART_NO = :articulCode
AND saab.section.CATALOGUE_NO = :modelCode
AND saab.section.GROUP_NO = :groupCode

AND HEAD_LINE_1 = :subGroupCode
AND :modificationCode BETWEEN FROM_YEAR AND TO_YEAR
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('subGroupCode',  $subGroupCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();

        foreach($aData as $item)
        {

                $schemas[]=$item['IMAGE_NO'];



        }

        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {

        $sql = "
        SELECT
textlines.POSITION
FROM textlines
LEFT JOIN saab.section ON (textlines.CATALOGUE_NO = saab.section.CATALOGUE_NO AND textlines.GROUP_NO = saab.section.GROUP_NO AND textlines.SECTION_NO = saab.section.SECTION_NO)
WHERE textlines.PART_NO = :articulCode
AND textlines.CATALOGUE_NO = :modelCode
AND textlines.GROUP_NO = :groupCode
AND saab.section.IMAGE_NO = :schemaCode
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('schemaCode',  $schemaCode);
        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();

        foreach($aData as $item)
        {
            $pncs[]=$item['POSITION'];
        }
        return array_unique($pncs);
    }
} 