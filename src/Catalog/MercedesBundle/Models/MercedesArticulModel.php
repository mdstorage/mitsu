<?php
namespace Catalog\MercedesBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\MercedesBundle\Components\MercedesConstants;

class MercedesArticulModel extends MercedesCatalogModel{

    private function getArticulCatnums($articul)
    {
        $catnums = array();

        $sqlCatnums = "
        SELECT CATNUM
        FROM `alltext_bm_parts2_v`
        WHERE PARTTYPE = :parttype
        AND PARTNUM = :partnum
        AND CALLOUT != ''
        GROUP BY CATNUM
        ";

        $query = $this->conn->prepare($sqlCatnums);
        $query->bindValue('parttype', substr($articul, 0, 1));
        $query->bindValue('partnum', str_pad(substr($articul, 1), 12, " ", STR_PAD_LEFT));
        $query->execute();

        $aData = $query->fetchAll();

        return $this->array_column($aData, 'CATNUM');
    }

    public function getArticulRegions($articul){

        $articulCatnums = $this->getArticulCatnums($articul);

        $regions = array();

        $sqlRegions = "
        SELECT
            group_concat(APPINF SEPARATOR '') string
        FROM
            (SELECT DISTINCT
                trim(' ' FROM APPINF) APPINF
            FROM
                alltext_models_v
            WHERE CATNUM IN (?) ) t;
        ";

        $query = $this->conn->executeQuery($sqlRegions, array(
            $articulCatnums
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $sData = $query->fetchColumn(0);

        $aData = array_unique(str_split($sData));

        foreach ($aData as $item) {
            if ($item !== " " && $item !== "") {
                $regions[] = $item;
            }
        }

        return $regions;
    }

    public function getArticulModels($articul, $regionCode)
    {
        $articulCatnums = $this->getArticulCatnums($articul);

        $sqlModels = "
        SELECT DISTINCT
            amv.CLASS
        FROM
            alltext_models_v amv
        WHERE amv.CATNUM IN (?)
        AND APPINF LIKE ?
        GROUP BY amv.CLASS;
        ";

        $query = $this->conn->executeQuery($sqlModels, array(
            $articulCatnums,
            '%'.$regionCode.'%'
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            \PDO::PARAM_STR
        ));

        $aData = $query->fetchAll();
        $models = array();

        foreach ($aData as $item) {
            $models[] = $item['CLASS'];
        }

        return $models;
    }

    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $articulCatnums = $this->getArticulCatnums($articul);

        $sqlModifications = "
        SELECT DISTINCT
            models.AGGTYPE
        FROM
            mercedesbenz.alltext_models_v models
        WHERE models.CATNUM IN (?)
        AND APPINF LIKE ?
        AND CLASS = ?
        ";

        $query = $this->conn->executeQuery($sqlModifications, array(
            $articulCatnums,
            '%'.$regionCode.'%',
            $modelCode
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR
        ));

        $aData = $query->fetchAll();
        $modifications = array();

        foreach ($aData as $item) {
            $modifications[] = $item['AGGTYPE'];
        }

        return $modifications;
    }

    public function getArticulComplectations($articul)
    {
        $articulCatnums = $this->getArticulCatnums($articul);

        $sqlComplectations = "
        SELECT DISTINCT
            IF (models.SUBBM2 != '', concat(models.TYPE, '.', models.SUBBM1, '-', models.SUBBM2), concat(models.TYPE, '.', models.SUBBM1)) COMPLECTATION,
            models.CATNUM
        FROM
            alltext_models_v as models
        WHERE models.CATNUM IN (?)
        ";

        $query = $this->conn->executeQuery($sqlComplectations, array(
            $articulCatnums
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aData = $query->fetchAll();
        $complectations = array();

        foreach ($aData as $item) {
            $complectations[] = $item['CATNUM'] . "." . $item['COMPLECTATION'];
        }

        return $complectations;
    }

    public function getArticulGroups($articul, $complectationCode)
    {

        $sqlGroups = "
        SELECT GROUPNUM
        FROM alltext_bm_parts2_v
        WHERE PARTTYPE = :parttype
        AND PARTNUM = :partnum
        AND CATNUM = :complectationCode
        AND CALLOUT != ''
        GROUP BY GROUPNUM
        ";

        $query = $this->conn->prepare($sqlGroups);
        $query->bindValue('parttype', substr($articul, 0, 1));
        $query->bindValue('partnum', str_pad(substr($articul, 1), 12, " ", STR_PAD_LEFT));
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->execute();

        $aData = $query->fetchAll();
        $groups = $this->array_column($aData, 'GROUPNUM');

        return $groups;
    }

    public function getArticulSubGroups($articul, $complectationCode, $groupCode)
    {

        $sqlSubGroups = "
        SELECT SUBGRP
        FROM `alltext_bm_parts2_v`
        WHERE PARTTYPE = :parttype
        AND PARTNUM = :partnum
        AND CATNUM = :complectationCode
        AND GROUPNUM = :groupCode
        AND CALLOUT != ''
        GROUP BY SUBGRP
        ";

        $query = $this->conn->prepare($sqlSubGroups);
        $query->bindValue('parttype', substr($articul, 0, 1));
        $query->bindValue('partnum', str_pad(substr($articul, 1), 12, " ", STR_PAD_LEFT));
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subGroups = $this->array_column($aData, 'SUBGRP');

        return $subGroups;
    }

    public function getArticulSchemas($articul, $complectationCode, $groupCode, $subGroupCode)
    {
        $sqlSchema = "
        SELECT image_name
        FROM `bm_images_arc_image_v` image
        WHERE image.desc IN (?)
        GROUP BY image_name

        UNION

        SELECT image_name
        FROM `bm_images_image_v` image
        WHERE image.desc IN (?)
        GROUP BY image_name
        ";

        $query = $this->conn->executeQuery($sqlSchema, array(
            $this->getArticulPncs($articul, $complectationCode, $groupCode, $subGroupCode),
            $this->getArticulPncs($articul, $complectationCode, $groupCode, $subGroupCode)
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aData = $query->fetchAll();

        $schemas = $this->array_column($aData, 'image_name');

        return $schemas;
    }

    public function getArticulPncs($articul, $complectationCode, $groupCode, $subGroupCode)
    {
        $sqlPncs = "
        SELECT CAST(CALLOUT as UNSIGNED) CALLOUT
        FROM `alltext_bm_parts2_v`
        WHERE PARTTYPE = :parttype
        AND PARTNUM = :partnum
        AND CATNUM = :complectationCode
        AND GROUPNUM = :groupCode
        AND SUBGRP = :subGroupCode
        AND CALLOUT != ''
        GROUP BY CALLOUT
        ";

        $query = $this->conn->prepare($sqlPncs);
        $query->bindValue('parttype', substr($articul, 0, 1));
        $query->bindValue('partnum', str_pad(substr($articul, 1), 12, " ", STR_PAD_LEFT));
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subGroups = $this->array_column($aData, 'CALLOUT');

        return $subGroups;
    }
} 