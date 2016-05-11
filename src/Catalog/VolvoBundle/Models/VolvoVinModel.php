<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\VolvoBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\VolvoBundle\Components\VolvoConstants;

class VolvoVinModel extends VolvoCatalogModel {

    public function getVinFinderResult($vin)
    {


        $sql = "
        SELECT model.MAKE_DESC, vin_archive2.MODEL_YEAR, catalog_model_string.CATALOG_CODE, model.MODEL_DESC, country.COUNTRY_CODE
        FROM vin_archive2
        INNER JOIN catalog_model_string ON (catalog_model_string.MODEL_STRING = vin_archive2.ATTRIBUTE5
        AND vin_archive2.MODEL_YEAR BETWEEN catalog_model_string.FIRST_YEAR AND catalog_model_string.LAST_YEAR)
        INNER JOIN model ON (model.CATALOG_CODE = catalog_model_string.CATALOG_CODE)
        INNER JOIN vin_partition ON (vin_partition.START_POS = 1 AND vin_partition.END_POS = 1)
        INNER JOIN vin_rule country ON (country.VIN_PARTITION_ID = vin_partition.VIN_PARTITION_ID AND country.MATCH_VALUE = SUBSTRING(:vin, 1, 1))
        WHERE vin_archive2.VIN_CHAR9 = SUBSTRING(:vin, 1, 9)
        AND vin_archive2.VIN_CHAR2 = SUBSTRING(:vin, 10, 2)
        AND vin_archive2.VIN_CHAR6 = SUBSTRING(:vin, 12, 6)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();




        $result = array();

        if ($aData) {
            $result = array(
                'brand' => $aData['MAKE_DESC'],
                'model' => strtoupper((stripos($aData['MODEL_DESC'],' '))?substr($aData['MODEL_DESC'], 0, stripos($aData['MODEL_DESC'],' ')):$aData['MODEL_DESC']),
                'modif_for_group' => $aData['MODEL_YEAR'],
                'complectation' => $aData['CATALOG_CODE'].'_'.$aData['MODEL_DESC'],
                Constants::PROD_DATE => $aData['MODEL_YEAR'],
                'region' => $aData['COUNTRY_CODE'],
                );
        }


        return $result;
    }


} 