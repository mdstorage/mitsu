<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\BuickBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\BuickBundle\Components\BuickConstants;

class BuickArticulModel extends BuickCatalogModel{

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



        $reg = array('US', 'CA');
        $regions = array();

        foreach($aData as $index => $value)

        {
            if ($value['COUNTRY_CODE'] === '*')
            {$regions = $reg;}
            else
           $regions[] = $value['COUNTRY_CODE'];
        }


        return array_unique($regions);

    }

    public function getArticulModels ($articul, $regionCode)
    {


        $sql = "
        SELECT model.MODEL_DESC, model.MAKE_DESC
        FROM part_usage
        INNER JOIN model ON (model.CATALOG_CODE = part_usage.CATALOG_CODE AND (model.COUNTRY_CODE = part_usage.COUNTRY_CODE OR model.COUNTRY_CODE = '*')
        and (model.MAKE_DESC = 'Buick' OR model.MAKE_DESC = 'Lt Truck Buick'))
        WHERE part_usage.PART_NBR = :articulCode
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*')

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

        $a = array();
        foreach($aData as $item)
        {
            $a[(stripos($item['MODEL_DESC'],' '))?substr($item['MODEL_DESC'], 0, stripos($item['MODEL_DESC'],' ')):$item['MODEL_DESC']] = (stripos($item['MODEL_DESC'],' '))?substr($item['MODEL_DESC'], 0, stripos($item['MODEL_DESC'],' ')):$item['MODEL_DESC'];
        }



        $models = array();
        foreach($aData as $item){

            foreach($a as $value)
            {
                if (strpos($item['MODEL_DESC'], $value) !== false)
                {
                    $models[] = urlencode(strtoupper($value).'_'.$item['MAKE_DESC']);

                }

            }

        }


        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {

        $modelCode = urldecode(substr($modelCode, 0, strpos($modelCode, '_')));


        $sql = "
        SELECT model.CATALOG_CODE, model.FIRST_YEAR, model.LAST_YEAR
        FROM part_usage
        INNER JOIN model ON (model.CATALOG_CODE = part_usage.CATALOG_CODE
        AND (model.COUNTRY_CODE = part_usage.COUNTRY_CODE OR model.COUNTRY_CODE = '*') AND model.MODEL_DESC LIKE :modelCode)
        WHERE part_usage.PART_NBR = :articulCode
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*')

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
        $query->bindValue('modelCode',  '%'.$modelCode.'%');
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();

        $first_year = array();
        $last_year = array();

        foreach($aData as $item)
        {
            $first_year[] = $item['FIRST_YEAR'];
            $last_year[] = $item['LAST_YEAR'];

        }

        foreach($aData as $item)
        {
            foreach (range(min($first_year), max($last_year), 1) as $value)
            {
                $modifications[] = $value;
            }


        }


        return array_unique($modifications);


    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
        $modelCode = urldecode(substr($modelCode, 0, strpos($modelCode, '_')));

        $sql = "
        SELECT model.CATALOG_CODE, model.MODEL_DESC
        FROM part_usage
        INNER JOIN model ON (model.CATALOG_CODE = part_usage.CATALOG_CODE
        AND (model.COUNTRY_CODE = part_usage.COUNTRY_CODE OR model.COUNTRY_CODE = '*') AND model.MODEL_DESC LIKE :modelCode AND :modificationCode BETWEEN model.FIRST_YEAR AND model.LAST_YEAR)
        WHERE part_usage.PART_NBR = :articulCode
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*')

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  '%'.$modelCode.'%');
        $query->execute();

        $aData = $query->fetchAll();

            $complectations = array();

        foreach($aData as $item){

            $complectations[] = urlencode($item['CATALOG_CODE'].'_'.$item ['MODEL_DESC']);

        }


        return array_unique($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $complectationCode = urldecode($complectationCode);

        $catalogCode = substr($complectationCode, 0, strpos($complectationCode, '_'));

        $year = $modificationCode;

        $sql = "
        SELECT part_usage.MAJOR_GROUP
        FROM part_usage
        INNER JOIN callout_legend ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND callout_legend.CAPTION_GROUP = part_usage.MAJOR_GROUP)
        WHERE part_usage.PART_NBR = :articulCode
        AND part_usage.CATALOG_CODE = :catalogCode
        AND :year BETWEEN part_usage.FIRST_YEAR AND part_usage.LAST_YEAR
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*')

        UNION

        SELECT part_v.MAJOR_GROUP
        FROM part_v
        INNER JOIN callout_legend ON (callout_legend.PART_USAGE_ID = part_v.PART_USAGE_ID AND callout_legend.CAPTION_GROUP = part_v.MAJOR_GROUP)
        WHERE part_v.PART_NBR = :articulCode
        AND part_v.CATALOG_CODE = :catalogCode
        AND :year BETWEEN part_v.FIRST_YEAR AND part_v.LAST_YEAR
        AND (part_v.COUNTRY_CODE = :regionCode OR part_v.COUNTRY_CODE = '*')

        UNION

        SELECT callout_legend.CAPTION_GROUP
        FROM part_usage
        INNER JOIN callout_legend ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR)
        WHERE part_usage.PART_NBR = :articulCode
        AND part_usage.PART_TYPE LIKE 'Z'
        AND part_usage.CATALOG_CODE = :catalogCode
        AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*')

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
        $complectationCode = urldecode($complectationCode);

        $catalogCode = substr($complectationCode, 0, strpos($complectationCode, '_'));
        $year = $modificationCode;

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
        $complectationCode = urldecode($complectationCode);
        $catalogCode = substr($complectationCode, 0, strpos($complectationCode, '_'));
        $year = $modificationCode;


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