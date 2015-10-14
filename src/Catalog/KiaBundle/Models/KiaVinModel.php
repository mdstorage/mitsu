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
        FROM kiac
        WHERE catalog_code = :model
        ORDER BY family
        ";

        $query = $this->conn->prepare($sqlmodif);
        $query->bindValue('model', $aDataCompl['model']);
        $query->execute();

        $aDataModif = $query->fetch();
        $modelsReg = array();


        $aDataRegions = array('AMANTI', 'AVELLA', 'CADENZA', 'CEED', 'CERATO', 'CERATO/FORTE', 'CERATO/SPECTRA', 'CLARUS', 'ED', 'FORTE', 'IH 12', 'MAGENTIS', 'MENTOR', 'MORNING/PICANTO', 'OPIRUS', 'OPTIMA',
            'OPTIMA/MAGENTIS', 'PICANTO', 'PRIDE', 'PRIDE/FESTIVA', 'QUORIS', 'RIO', 'SEPHIA', 'SEPHIA/SHUMA/MENTOR', 'SMA GEN (1998-2004)', 'SMA MES (19981101-20040228)', 'SPECTRA', 'SPECTRA/SEPHIA II/SHUMA II/MENTOR II', 'TFE 11', 'VENGA',
            'BORREGO', 'CARENS', 'CARNIVAL', 'CARNIVAL/SEDONA', 'JOICE DS', 'MOHAVE', 'RETONA', 'RONDO', 'RONDO/CARENS', 'SEDONA', 'SORENTO', 'SOUL', 'SPORTAGE', 'AM928 (1998-)', 'BESTA', 'BONGO-3 1TON,1.4TON',
            'COSMOS', 'GRANBIRD', 'K2500/K2700/K2900/K3000/K3600', 'MIGHTY', 'POWER COMBI', 'PREGIO', 'PREGIO/BESTA', 'RHINO', 'TOWNER', 'SPTR');

        foreach($aDataRegions as $itemReg)
        {
            if (strpos($aDataModif['catalog_name'], $itemReg) !== false)
            {
                $modelsReg[] = $itemReg;

            }


        }


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
                'model_for_groups' => $modelsReg[0],
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