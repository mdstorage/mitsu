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
                'complectation' => $aData['MDLCD'] . $aData['MSCSPCCD'],
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

    public function getVinOptionsSet($regionCode, $modificationCode, $complectationCode, $complectationSubCode)
    {
        $sqlOptionsSet = "
        SELECT m.`XC26OPT1`
        FROM model3 m
        WHERE m.catalog = :regionCode
          AND m.catalog_number = :modificationCode
          AND m.MDLCD = :MDLCD
          AND m.`MSCSPCCD` = :complectationSubCode
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlOptionsSet);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('MDLCD', $complectationCode);
        $query->bindValue('complectationSubCode', $complectationSubCode);
        $query->execute();

        $sqlOptionsSet = $query->fetchColumn(0);

        return $sqlOptionsSet;
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

    public function getVinSchemas($regionCode, $modificationCode, $complectationCode, $subComplectationCode, $subGroupCode)
    {
        $additionalParameters = $this->getVinAdditionalParameters($regionCode, $modificationCode, $complectationCode);
        $optionsSet = $this->getVinOptionsSet($regionCode, $modificationCode, $complectationCode, $subComplectationCode);

        $sqlSchemas = "
        SELECT sp.pic_name, sp.`XC26SLSU`, sp.`XC26SKSU`, sp.XC26TKT1
        FROM sgroup_pics sp
        WHERE sp.catalog = :regionCode
            AND sp.catalog_number = :modificationCode
            AND sp.sgroup = :subGroupCode
            AND sp.pic_name != ''
            AND sp.lang = 1
        UNION
        SELECT s3.XC26ILFL, s3.`XC26SLSU`, s3.`XC26SKSU`, s3.XC26TKT1
        FROM sgroup3 s3
        WHERE s3.catalog = :regionCode
            AND s3.catalog_number = :modificationCode
            AND s3.XC26PSNO = :subGroupCode
            AND s3.XC26ILFL != ''
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();



        $schemas = array();

        foreach ($aData as $item) {
            $array = array_intersect(explode(" ", $item['XC26TKT1']), explode(" ", $additionalParameters));
$all[] = explode(" ", $item['XC26TKT1']);
            if (($item['XC26SKSU'] == 0 && $item['XC26SLSU'] == 0) || ($array || substr_count($optionsSet, $item['XC26TKT1']) > 0)) {
                $schemas[] = $item['pic_name'];
            }
        }
var_dump($all);die;
        return $schemas;
    }

} 