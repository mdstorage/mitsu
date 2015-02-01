<?php
namespace Catalog\MercedesBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\MercedesBundle\Components\MercedesConstants;

class MercedesVinModel extends MercedesCatalogModel
{
    /**
     * Поиск конкретной модели по vin-коду
     *
     * @param $vin
     * @return array
     */
    public function getVinFinderResult($vin)
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
        WHERE map.VINWHC = :vinwhc
          AND map.VIN = :vin
        ";

        $query = $this->conn->prepare($sqlVin);
        $query->bindValue('vinwhc', substr($vin, 0, 3));
        $query->bindValue('vin', substr($vin, 3));
        $query->execute();

        $aVin = $query->fetchAll();

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
//        var_dump($aInfo);die;

        if ($aData) {
            $result = array(
                'region' => $aData['APPINF'],
                'model' => $aData['DB_NAME'],
                'prod_year' => substr($aInfo['DDATE'], 1, 1) > 6 ? '19' . $aInfo['DDATE'] : '20' . $aInfo['DDATE'],
                'modification' => $aData['SALESDES'],
//                'country' => $aData['XC26EDST'],
//                'complectation' => $aData['MDLCD'] . $aData['MSCSPCCD'],
//                'ext_color' => $aData['ext_color'],
//                'int_color' => $aData['int_color'],
                Constants::PROD_DATE => substr($aInfo['RDATE'], 1, 1) > 6 ? '19' . $aInfo['RDATE'] : '20' . $aInfo['RDATE']
            );
        }

        return $result;
    }
}