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
            IF (SUBSTRING(map.LOCATION, 1, 1) IN ('P', 'C'), SUBSTRING(map.LOCATION, 1, 1),
              IF (SUBSTRING(map.LOCATION, 1, 1) = 'E', '1',
                CASE map.LOCATION
                  WHEN 'NA' THEN 'F'
                  WHEN 'JA' THEN 'S'
                  WHEN 'LA' THEN 'W'
                  WHEN 'SA' THEN 'K'
                  WHEN 'SM' THEN 'M'
                END
              )
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

        $aData = $query->fetchAll();

        foreach ($aData as &$item) {
            $sqlModels = "
                SELECT DISTINCT
                    *
                FROM
                    alltext_models_v amv
                WHERE amv.APPINF LIKE :regionCode;
                ";

            $query = $this->conn->prepare($sqlModels);
            $query->bindValue('regionCode', '%' . $item['MARKET'] . '%');
            $query->execute();

            $aModel = $query->fetchAll();

            $item['model'] = $aModel;

        }


        var_dump($aData);die;

        $sql = "
        SELECT c.model_name, c.prod_year
        FROM catalog c
        WHERE c.catalog_number = :modificationCode
         AND lang = 1
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $aData['catalog_number']);
        $query->execute();

        $aModel = $query->fetch();

        $result = array();

        if ($aData) {
            $result = array(
                'region' => $aData['catalog'],
                'model' => $aModel['model_name'],
                'prod_year' => $aModel['prod_year'],
                'modification' => $aData['catalog_number'],
                'country' => $aData['XC26EDST'],
                'complectation' => $aData['MDLCD'] . $aData['MSCSPCCD'],
                'ext_color' => $aData['ext_color'],
                'int_color' => $aData['int_color'],
                Constants::PROD_DATE => $aData['prod_date']
            );
        }

        return $result;
    }
}