<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\AbarthBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\AbarthBundle\Components\AbarthConstants;

class AbarthArticulModel extends AbarthCatalogModel{

    public function getArticulRegions($articulCode){


        $aData = array('EU' => 'Европа');



        $regions = array();
        foreach($aData as $index => $value)
        {
            $regions[] = $index;
        }

        return $regions;

    }

    public function getArticulModels ($articul, $regionCode)
    {

        $sql = "
        select comm_modgrp.cmg_cod from tbdata, catalogues, comm_modgrp
        where prt_cod = :articulCode and tbdata.cat_cod = catalogues.cat_cod and catalogues.cmg_cod = comm_modgrp.cmg_cod
        and catalogues.mk2_cod = comm_modgrp.mk2_cod
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

		
		$models = array();

        foreach($aData as $item)
        {
            $models[] = $item['cmg_cod'];

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        $sql = "
        select catalogues.cat_cod from tbdata, catalogues
        where tbdata.prt_cod = :articulCode and tbdata.cat_cod = catalogues.cat_cod and catalogues.cmg_cod = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        foreach($aData as $item)
        {
            $modifications[] = $item['cat_cod'];

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

        $sql = "
        select grp_cod from tbdata
        where tbdata.prt_cod = :articulCode and tbdata.cat_cod = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{

			$groups[]= $item['grp_cod'];

			
		}

        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $sql = "
        select sgrp_cod from tbdata
        where tbdata.prt_cod = :articulCode and tbdata.cat_cod = :modificationCode and grp_cod = :groupCode
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
            $subgroups[]= $groupCode.str_pad($item['sgrp_cod'], 2, "0", STR_PAD_LEFT);

        }


        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $sql = "
        select variante, sgs_cod from tbdata
        where tbdata.prt_cod = :articulCode and tbdata.cat_cod = :modificationCode and grp_cod = :groupCode and sgrp_cod = :subGroupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', str_replace($groupCode,'',$subGroupCode));
        $query->execute();

        $aData = $query->fetchAll();
	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = $item['variante'].'_'.$item['sgs_cod'];


        }
		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {

        $sgs_cod = substr($schemaCode, strpos($schemaCode, '_')+1, strlen($schemaCode));
        $variante = substr($schemaCode, 0, strpos($schemaCode, '_'));

        $sql = "
        select tbd_rif from tbdata
        where tbdata.prt_cod = :articulCode and tbdata.cat_cod = :modificationCode and grp_cod = :groupCode and sgrp_cod = :subGroupCode
        and sgs_cod = :sgs_cod and variante = :variante
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', str_replace($groupCode,'',$subGroupCode));
        $query->bindValue('sgs_cod', $sgs_cod);
        $query->bindValue('variante', $variante);
        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {

                $pncs[] = $item['tbd_rif'];


        }

        return array_unique($pncs);
    }
} 