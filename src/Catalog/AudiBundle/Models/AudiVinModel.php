<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\AudiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

class AudiVinModel extends AudiCatalogModel
{


    public function getVinRegions($vin, $commonVinFind = false)
    {

        $sql = "
        SELECT markt
        FROM all_overview, all_vkbz, all_vincode
        WHERE (all_vkbz.epis_typ = all_overview.epis_typ and all_vkbz.vkbz = all_vincode.verkaufstyp and all_vkbz.mkb_4 = all_vincode.motorkennbuchstable
        and all_overview.einsatz = all_vincode.model_year and all_vincode.vin = :vin and all_overview.catalog = 'au')
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();
        $aData = $query->fetchAll();

        if (!$aData) {
            return null;
        }
        if ($commonVinFind) {
            $regionCodes = array_unique(array_map('current', $aData));
            foreach ($regionCodes as $regionCode) {
                $regionResult[$regionCode] = $this->getVinFinderResult($vin, $regionCode, $commonVinFind);
            }
            return $regionResult;
        }

        $result = [];
        foreach ($aData as $item) {
            $result[$item['markt']] = [Constants::NAME => $item['markt'], Constants::OPTIONS => []];
        }
        return $result;
    }

    public function getVinFinderResult($vin, $region, $commonVinFind)
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

        foreach ($aDatas as $aData) {
            switch ($aData['catalog']) {
                case 'au':
                    $marka = 'AUDI';
                    break;
                case 'se':
                    $marka = 'SEAT';
                    break;
                case 'vw':
                    $marka = 'VOLKSWAGEN';
                    break;
                case 'sk':
                    $marka = 'SKODA';
                    break;
                default:
                    $marka = null;
            }
        }
        switch ($region) {
            case 'RDW':
                $regionDesc = 'Europe';
                break;
            case 'BR':
                $regionDesc = 'Brazil';
                break;
            case 'MEX':
                $regionDesc = 'Mexico';
                break;
            case 'RA':
                $regionDesc = 'China';
                break;
            case 'ZA':
                $regionDesc = 'South Africa';
                break;
            case 'CA':
                $regionDesc = 'Canada';
                break;
            case 'USA':
                $regionDesc = 'USA';
                break;
            default:
                $regionDesc = $region;
        }

        $result = [];

        foreach ($aDatas as $aData) {
            $modif            = $aData['einsatz'] . '_' . $aData['epis_typ'];
            $model_for_groups = $aData['modell'];
            $model            = $this->getDesc($aData['vkb_ts_text'], 'R');
            $prodDate         = $aData['prod_date'];
            $engineCode       = $aData['motorkennbuchstable'];
            $transCode        = $aData['getriebekkenbuchstable'];
            $color            = substr($aData['int_roof_ext'], 0, 2);

            $result           = [
                'marka'              => $marka,
                'model'              => $model,
                Constants::PROD_DATE => $prodDate,
                'mod_god'            => $aData['model_year'],
                'mod_kod'            => $aData['verkaufstyp'],
                'dvig_kod'           => $engineCode,
                'kpp_kod'            => $transCode,
                'osn'                => substr($aData['int_roof_ext'], strlen($aData['int_roof_ext']) - 2, 2),
                'kuzovcolor'         => $color,
                'kry6acolor'         => substr($aData['int_roof_ext'], 2, 2),
                'modif'              => $modif,
                'model_for_groups'   => $model_for_groups,
                'region'             => $regionDesc,
            ];
        }
        if ($commonVinFind) {
            $commonVinResult = [
                'marka'              => $marka,
                'model'              => $model,
                'region'             => $regionDesc,
                Constants::PROD_DATE => $prodDate,
                'engine_code'        => $engineCode,
                'transmission_code'  => $transCode,
                'color'              => $color,

            ];
            $urlParams       = [
                'path'   => 'vin_audi_groups',
                'params' => [
                    'regionCode'       => $region,
                    'modelCode'        => $result['model_for_groups'],
                    'modificationCode' => $result['modif'],
                ],
            ];
            return ['result' => $commonVinResult, 'urlParams' => $urlParams];
        }

        return $result;
    }


    public function getVinSubgroups($regionCode, $modelCode, $modificationCode, $groupCode, $vin)
    {

        $modificationCode = substr($modificationCode, strpos($modificationCode, '_') + 1, strlen($modificationCode));

        $groupCode = (($groupCode == '10') ? '0' : $groupCode);

        $sql = "
        SELECT ltr,mkb
        FROM all_vincode, all_vkbz
        WHERE vin = :vin and all_vkbz.vkbz = all_vincode.verkaufstyp
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $sData = $query->fetchAll();
        foreach ($sData as $item) {
            $filtrDvig     = substr($item['ltr'], 0, 1) . ',' . substr($item['ltr'], 1, 1);
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

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData  = $query->fetchAll();
        $ObDvig = [];

        foreach ($aData as $item) {
            $sqlSub = "
        SELECT all_katalog.hg_ug, all_katalog.tsben, all_katalog.bildtafel2, all_katalog.modellangabe, ou, all_katalog.tsbem
        FROM all_katalog
        WHERE all_katalog.id = :item +1
        ";

            $query = $this->conn->prepare($sqlSub);
            $query->bindValue('item', $item['id']);
            $query->execute();

            $aDataSub[$item['bildtafel2']]           = $query->fetch();
            $aDataSub[$item['bildtafel2']]['grafik'] = $item['grafik'];

            $ObDvig[] = $this->getDesc($aDataSub[$item['bildtafel2']]['tsbem'], 'R');
            /*    $ObDvig[] = $aDataSub[$item['bildtafel2']]['tsbem'];*/
        }
        $sDataLitr  = [];
        $sDataLitr0 = [];

        foreach ($ObDvig as $index => &$value) {
            preg_match_all("/\d{1}\,\d{1}/x",
                $value, $sDataLitr);

            foreach ($sDataLitr as $item) {
                foreach ($item as $item1) {
                    $sDataLitr0[] = $item1;
                }
            }
        }

        $subgroups        = [];
        $subgroupsIndexes = [];

        foreach ($aDataSub as $item) {

            $subgroups[$item['bildtafel2']] = [

                Constants::NAME    => $this->getDesc($item['tsben'], 'R'),
                Constants::OPTIONS => [
                    'dannye' => $item['modellangabe'],
                    'podgr'  => $item['hg_ug'],
                    'prime4' => $this->getDesc($item['tsbem'], 'R'),
                    'grafik' => substr($item['grafik'], strlen($item['grafik']) - 3, 3) . '/' . substr($item['grafik'],
                            strlen($item['grafik']) - 3, 3) . substr($item['grafik'], 1, 5) . substr($item['grafik'], 0,
                            1),
                    'ObDvig' => (count(array_unique($sDataLitr0)) > 1) ? array_unique($sDataLitr0) : '',
                ],

            ];
        }
        foreach ($subgroups as $index => $value) {
            if ((!empty($value['options']['prime4']) && (stripos($value['options']['prime4'], $filtrDvig) === false))
                || (!empty($value['options']['dannye']) && (stripos($value['options']['dannye'],
                            $filtrNameDvig) === false))
            ) {
                unset ($subgroups[$index]);
            }
        }

        foreach ($subgroups as $index => $value) {
            $subgroupsIndexes[] = $index;
        }

        return $subgroupsIndexes;
    }

    public function getVinArticuls(
        $regionCode,
        $modelCode,
        $modificationCode,
        $groupCode,
        $subGroupCode,
        $pncCode,
        $vin
    ) {

        $modificationCode = substr($modificationCode, strpos($modificationCode, '_') + 1, strlen($modificationCode));

        $groupCode = (($groupCode == '10') ? '0' : $groupCode);

        $sql = "
        SELECT ltr,mkb
        FROM all_vincode, all_vkbz
        WHERE vin = :vin and all_vkbz.vkbz = all_vincode.verkaufstyp
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $sData = $query->fetchAll();
        foreach ($sData as $item) {
            $filtrDvig     = substr($item['ltr'], 0, 1) . ',' . substr($item['ltr'], 1, 1);
            $filtrNameDvig = $item['mkb'];
        }

        $sqlPnc = "
        SELECT all_katalog.teilenummer, all_katalog.tsben, all_katalog.tsbem, all_katalog.modellangabe, all_katalog.stuck, einsatz, auslauf, mv_data, all_stamm.gruppen_data newArt,
        all_stamm.entfalldatum dataOtmeny, all_katalog.bemerkung
        FROM all_katalog
        left join all_stamm on (all_stamm.catalog = all_katalog.catalog and (all_stamm.markt = :regionCode or all_stamm.markt = '') and all_stamm.teilenummer = all_katalog.teilenummer)
        WHERE all_katalog.catalog = 'au'
        and all_katalog.epis_typ = :modificationCode
        and  LEFT(hg_ug, 1) = :groupCode
        and all_katalog.bildtafel = ''
        and dir_name = 'R'
        and bildtafel2 = :subGroupCode
        and (btpos = :pncCode or btpos = :pncCodemod)

        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pncCode', '(' . $pncCode . ')');
        $query->bindValue('pncCodemod', $pncCode);
        $query->execute();

        $aArticuls = $query->fetchAll();

        $articuls        = [];
        $articulsIndexes = [];
        $k               = 0;

        foreach ($aArticuls as $item) {

            if (($this->getDesc($item['tsbem'], 'R'))) {
                $k = 1;
            }

            $articuls[$item['teilenummer']] = [
                Constants::NAME    => $this->getDesc($item['tsben'], 'R'),
                Constants::OPTIONS => [
                    Constants::QUANTITY   => $item['stuck'],
                    Constants::START_DATE => $item['einsatz'],
                    Constants::END_DATE   => $item['auslauf'],
                    'prime4'              => ($this->getDesc($item['tsbem'], 'R')) ? $this->getDesc($item['tsbem'],
                        'R') : $item['bemerkung'],
                    'dannye'              => $item['modellangabe'],
                    'with'                => $item['mv_data'],
                    'zamena'              => substr($item['newArt'], 0, strpos($item['newArt'], '~')),
                    'zamenakoli4'         => substr($item['newArt'], strpos($item['newArt'], '~'),
                        strlen($item['newArt'])),
                    'dataOtmeny'          => $item['dataOtmeny'],

                ],
            ];
        }
        foreach ($articuls as $index => $value) {

            if (($value['options']['prime4'] != '') & (strpos($value['options']['prime4'],
                        $filtrDvig) === false) || ($value['options']['dannye'] != '') & (strpos($value['options']['dannye'],
                        $filtrNameDvig) === false)
            ) {
                if ($k == 1) {
                    unset ($articuls[$index]);
                }
            }
        }

        foreach ($articuls as $index => $value) {
            $articulsIndexes[] = $index;
        }

        return $articulsIndexes;
    }


} 
