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

    public function getArticulModels($articul)
    {
        $articulCatnums = $this->getArticulCatnums($articul);

        $sqlModels = "
        SELECT DISTINCT
            amv.CLASS
        FROM
            alltext_models_v amv
        WHERE amv.CATNUM IN (?)
        GROUP BY amv.CLASS;
        ";

        $query = $this->conn->executeQuery($sqlModels, array(
            $articulCatnums
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aData = $query->fetchAll();
        $models = array();

        foreach ($aData as $item) {
            $models[] = $item['CLASS'];
        }

        return $models;
    }

    public function getArticulModifications($articul)
    {
        $articulCatnums = $this->getArticulCatnums($articul);

        $sqlModifications = "
        SELECT DISTINCT
            models.AGGTYPE
        FROM
            mercedesbenz.alltext_models_v models
        WHERE models.CATNUM IN (?);
        ";

        $query = $this->conn->executeQuery($sqlModifications, array(
            $articulCatnums
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
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
        $groups = array();

        $sqlGroups = "
        SELECT GROUPNUM
        FROM `alltext_bm_parts2_v`
        WHERE PARTTYPE = :parttype
        AND PARTNUM = :partnum
        AND CATNUM = :complectationCode
        GROUP BY GROUPNUM
        ";

        $query = $this->conn->prepare($sqlGroups);
        $query->bindValue('parttype', substr($articul, 0, 1));
        $query->bindValue('partnum', str_pad(substr($articul, 1), 12, " ", STR_PAD_LEFT));
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->execute();

        $aData = $query->fetchAll();

        return $this->array_column($aData, 'GROUPNUM');
    }
} 