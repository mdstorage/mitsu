<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\ChevroletUsaBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\ChevroletUsaBundle\Components\ChevroletUsaConstants;

class ChevroletUsaArticulModel extends ChevroletUsaCatalogModel{

    public function getArticulRegions($articulCode){


        $sql = "
        SELECT COUNTRY_CODE FROM part_usage
        WHERE PART_NBR = :articulCode

        UNION

        SELECT COUNTRY_CODE FROM part_v
        WHERE PART_NBR = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();



        $regions = array();
        foreach($aData as $index => $value)
        {
           $regions[] = $value['COUNTRY_CODE'];
        }


        return array_unique($regions);

    }

    public function getArticulModels ($articul, $regionCode)
    {

        $sql = "
        SELECT model.MODEL_DESC
        FROM part_usage
        INNER JOIN model ON (model.CATALOG_CODE = part_usage.CATALOG_CODE AND (model.COUNTRY_CODE = part_usage.COUNTRY_CODE OR model.COUNTRY_CODE = '*')
        and (model.MAKE_DESC = 'Chevrolet' OR model.MAKE_DESC = 'Lt Truck Chevrolet'))
        WHERE part_usage.PART_NBR = :articulCode
        AND part_usage.COUNTRY_CODE = :regionCode

     /*   UNION

        SELECT model.MODEL_DESC
        FROM part_v
        INNER JOIN model ON (model.CATALOG_CODE = part_v.CATALOG_CODE AND (model.COUNTRY_CODE = part_v.COUNTRY_CODE OR model.COUNTRY_CODE = '*'))
        WHERE part_v.PART_NBR = :articulCode
        AND part_v.COUNTRY_CODE = :regionCode*/

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);

        $query->execute();

        $aData = $query->fetchAll();

		
		$models = array();

        foreach($aData as $item)
        {

                $models[] = $item['MODEL_DESC'];


        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {


        $sql = "
        SELECT model.CATALOG_CODE, model.FIRST_YEAR, model.LAST_YEAR
        FROM part_usage
        INNER JOIN model ON (model.CATALOG_CODE = part_usage.CATALOG_CODE
        AND (model.COUNTRY_CODE = part_usage.COUNTRY_CODE OR model.COUNTRY_CODE = '*') AND model.MODEL_DESC = :modelCode)
        WHERE part_usage.PART_NBR = :articulCode
        AND part_usage.COUNTRY_CODE = :regionCode

   /*     UNION

        SELECT model.CATALOG_CODE, model.FIRST_YEAR, model.LAST_YEAR
        FROM part_v
        INNER JOIN model ON (model.CATALOG_CODE = part_v.CATALOG_CODE
        AND (model.COUNTRY_CODE = part_v.COUNTRY_CODE OR model.COUNTRY_CODE = '*') AND model.MODEL_DESC = :modelCode)
        WHERE part_v.PART_NBR = :articulCode
        AND part_v.COUNTRY_CODE = :regionCode*/

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        foreach($aData as $item)
        {
            foreach (range($item['FIRST_YEAR'], $item['LAST_YEAR'], 1) as $value)
            {
                $modifications[] = $item['CATALOG_CODE'].'_'.$value;
            }


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

        $catalogCode = substr($modificationCode, 0, strpos($modificationCode, '_'));
        $year = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $modelCode = urldecode($modelCode);

        $sql = "
        SELECT MAJOR_GROUP
        FROM part_usage
        WHERE part_usage.PART_NBR = :articulCode
        AND part_usage.CATALOG_CODE = :catalogCode
        AND :year BETWEEN FIRST_YEAR AND LAST_YEAR
        AND part_usage.COUNTRY_CODE = :regionCode

        UNION

        SELECT MAJOR_GROUP
        FROM part_v
        WHERE part_v.PART_NBR = :articulCode
        AND part_v.CATALOG_CODE = :catalogCode
        AND :year BETWEEN FIRST_YEAR AND LAST_YEAR
        AND part_v.COUNTRY_CODE = :regionCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('catalogCode', $catalogCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('year', $year);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{

			$groups[]= $item['MAJOR_GROUP'];

			
		}


        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {

        $aData =array('1' => '1');

    	$subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]= $item;

        }


        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $catalogCode = substr($modificationCode, 0, strpos($modificationCode, '_'));
        $year = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $sql = "
        SELECT callout_legend.ART_NBR
        FROM callout_legend
        INNER JOIN part_usage ON (part_usage.PART_USAGE_ID = callout_legend.PART_USAGE_ID
        AND part_usage.PART_TYPE NOT LIKE 'Z'
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*')
        AND part_usage.PART_NBR = :articulCode
        AND part_usage.CATALOG_CODE = :catalogCode
        AND :year BETWEEN part_usage.FIRST_YEAR AND part_usage.LAST_YEAR)
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.CAPTION_FIRST_YEAR AND callout_legend.CAPTION_LAST_YEAR


        UNION
        SELECT callout_legend.ART_NBR
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID
        AND part_usage.PART_TYPE LIKE 'Z'
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*')
        AND part_usage.PART_NBR = :articulCode
        AND part_usage.CATALOG_CODE = :catalogCode)
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.CAPTION_FIRST_YEAR AND callout_legend.CAPTION_LAST_YEAR

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);

        $query->bindValue('articulCode', $articul);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('catalogCode', $catalogCode);
        $query->bindValue('year', $year);

        $query->execute();

        $aData = $query->fetchAll();
	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = $item['ART_NBR'];


        }
		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {

        $catalogCode = substr($modificationCode, 0, strpos($modificationCode, '_'));
        $year = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));


        $sql = "
        SELECT (callout_legend.CALLOUT_NBR) CALLOUT_NBR
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND part_usage.PART_TYPE NOT LIKE 'Z' AND part_usage.PART_NBR = :articulCode
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*'))
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode



        UNION
        SELECT (callout_legend.CALLOUT_NBR) CALLOUT_NBR
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND part_usage.PART_TYPE LIKE 'Z' AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*'))
        INNER JOIN part_v ON (part_v.PART_NBR = part_usage.PART_NBR AND part_v.PART_NBR = :articulCode AND part_v.COUNTRY_LANG = 'EN' and part_v.CATALOG_CODE = callout_legend.CATALOG_CODE and part_v.COUNTRY_CODE = part_usage.COUNTRY_CODE
        and (callout_legend.ORIG_MINOR_GROUP IS NULL OR part_v.MINOR_GROUP = callout_legend.ORIG_MINOR_GROUP))
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode



        ORDER BY (1)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('catalogCode', $catalogCode);
        $query->bindValue('year', $year);
        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {

                $pncs[] = $item['CALLOUT_NBR'];


        }

        return array_unique($pncs);
    }
} 