<?php
namespace Catalog\MercedesBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;

class MercedesCatalogModel extends CatalogModel{

    public function getRegions()
    {
        $regions = array();

        $sqlRegions = "
        SELECT
            group_concat(APPINF SEPARATOR '') string
        FROM
            (SELECT DISTINCT
                trim(' ' FROM APPINF) APPINF
            FROM
                alltext_models_v) t;
        ";

        $query = $this->conn->query($sqlRegions);

        $sData = $query->fetchColumn(0);

        $aData = array_unique(str_split($sData));

        foreach ($aData as $item) {
            if ($item !== " ") {
                $regions[$item] = array(
                    Constants::NAME => 'region_' . $item,
                    Constants::OPTIONS => array()
                );
            }
        }

        return $regions;
    }

    public function getModels($regionCode)
    {
        $sqlModels = "
        SELECT DISTINCT
            amv.CLASS
        FROM
            alltext_models_v amv
        WHERE amv.APPINF LIKE :regionCode;
        ";

        $query = $this->conn->prepare($sqlModels);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->execute();

        $aData = $query->fetchAll();
        $models = array();

        foreach ($aData as $item) {
            $models[$item['CLASS']] = array(
                Constants::NAME => 'model_' . $item['CLASS'],
                Constants::OPTIONS => array()
            );
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sqlModifications = "
        SELECT DISTINCT
            AGGTYPE,
            descr.AGGTYPE_DESC
        FROM
            mercedesbenz.alltext_models_v models
        LEFT JOIN alltext_aggtype_code_map_v descr ON TRIM(models.AGGTYPE) = TRIM(descr.DAG_AGGTYPE_CODE)
        WHERE models.APPINF LIKE :regionCode AND models.CLASS = :modelCode;
        ";

        $query = $this->conn->prepare($sqlModifications);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();

        foreach ($aData as $item) {
            $modifications[$item['AGGTYPE']] = array(
                Constants::NAME => $item['AGGTYPE_DESC'],
                Constants::OPTIONS => array()
            );
        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {
        $sqlComplectations = "
        SELECT DISTINCT
            IF (models.SUBBM2 != '', concat(models.TYPE, '.', models.SUBBM1, '-', models.SUBBM2), concat(models.TYPE, '.', models.SUBBM1)) COMPLECTATION,
            models.SALESDES TRADEMARK,
            models.CATNUM,
            models.REMARKS
        FROM
            alltext_models_v as models
        WHERE
            models.APPINF LIKE :regionCode
            AND models.CLASS = :modelCode
            AND models.AGGTYPE = :modificationCode
        ";

        $query = $this->conn->prepare($sqlComplectations);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();

        foreach ($aData as $item) {
            $complectations[$item['CATNUM'] . "." . $item['COMPLECTATION']][Constants::NAME] = $item['TRADEMARK'];
            $complectations[$item['CATNUM'] . "." . $item['COMPLECTATION']][Constants::OPTIONS]['REMARKS'] = $item['REMARKS'];
        }
        return $complectations;
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $complectationCode = substr($complectationCode, 0, 3);
        $sqlGroups = "
            SELECT
                GROUPS.GROUPNUM, IFNULL(DIC_RU.TEXT, DIC_EN.TEXT) DESCR
            FROM
                alltext_bm_group_v GROUPS
                LEFT OUTER JOIN alltext_bm_dictionary_v DIC_RU
                    ON DIC_RU.DESCIDX = GROUPS.DESCIDX
                    AND DIC_RU.LANG = 'R'
                LEFT OUTER JOIN alltext_bm_dictionary_v DIC_EN
                    ON DIC_EN.DESCIDX = GROUPS.DESCIDX
                    AND DIC_EN.LANG = 'E'
            WHERE
                GROUPS.CATNUM = :complectationCode
            ORDER BY
                GROUPS.GROUPNUM
        ";

        $query = $this->conn->prepare($sqlGroups);
        $query->bindValue('complectationCode', $complectationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();
        foreach ($aData as $item) {
            $groups[$item['GROUPNUM']] = array(
                Constants::NAME => iconv('Windows-1251', 'UTF-8', $item['DESCR'])
            );
        }

        return $groups;
    }

    public function getComplectationAgregats($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $catnum = substr($complectationCode, 0, 3);
        $model = substr($complectationCode, 4);

        $sqlAggregates = "
        SELECT
            FRONTAX `VA`,
            STEER `LG`,
            REARAX `HA`,
            A_BODY `FH`,
            ENGINE `M`,
            PLATFRM `P`,
            AUTO `GA`,
            MANUAL `GM`,
            EXHAUSTSYS `AS`,
            EMOTOR `E`,
            HVBATTERY `B`,
            FUELCELL `N`
        FROM
            alltext_bm_spm_v details
        WHERE
            CATNUM = :complectationCode AND MODEL = :model;
        ";

        $query = $this->conn->prepare($sqlAggregates);
        $query->bindValue('complectationCode', $catnum);
        $query->bindValue('model', $model);
        $query->execute();

        $aData = $query->fetchAll();

        $slqAggTypes = "
        SELECT DAG_AGGTYPE_CODE, AGGTYPE_DESC
        FROM alltext_aggtype_code_map_v
        ";

        $aAggData = $this->conn->executeQuery($slqAggTypes)->fetchAll();
        $aAggs = array_combine($this->array_column($aAggData, 'DAG_AGGTYPE_CODE'), $this->array_column($aAggData, 'AGGTYPE_DESC'));

        $aggregates = array();
        foreach ($aData as $item) {
            foreach ($item as $key => $value) {
                if ($value) {
                    $aggregates[$key] = array(
                        Constants::NAME => $aAggs[$key],
                        Constants::OPTIONS => array(
                            'COMPLECTATIONS' => substr_count($value, " ") ? explode("      ", $value) : str_split($value, 7)
                        )
                    );
                    foreach ($aggregates[$key][Constants::OPTIONS]['COMPLECTATIONS'] as &$v) {
                        $v = $this->getCatnum($regionCode, $modelCode, $modificationCode, $v) . "." . $v;
                    }
                }
            }
        }

        return $aggregates;
    }

    public function getCatnum($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $type = substr($complectationCode, 0, 3);
        $sub1 = substr($complectationCode, 4, 3);
        $sub2 = substr($complectationCode, 8, 3) ?: '';

        $sqlCatnum = "
        SELECT CATNUM
        FROM alltext_models_v
        WHERE APPINF LIKE :regionCode
          AND AGGTYPE = :modelCode
          AND TYPE = :mtype
          AND SUBBM1 = :sub1
          AND SUBBM2 = :sub2
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlCatnum);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->bindValue('modelCode', $modificationCode);
        $query->bindValue('mtype', $type);
        $query->bindValue('sub1', $sub1);
        $query->bindValue('sub2', $sub2);
        $query->execute();

        $catnum = $query->fetchColumn(0);

        return $catnum;
    }
} 