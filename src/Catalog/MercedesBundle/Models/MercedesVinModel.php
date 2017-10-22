<?php

namespace Catalog\MercedesBundle\Models;

use Catalog\CommonBundle\Components\Constants;

class MercedesVinModel extends MercedesCatalogModel
{
    /**
     * Поиск конкретной модели по vin-коду
     *
     * @param $vin
     *
     * @return array
     */
    public function getVinFinderResult($vin, $commonVinFind = false)
    {
        $result = [];
        $aModel = [];

        $aVin = $this->getIdentDataByVin($vin);

        if(!$aVin){
            return null;
        }

        foreach ($aVin as $index => &$item) {
            $sqlModels = "
                SELECT DISTINCT
                    *
                FROM
                    alltext_models_v models
                WHERE models.APPINF LIKE :regionCode
                  AND models.TYPE = :modelstype
                  AND models.SUBBM1 = :subbm1;
                ";

            $query = $this->conn->prepare($sqlModels);
            $query->bindValue('regionCode', '%' . $item['MARKET'] . '%');
            $query->bindValue('modelstype', substr($item['CHASSBM'], 0, 3));
            $query->bindValue('subbm1', substr($item['CHASSBM'], 3, 3));
            $query->execute();

            $aModel = $query->fetchAll();
            foreach ($aModel as &$value) {
                $value = $value + $item;
                unset($value);
            }/*оказывается, что автомобиль может быть не один, и поэтому добавление нулевого элемента $aModel[0] ошибочно. Исправлено 17.01.17*/

            unset($item);
        }

        if ($aModel) {
            foreach ($aModel as $index => $aData) {

                $tableName = strtolower($aData['DB_NAME'] . "_DC_RTYPE1_V");

                $sqlInfo = "
            SELECT *,
              IF (info.DELIVERY_DATE != '', info.DELIVERY_DATE, info.RELEASE_DATE) DDATE,
              IF (info.RELEASE_DATE != '', info.RELEASE_DATE, info.DELIVERY_DATE) RDATE
            FROM " . $tableName . " info
            WHERE info.WHC = :whc
             AND info.CHASSBM = :chassbm
             AND info.CHASS_IDENT = :chassIdent
            ";

                $query = $this->conn->prepare($sqlInfo);
                $query->bindValue('whc', $aData['WHC']);
                $query->bindValue('chassbm', $aData['CHASSBM']);
                $query->bindValue('chassIdent', $aData['CHASS_IDENT']);
                $query->execute();

                $aInfo = $query->fetch();

                switch (trim($aData['MARKET'])) {
                    case '1':
                        $region_RU = 'Европа';
                        break;

                    case 'F':
                        $region_RU = 'Северная Америка';
                        break;

                    case 'S':
                        $region_RU = 'Япония';
                        break;

                    case 'W':
                        $region_RU = 'Латинская Америка';
                        break;

                    case 'K':
                        $region_RU = 'Южная Африка';
                        break;

                    case 'M':
                        $region_RU = 'Smart';
                        break;

                    case 'P':
                        $region_RU = 'Агрегаты';
                        break;
                }

                $result[Constants::PROD_DATE] = substr($aInfo['RDATE'], 0,
                    1) > 6 ? '19' . $aInfo['RDATE'] : '20' . $aInfo['RDATE'];
                $result[$index]               = [
                    'marka'              => 'MERCEDESBENZ',
                    'region'             => trim($aData['MARKET']),
                    'region_RU'          => $region_RU,
                    'model'              => trim($aData['CLASS']),
                    'saledes'            => trim($aData['SALESDES']),
                    'prod_year'          => substr($aInfo['DDATE'], 0,
                        1) > 6 ? '19' . $aInfo['DDATE'] : '20' . $aInfo['DDATE'],
                    'modification'       => $aData['AGGTYPE'],
                    //                'country' => $aData['XC26EDST'],
                    'complectation'      => trim($aData['CATNUM']) . '.' . $aData['TYPE'] . '.' . $aData['SUBBM1'],
                    //                'ext_color' => $aData['ext_color'],
                    //                'int_color' => $aData['int_color'],
                    Constants::PROD_DATE => substr($aInfo['RDATE'], 0,
                        1) > 6 ? '19' . $aInfo['RDATE'] : '20' . $aInfo['RDATE'],
                ];
            }
            if ($commonVinFind) {
                $urlParams        = [
                    'path'   => 'vin_mercedes_groups',
                    'params' => [
                        'regionCode'       => $result[0]['region'],
                        'modelCode'        => $result[0]['model'],
                        'modificationCode' => $result[0]['modification'],
                        'complectationCode'    => $result[0]['complectation'],
                    ],
                ];
                $removeFromResult = ['saledes', 'region'];
                return ['result'    => array_diff_key($result[0], array_flip($removeFromResult)),
                        'urlParams' => $urlParams,
                ];
            }
        }

        return $result;
    }

    public function getArticulsByVin($vin)
    {
        $sacodes = $this->getRtype3ByVin($vin);
        return $sacodes;
    }

    private function getIdentDataByVin($vin)
    {
        $sqlVin = "
        SELECT
          IF (SUBSTRING(map.LOCATION, 1, 1) = 'E', '1',
            CASE RIGHT (map.LOCATION, 2)
              WHEN 'NA' THEN 'F'
              WHEN 'JA' THEN 'S'
              WHEN 'LA' THEN 'W'
              WHEN 'SA' THEN 'K'
              WHEN 'SM' THEN 'M'
            END
            ) MARKET,
          map.LOCATION, map.WHC, map.CHASSBM, map.CHASS_IDENT,
          db.DB_NAME, db.TABLES
        FROM comm_dc_map_v map
        LEFT JOIN special_dcdbinfo_v db ON map.LOCATION = db.LOCATION
        WHERE ((map.VINWHC = :vinwhc AND map.VIN = :vin) OR (map.WHC = :vinwhc AND
          map.CHASSBM = :chassbm AND map.CHASS_IDENT = :chassident)) AND RIGHT (map.LOCATION, 2) NOT LIKE 'SM'
        ";

        $query = $this->conn->prepare($sqlVin);
        $query->bindValue('vinwhc', substr($vin, 0, 3));
        $query->bindValue('vin', substr($vin, 3));
        $query->bindValue('chassbm', substr($vin, 3, 6));
        $query->bindValue('chassident', substr($vin, 9));
        $query->execute();

        $aVin = $query->fetchAll();

        return $aVin;
    }

    private function getRtype3ByVin($vin)
    {
        $aVin = $this->getIdentDataByVin($vin);

        $sSaCode = '';

        if ($aVin) {
            $aData = $aVin[0];

            $tableName = strtolower($aData['DB_NAME'] . "_DC_RTYPE3_V");

            $sqlInfo = "
            SELECT SACODE_DATA
            FROM " . $tableName . " info
            WHERE info.WHC = :whc
             AND info.CHASSBM = :chassbm
             AND info.CHASS_IDENT = :chassIdent
            ";

            $query = $this->conn->prepare($sqlInfo);
            $query->bindValue('whc', $aData['WHC']);
            $query->bindValue('chassbm', $aData['CHASSBM']);
            $query->bindValue('chassIdent', $aData['CHASS_IDENT']);
            $query->execute();

            $sSaCode = $query->fetchColumn();
        }

        $aSacodes = explode(" ", $sSaCode);
        return $aSacodes;
    }
}
