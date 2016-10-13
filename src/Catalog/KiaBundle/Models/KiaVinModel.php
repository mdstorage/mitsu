<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\KiaBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\KiaBundle\Components\KiaConstants;

class KiaVinModel extends KiaCatalogModel {

    public function getVinFinderResult($vin)
    {

        $sql = "
        SELECT DISTINCT
        vin.model_index,
        kiac.catalog_code,
        kiac.catalog_folder,
        kiac.previous_region,
        kiac.data_regions,
        kiac.catalog_name,
        kiac.family,
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
        INNER JOIN kiac ON (kiac.catalog_code = vin_model.model)
        INNER JOIN vin_description ON (vin_description.vin = :vin)
        INNER JOIN cats_0_extcolor ON (cats_0_extcolor.ext_color_1 = vin_description.ext_color)
        INNER JOIN cats_0_intcolor ON (cats_0_intcolor.int_color = vin_description.int_color)
        INNER JOIN cats_0_nation ON (cats_0_nation.country = vin_description.country AND cats_0_nation.region = vin_description.region)
        WHERE vin.vin = :vin
        ORDER BY kiac.family
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();


        $modelsReg = array();


        $aDataRegions = array('AMANTI', 'AVELLA', 'CADENZA', 'CEED', 'CERATO', 'CERATO/FORTE', 'CERATO/SPECTRA', 'CLARUS', 'ED', 'FORTE', 'IH 12', 'MAGENTIS', 'MENTOR', 'MORNING/PICANTO', 'OPIRUS', 'OPTIMA',
            'OPTIMA/MAGENTIS', 'PICANTO', 'PRIDE', 'PRIDE/FESTIVA', 'QUORIS', 'RIO', 'SEPHIA', 'SEPHIA/SHUMA/MENTOR', 'SMA GEN (1998-2004)', 'SMA MES (19981101-20040228)', 'SPECTRA', 'SPECTRA/SEPHIA II/SHUMA II/MENTOR II', 'TFE 11', 'VENGA',
            'BORREGO', 'CARENS', 'CARNIVAL', 'CARNIVAL/SEDONA', 'JOICE DS', 'MOHAVE', 'RETONA', 'RONDO', 'RONDO/CARENS', 'SEDONA', 'SORENTO', 'SOUL', 'SPORTAGE', 'AM928 (1998-)', 'BESTA', 'BONGO-3 1TON,1.4TON',
            'COSMOS', 'GRANBIRD', 'K2500/K2700/K2900/K3000/K3600', 'MIGHTY', 'POWER COMBI', 'PREGIO', 'PREGIO/BESTA', 'RHINO', 'TOWNER', 'SPTR');

        foreach($aDataRegions as $itemReg)
        {
            if (strpos($aData['catalog_name'], $itemReg) !== false)
            {
                $modelsReg[] = $itemReg;

            }


        }


        $complectations = $this->getComplectations('','',$aData['catalog_code'].'_'.$aData['catalog_folder']);


        if ($aData['previous_region']) {

            $region_for_groups = str_replace('|', '', $aData['previous_region']);
        }
        else {

            $region_for_groups = substr($aData['data_regions'], 0, 3);
        }


        $result = array();

        if ($aData) {
            $result = array(
                'model_for_groups' => $modelsReg[0],
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
    
    
    public function getVinSubGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $prodDate)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $sqlSubGroups = "
        SELECT sector_id
        FROM cats_map
        WHERE catalog_name = :catCode
            AND part =:groupCode
        ";

        $query = $this->conn->prepare($sqlSubGroups);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        foreach($aData as &$item)
        {
            $sqlArticuls = "
            SELECT start_data, end_data
            FROM cats_table
            WHERE catalog_code = :catCode
            AND main_part = :groupCode
            AND compl_name = :item

        ";

            $query = $this->conn->prepare($sqlArticuls);
            $query->bindValue('catCode', $catCode);
            $query->bindValue('groupCode', $groupCode);
            $query->bindValue('item', $item['sector_id']);
            $query->execute();

            $item['arts'] = $query->fetchAll();

        }

        foreach($aData as $indexId => &$valueId)
        {
            foreach($valueId['arts'] as $index => $value) {

                if (($value['start_data'] > $prodDate) || (($value['end_data']) && ($value['end_data'] < $prodDate)))
                {
                   unset($valueId['arts'][$index]);
                }
            }
            if(!$valueId['arts'])
            {
                unset($aData[$indexId]);
            }
        }





        $aDataSubGroup = array();
        $subGroups = array();
        
        foreach($aData as $item)
        {
            $subGroups[] = $item['sector_id'];
        }

        foreach(array_unique($subGroups) as $item)
        {
            $sql = "
            SELECT sector_name
            FROM cats_map
            WHERE catalog_name =:catCode
            AND sector_id = :item
            AND part = :groupCode
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('catCode',  $catCode);
            $query->bindValue('item',  $item);
            $query->bindValue('groupCode',  $groupCode);
            $query->execute();

            $aDataSub = $query->fetch();
            $aDataSubGroup[] = $aDataSub['sector_name'];
        }


        return $aDataSubGroup;
    }
   
   
        
} 