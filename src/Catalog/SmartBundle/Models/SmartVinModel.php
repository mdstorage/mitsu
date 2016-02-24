<?php
namespace Catalog\SmartBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\SmartBundle\Components\SmartConstants;

class SmartVinModel extends SmartCatalogModel
{
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
        WHERE (map.VINWHC = :vinwhc AND map.VIN = :vin) OR (map.WHC = :vinwhc AND
          map.CHASSBM = :chassbm AND map.CHASS_IDENT = :chassident)
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
    /**
     * Поиск конкретной модели по vin-коду
     *
     * @param $vin
     * @return array
     */
    public function getVinFinderResult($vin)
    {
        $result = array();

        $aVin = $this->getIdentDataByVin($vin);

        foreach ($aVin as &$item) {
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

            $item = $item + $aModel[0];

        }

        if ($aVin) {
            $aData = $aVin[0];

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

            if ($aData) {
                $result = array(
                    'region' => trim($aVin[0]['MARKET']),
                    'model' => trim($aData['CLASS']),
                    'saledes' => trim($aData['SALESDES']),
                    'prod_year' => substr($aInfo['DDATE'], 0, 1) > 6 ? '19' . $aInfo['DDATE'] : '20' . $aInfo['DDATE'],
                    'modification' => $aData['AGGTYPE'],
    //                'country' => $aData['XC26EDST'],
                    'complectation' => $aData['CATNUM'] . '.' . $aData['TYPE'] . '.' .  $aData['SUBBM1'],
    //                'ext_color' => $aData['ext_color'],
    //                'int_color' => $aData['int_color'],
                    Constants::PROD_DATE => substr($aInfo['RDATE'], 0, 1) > 6 ? '19' . $aInfo['RDATE'] : '20' . $aInfo['RDATE']
                );
            }
        }

        return $result;
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

    public function getArticulsByVin($vin)
    {
        $sacodes = $this->getRtype3ByVin($vin);
        return $sacodes;
    }
}