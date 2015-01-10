<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\MazdaBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\MazdaBundle\Components\MazdaConstants;

class MazdaVinModel extends MazdaCatalogModel {

    public function getVinFinderResult($vin)
    {
        $sql = "
        SELECT v.catalog, v.catalog_number, v.XC26EDST, v.MDLCD, v.MSCSPCCD, v.ext_color, v.int_color, v.prod_date
        FROM vin v
        WHERE v.VIN = :vin
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();

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
                'complectation' => $aData['MDLCD'].$aData['MSCSPCCD'],
                'ext_color' => $aData['ext_color'],
                'int_color' => $aData['int_color'],
                Constants::PROD_DATE => $aData['prod_date']
            );
        }

        return $result;
    }

} 