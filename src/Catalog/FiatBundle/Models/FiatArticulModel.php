<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\FiatBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\FiatBundle\Components\FiatConstants;

class FiatArticulModel extends FiatCatalogModel{

    public function getArticulRegions($articulCode){

        
        $sql = "
        select markt from all_overview, all_katalog
        where teilenummer = :articulCode and all_katalog.epis_typ = all_overview.epis_typ and all_katalog.catalog = all_overview.catalog
        and all_overview.catalog = 'se'
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();
        $regions = array();

        foreach($aData as $item)
        {
            $regions[] = $item['markt'];

        }
        return array_unique($regions);

    }

    public function getArticulModels ($articul, $regionCode)
    {

        $sql = "
        select modell from all_overview, all_katalog
        where teilenummer = :articulCode and all_katalog.epis_typ = all_overview.epis_typ and all_katalog.catalog = all_overview.catalog
        and all_overview.catalog = 'se' and all_overview.markt = :regionCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();

		
		$models = array();

        foreach($aData as $item)
        {
            $models[] = $item['modell'];

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        $sql = "
        select all_katalog.epis_typ, all_overview.einsatz from all_overview, all_katalog
        where teilenummer = :articulCode and all_katalog.epis_typ = all_overview.epis_typ and all_katalog.catalog = all_overview.catalog
        and all_overview.catalog = 'se' and all_overview.markt = :regionCode and all_overview.modell = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        foreach($aData as $item)
        {
            $modifications[] = $item['einsatz'].'_'.$item['epis_typ'];

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

        $modelCode = urldecode($modelCode);
        $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $sql = "
        select hg_ug from all_katalog
        where teilenummer = :articulCode and epis_typ = :modificationCode
        and catalog = 'se'
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{

			$groups[]= (substr($item['hg_ug'],0,1)=='0')?'10':substr($item['hg_ug'],0,1);

			
		}

        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $modelCode = urldecode($modelCode);
        $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $groupCode = (($groupCode == '10')?'0':$groupCode);

        $sql = "
        select bildtafel2 from all_katalog
        where teilenummer = :articulCode and epis_typ = :modificationCode
        and catalog = 'se' and  LEFT(hg_ug, 1) = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

    	$subgroups = array();

        foreach($aData as $item)
        {

            $subgroups[]= $item['bildtafel2'];


        }

        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $modelCode = urldecode($modelCode);
        $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $groupCode = (($groupCode == '10')?'0':$groupCode);

        $sql = "
        select grafik from all_katalog
        where epis_typ = :modificationCode and all_katalog.bildtafel <> '' and bildtafel2 = :subGroupCode
        and catalog = 'se' and  LEFT(hg_ug, 1) = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();
	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = substr($item['grafik'],strlen($item['grafik'])-3,3).substr($item['grafik'],1,5).substr($item['grafik'],0,1);


        }
		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $modelCode = urldecode($modelCode);
        $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $groupCode = (($groupCode == '10')?'0':$groupCode);

        $sql = "
        select btpos from all_katalog
        where teilenummer = :articulCode and epis_typ = :modificationCode
        and catalog = 'se' and  LEFT(hg_ug, 1) = :groupCode and bildtafel2 = :subGroupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);

        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {

                $pncs[] = str_replace(array('(', ')'),'',$item['btpos']);


        }
        
        return array_unique($pncs);
    }
} 