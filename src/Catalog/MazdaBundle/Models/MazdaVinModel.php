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
                'complectation' => $aData['MDLCD'],
                'ext_color' => $aData['ext_color'],
                'int_color' => $aData['int_color'],
                Constants::PROD_DATE => $aData['prod_date']
            );
        }

        return $result;
    }

    public function getVinAdditionalParameters($regionCode, $modificationCode, $complectationCode)
    {
        $sqlAdditionalParameters = "
        SELECT m.XC26MVT1
        FROM model2 m
        WHERE m.catalog = :regionCode
          AND m.catalog_number = :modificationCode
          AND m.MDLCD = :MDLCD
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlAdditionalParameters);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('MDLCD', $complectationCode);
        $query->execute();

        $additionalParameters = $query->fetchColumn(0);

        return $additionalParameters;
    }

    public function getVinGroups($regionCode, $modificationCode, $complectationCode)
    {
        $additionalParameters = $this->getVinAdditionalParameters($regionCode, $modificationCode, $complectationCode);

        $sqlVinGroups = "
        SELECT p.id
        FROM pgroups p
        LEFT JOIN pgroup2 p2 ON (p.id = p2.id AND p.catalog = p2.catalog AND p.catalog_number = p2.catalog_number)
        WHERE p.catalog = ?
          AND p.catalog_number = ?
          AND p.lang = 1
          AND (p2.adds IN (?) OR p2.adds2 IN (?) OR p2.adds IS NULL)
        ";

        $query = $this->conn->executeQuery($sqlVinGroups, array(
            $regionCode,
            $modificationCode,
            explode(" ", $additionalParameters),
            explode(" ", $additionalParameters)
        ), array(
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aDataDescr = $query->fetchAll();

        return $this->array_column($aDataDescr, 'id');
    }

} 