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
        vo.*, vm.model, ctlg.cat_name, ctlg.catalogue_code, ctlg.cat_folder, ctlg.family, ctlg.data_regions, llextcolor.lex_desc Extcolor, llintcolor.lex_desc Intcolor, c0nation.nation Country, c0nation.region Region
        FROM vin_options vo
        INNER JOIN vin_model vm ON (vm.vin_model_id = vo.vin_model_id)
        INNER JOIN catalog ctlg ON (ctlg.catalogue_code = vm.catalogue_code)
        INNER JOIN cats0_extcolor c0ext ON (c0ext.color_main_code = vo.exterior_color)
        LEFT JOIN lex_lex llextcolor ON (llextcolor.lex_code = c0ext.up_lex_code)
        INNER JOIN cats0_intcolor c0int ON (c0int.color_code = vo.interior_color)
        LEFT JOIN lex_lex llintcolor ON (llintcolor.lex_code = c0int.lex_code)
        INNER JOIN cats0_nation c0nation ON (c0nation.nation_code = CONCAT(vo.country, vo.region))
        WHERE vo.vin = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();
        $aNull = array('');
        $apieces = array_diff(explode("|", $aData['data_regions']), $aNull);

        if (count($apieces) == 1){
            $aData['data_regions'] = $apieces[0];
        }
        else{
            switch($aData['Country'])
            {
                case ('PUERTO RICO'): $reg = 'PUT';break;
                case ('U.S.A'): $reg = 'USA';break;
            }

            switch($aData['Region'])
            {
                case ('AUSTRALIA'): $reg = 'AUS';break;
                case ('CANADA'): $reg = 'CAN';break;
                case ('CENTRAL ASIA'): $reg = 'CIS';break;
                case ('EUROPE'): $reg = 'EUR';break;
                case ('GENERAL'): $reg = 'GEN';break;
                case ('GENERAL(LHD)'): $reg = 'GEN';break;
                case ('GENERAL(RHD)'): $reg = 'GEN';break;
                case ('MIDDLE EAST'): $reg = 'MES';break;
                case ('KOREA'): $reg = 'MES';break;
            }
            if (in_array($reg, $apieces))
            {
                $aData['data_regions'] = $reg;
            }
        }
        $complectations = $this->getComplectations($aData['data_regions'], $aData['family'], $aData['catalogue_code'].'_'.$aData['cat_folder'], $aData['model']);
        $complectationRes = array();
            for ($i = 1; $i < 11; $i++) {
                $ten['f'.$i] = iconv("Windows-1251", "UTF-8", $complectations['ten' . $i]);
                if ($complectations['ken' . $i]){
                    $complectationRes['f'.$i] = $ten['f'.$i].': (' . $complectations['f' . $i] . ') ' . $complectations['ken' . $i];
                }
            }
        $result = array();
        if ($aData) {
            $result = array(
                'marka' => 'KIA',
                'model_for_groups' => $aData['family'],
                'model' => $aData['cat_name'],
                'modif' => $aData['catalogue_code'].'_'.$aData['cat_folder'],
                'compl' => $complectationRes,
                Constants::PROD_DATE => $aData['build_date'] ,
                'region' => '('.$aData['region'].') '.$aData['Region'],
                'country' => '('.$aData['country'].') '.$aData['Country'],
                'wheel' => $aData['drive_type'],
                'ext_color' => '('.$aData['exterior_color'].') '.$aData['Extcolor'],
                'int_color' => '('.$aData['interior_color'].') '.$aData['Intcolor'],
                'compl_for_groups' => $aData['model'],
                'region_for_groups' => $aData['data_regions']
            );
        }
        return $result;
    }
    
    
    /*public function getVinSubGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $prodDate)
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
    }*/
   
   
        
} 