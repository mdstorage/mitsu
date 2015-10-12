<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\KiaBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\KiaBundle\Components\KiaConstants;

class KiaArticulModel extends KiaCatalogModel{

    public function getArticulRegions($articulCode){

        
        $sql = "
        select * from cats_table
        where detail_code = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();
        $regions = array();
        $aDataCatalog = array();
        
        if ($aData)
        {
                  
        foreach($aData as $item)
        {
		$sqlCatalog = "
        SELECT data_regions
        FROM kiac
        WHERE cutup_code = :cutup_code
        ";

        $query = $this->conn->prepare($sqlCatalog);
        $query->bindValue('cutup_code', $item['catalog_code']);
        $query->execute();

        $aDataCatalog[] = $query->fetchAll();
		}


		foreach($aDataCatalog as $item)
        {
        	foreach($item as $item1)
        	{
				$regions = explode("|", $item1['data_regions']);
                foreach($regions as $index => $value)
                {
                    if ($value == '')
                    {
                        unset($regions[$index]);
                    }
                    $reg[] = $value;
                }

			}
        		
        		
		}
		}

        return array_unique($reg);

    }

    public function getArticulModels($articul, $regionCode)
    {

        $sql = "
        select * from cats_table
        where detail_code = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataCatalog = array();
        $modelsReg = array();

        if ($aData)
        {

            foreach($aData as $item)
            {
                $sqlCatalog = "
        SELECT catalog_name
        FROM kiac
        WHERE cutup_code = :cutup_code
        ";

                $query = $this->conn->prepare($sqlCatalog);
                $query->bindValue('cutup_code', $item['catalog_code']);
                $query->execute();

                $aDataCatalog[] = $query->fetch();
            }

            $aDataRegions = array('AMANTI', 'AVELLA', 'CADENZA', 'CEED', 'CERATO', 'CERATO/FORTE', 'CERATO/SPECTRA', 'CLARUS', 'ED', 'FORTE', 'IH 12', 'MAGENTIS', 'MENTOR', 'MORNING/PICANTO', 'OPIRUS', 'OPTIMA',
                'OPTIMA/MAGENTIS', 'PICANTO', 'PRIDE', 'PRIDE/FESTIVA', 'QUORIS', 'RIO', 'SEPHIA', 'SEPHIA/SHUMA/MENTOR', 'SMA GEN (1998-2004)', 'SMA MES (19981101-20040228)', 'SPECTRA', 'SPECTRA/SEPHIA II/SHUMA II/MENTOR II', 'TFE 11', 'VENGA',
                'BORREGO', 'CARENS', 'CARNIVAL', 'CARNIVAL/SEDONA', 'JOICE DS', 'MOHAVE', 'RETONA', 'RONDO', 'RONDO/CARENS', 'SEDONA', 'SORENTO', 'SOUL', 'SPORTAGE', 'AM928 (1998-)', 'BESTA', 'BONGO-3 1TON,1.4TON',
                'COSMOS', 'GRANBIRD', 'K2500/K2700/K2900/K3000/K3600', 'MIGHTY', 'POWER COMBI', 'PREGIO', 'PREGIO/BESTA', 'RHINO', 'TOWNER', 'SPTR');

            foreach($aDataCatalog as $item) {
                foreach ($aDataRegions as $itemReg) {
                    if (strpos($item['catalog_name'], $itemReg) !== false) {
                        $modelsReg[] = $itemReg;

                    }


                }
            }


	    }
 
        return array_unique($modelsReg);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $sql = "
        select * from cats_table
        where detail_code = :articulCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataCatalog = array();

        if ($aData)
        {

            foreach ($aData as $item) {
                $sqlCatalog = "
        SELECT catalog_code, catalog_folder
        FROM kiac
        WHERE cutup_code = :cutup_code
        ";

                $query = $this->conn->prepare($sqlCatalog);
                $query->bindValue('cutup_code', $item['catalog_code']);
                $query->execute();

                $aDataCatalog[] = $query->fetchAll();
            }

            $modifications = array();
            foreach ($aDataCatalog as $item) {
                foreach ($item as $item1) {
                    $modifications[] = $item1['catalog_code'].'_'.$item1['catalog_folder'];
                }

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

        $sql = "
        SELECT main_part
        FROM cats_table
        WHERE detail_code = :articul

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{
			foreach($item as $item1)
			{
			$groups[]=$item1;
			}
			
		}
        return array_unique($groups);
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $ghg = $this->getComplectations($regionCode, $modelCode, $modificationCode);
        $complectationOptions = $ghg[$complectationCode]['options']['option2'];
        
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));



        $sql = "
        SELECT *
        FROM cats_table
        WHERE detail_code = :articul
        AND catalog_code = :catCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->bindValue('catCode', $catCode);
        $query->execute();

        $aData = $query->fetchAll();


        foreach ($aData as $index => $value)
        {
            $value2 = str_replace(substr($value['model_options'], 0, strpos($value['model_options'], '|')), '', $value['model_options']);
            $articulOptions = explode('|', str_replace(';', '', $value2));


            foreach ($articulOptions as $index1 => $value1) {
                if (($value1 == '') || ($index1 > (count($complectationOptions)-1))) {
                    unset ($articulOptions[$index1]);
                }
            }
            $cd = count($articulOptions);
            $cdc = count(array_intersect_assoc($articulOptions, $complectationOptions));

            if ($cd != $cdc)
            {
                unset ($aData[$index]);
            }

        }



        foreach ($aData as $item) {
            $sqlCatalog = "
        SELECT sector_name, sector_id
        FROM cats_map
        WHERE catalog_name = :catalog_code
        AND sector_id =:compl_name
        ";

            $query = $this->conn->prepare($sqlCatalog);
            $query->bindValue('catalog_code', $catCode);
            $query->bindValue('compl_name', $item['compl_name']);
            $query->execute();

            $aDataCatalog[] = $query->fetchAll();
        }


        $subgroups = array();
        foreach($aDataCatalog as $item) {
            foreach ($item as $item1) {
                $subgroups[$item1['sector_id']] = $item1['sector_name'];

            }

        }






      /*  return (array_intersect($subgroups, $test));*/
        return ($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $sql = "
        SELECT compl_name
        FROM cats_table
        WHERE detail_code = :articul

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->execute();

        $aData = $query->fetchAll();
	   
	   $schemas = array();
        foreach($aData as $item) {
            foreach ($item as $item1) {
                $schemas[] = $item1;

            }

        }
		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $sql = "
        SELECT scheme_num
        FROM cats_table
        WHERE detail_code = :articul

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {
            foreach ($item as $item1) {
                $pncs[] = $item1;

            }

        }
        
        return array_unique($pncs);
    }
} 