<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\ChevroletUsaBundle\Models;

use Catalog\CommonBundle\Components\Constants;

class ChevroletUsaVinModel extends ChevroletUsaCatalogModel
{

    public function getVinFinderResult($vin, $commonVinFind = false)
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
        AND (model.MAKE_DESC = 'Chevrolet' OR model.MAKE_DESC = 'Lt Truck Chevrolet')
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();

        if (!$aData) {
            return null;
        }
        $model = strtoupper((stripos($aData['MODEL_DESC'], ' ')) ? substr($aData['MODEL_DESC'],
                0, stripos($aData['MODEL_DESC'], ' ')) : $aData['MODEL_DESC']) . '_' . $aData['MAKE_DESC'];
        $modifForGroup = $aData['MODEL_YEAR'];

        $result = [
            'marka'              => 'ChevroletUsa',
            'brand'              => $aData['MAKE_DESC'],
            'model'              => $model,
            'modif_for_group'    => $modifForGroup,
            'complectation'      => $aData['CATALOG_CODE'] . '_' . $aData['MODEL_DESC'],
            Constants::PROD_DATE => $aData['MODEL_YEAR'],
            'region'             => $aData['COUNTRY_CODE'],
        ];

        if ($commonVinFind) {
            $urlParams        = [
                'path'   => 'vin_chevroletusa_groups',
                'params' => [
                    'regionCode'        => $aData['COUNTRY_CODE'],
                    'modelCode'         => urlencode($model),
                    'modificationCode'  => $modifForGroup,
                    'complectationCode' => urlencode($aData['CATALOG_CODE'] . '_' . $aData['MODEL_DESC']),
                ],
            ];
            $removeFromResult = ['modif_for_group', 'brand'];
            return ['result' => array_diff_key($result, array_flip($removeFromResult)), 'urlParams' => $urlParams];
        }
        return $result;
    }


} 
