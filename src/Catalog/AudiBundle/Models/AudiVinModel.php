<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\AudiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\AudiBundle\Components\AudiConstants;

class AudiVinModel extends AudiCatalogModel {


    public function getVinRegions($vin)
    {


        $sql = "
        SELECT markt
        FROM all_overview, all_vkbz, all_vincode
        WHERE (all_vkbz.epis_typ = all_overview.epis_typ and all_vkbz.vkbz = all_vincode.verkaufstyp and all_vkbz.mkb_4 = all_vincode.motorkennbuchstable
        and all_overview.einsatz = all_vincode.model_year and all_vincode.vin = :vin)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetchAll();


        $result = array();
        $Reg = array('RDW' => 'ЕВРОПА', 'BR' => 'БРАЗИЛИЯ', 'MEX' => 'МЕКСИКА', 'RA' => 'КИТАЙ', 'ZA' => 'ЮЖНАЯ АФРИКА', 'CA' => 'КАНАДА', 'USA' => 'США');

        foreach($aData as $item)
        {
            $result[$item['markt']] = array(Constants::NAME=>$item['markt'], Constants::OPTIONS=>array());
        }


        return $result;

    }

    public function getVinFinderResult($vin, $region)
    {
        
        $sql = "
        SELECT *
        FROM all_vincode
        LEFT JOIN all_vkbz on (all_vkbz.vkbz = all_vincode.verkaufstyp and all_vkbz.mkb_4 = all_vincode.motorkennbuchstable)
        left join all_overview on (all_overview.einsatz = all_vincode.model_year and all_overview.epis_typ = all_vkbz.epis_typ)
        WHERE vin = :vin AND markt = :region
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('region', $region);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aDatas = $query->fetchAll();


        $result = array();

        foreach($aDatas as $aData) {

            $result = array(
                /*   'model_for_groups' => urlencode($aDataModif['family']),*/
                'model' => $this->getDesc($aData['vkb_ts_text'], 'R'),
                Constants::PROD_DATE => $aData['prod_date'],
                'mod_god' => $aData['model_year'],
                'mod_kod' => $aData['verkaufstyp'],
                'dvig_kod' => $aData['motorkennbuchstable'],
                'kpp_kod' => $aData['getriebekkenbuchstable'],
                'osn' => substr($aData['int_roof_ext'], strlen($aData['int_roof_ext']) - 2, 2),
                'kuzovcolor' => substr($aData['int_roof_ext'], 0, 2),
                'kry6acolor' => substr($aData['int_roof_ext'], 2, 2),
                'modif' => $aData['einsatz'] . '_' . $aData['epis_typ'],
                'model_for_groups' => $aData['modell'],
                'region' => $region
            );

        }


        return $result;
    }





    public function getVinSubgroups($regionCode, $modelCode, $modificationCode, $groupCode, $vin)
    {


        $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $groupCode = (($groupCode == '10')?'0':$groupCode);

        $sql = "
        SELECT ltr,mkb
        FROM all_vincode, all_vkbz
        WHERE vin = :vin and all_vkbz.vkbz = all_vincode.verkaufstyp
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $sData = $query->fetchAll();
        foreach ($sData as $item)
        {
            $filtrDvig = substr($item['ltr'],0,1).','.substr($item['ltr'],1,1);
            $filtrNameDvig = $item['mkb'];
        }



        $sql = "
        SELECT all_katalog.id, all_katalog.bildtafel, all_katalog.grafik, all_katalog.bildtafel2
        FROM all_katalog
        WHERE all_katalog.catalog = 'au'
        and all_katalog.epis_typ = :modificationCode
        and  LEFT(hg_ug, 1) = :groupCode
        and all_katalog.bildtafel <> ''
        and dir_name = 'R'
        ";


        /*
                $sql = "
                SELECT all_katalog.hg_ug, all_katalog.tsben, all_katalog.bildtafel2, all_katalog.modellangabe, ou
                FROM all_katalog
                WHERE all_katalog.catalog = 'au'
                and all_katalog.epis_typ = :modificationCode
                and  LEFT(hg_ug, 1) = :groupCode
                ";*/

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();
        $ObDvig = array();


        foreach($aData as $item)

        {
            $sqlSub = "
        SELECT all_katalog.hg_ug, all_katalog.tsben, all_katalog.bildtafel2, all_katalog.modellangabe, ou, all_katalog.tsbem
        FROM all_katalog
        WHERE all_katalog.id = :item +1
        ";

            $query = $this->conn->prepare($sqlSub);
            $query->bindValue('item',  $item['id']);
            $query->execute();

            $aDataSub[$item['bildtafel2']] = $query->fetch();
            $aDataSub[$item['bildtafel2']]['grafik'] = $item['grafik'];

            $ObDvig[]=$this->getDesc($aDataSub[$item['bildtafel2']]['tsbem'], 'R');

            /*    $ObDvig[] = $aDataSub[$item['bildtafel2']]['tsbem'];*/
        }
        $sDataLitr = array();

        foreach($ObDvig as $index=>&$value)
        {
            preg_match_all("/\d{1}\,\d{1}/x",
                $value, $sDataLitr);

            foreach($sDataLitr as $item)
            {
                foreach($item as $item1)
                {
                    $sDataLitr0[] = $item1;
                }

            }


        }


        $subgroups = array();
        $subgroupsIndexes = array();

        foreach($aDataSub as $item)
        {

            $subgroups[$item['bildtafel2']] = array(

                Constants::NAME => $this->getDesc($item['tsben'], 'R'),
                Constants::OPTIONS => array('dannye'=>$item['modellangabe'],
                    'podgr'=>$item['hg_ug'],
                    'prime4'=>$this->getDesc($item['tsbem'], 'R'),
                    'grafik'=>substr($item['grafik'],strlen($item['grafik'])-3,3).'/'.substr($item['grafik'],strlen($item['grafik'])-3,3).substr($item['grafik'],1,5).substr($item['grafik'],0,1),
                    'ObDvig'=>(count(array_unique($sDataLitr0))>1)?array_unique($sDataLitr0):'')

            );

        }
        foreach($subgroups as $index => $value)
        {
            if(($value['options']['prime4'] != '') & (!strpos($value['options']['prime4'],$filtrDvig)) || ($value['options']['dannye'] != '') & (!strpos($value['options']['dannye'],$filtrNameDvig)))
            {
                unset ($subgroups[$index]);
            }

        }

        foreach($subgroups as $index => $value)
        {
            $subgroupsIndexes[] = $index;
        }

        return $subgroupsIndexes;
    }
   
   
        
} 