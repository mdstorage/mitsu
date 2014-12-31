<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\MazdaBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\MazdaBundle\Components\MazdaConstants;

class MazdaCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT catalog
        FROM models
        GROUP BY catalog";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[$item['catalog']] = array(Constants::NAME=>$item['catalog'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT model_name
        FROM catalog
        WHERE lang = 1
        GROUP BY model_name
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item){
            $models[md5($item['model_name'])] = array(Constants::NAME=>$item['model_name'], Constants::OPTIONS=>array());
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sql = "
        SELECT catalog_number, prod_year, prod_date, carline
        FROM catalog
        WHERE model_name = :modelCode AND lang = 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['catalog_number']] = array(
                Constants::NAME     => $item['catalog_number'],
                Constants::OPTIONS  => array(
                    MazdaConstants::PROD_YEAR   => $item['prod_year'],
                    MazdaConstants::PROD_DATE   => $item['prod_date'],
                    MazdaConstants::CARLINE     => $item['carline']
            ));
        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {
        $sql = "
        SELECT CONCAT(`MDLCD`, `MSCSPCCD`) as complectation
        FROM model3
        WHERE catalog = :regionCode AND catalog_number = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();
        foreach($aData as $item){
            $complectations[$item['complectation']] = array(
                Constants::NAME     => $item['complectation'],
                Constants::OPTIONS  => array()
            );
        }

        return $complectations;
    }

    public function getGroups($regionCode, $modelCode, $modificationCode)
    {
        $sql = "
        SELECT `id`, `descr`
        FROM pgroups
        WHERE catalog = :regionCode AND catalog_number = :modificationCode AND lang = 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();
        foreach($aData as $item){
            $groups[$item['id']] = array(
                Constants::NAME     => $item['descr'],
                Constants::OPTIONS  => array()
            );
        }

        return $groups;
    }

    public function getGroup($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $sql = "
        SELECT pg.cd, pg.descr, pp.pic_name
        FROM pgroups pg
        LEFT JOIN pgroup_pics pp ON (pg.id = pp.id AND pg.catalog = pp.catalog AND pg.catalog_number = pp.catalog_number)
        WHERE pg.catalog = :regionCode
            AND pg.catalog_number = :modificationCode
            AND pg.id = :groupCode
            AND pg.lang = 1
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetch();

        $group = array(Constants::NAME => $aData['descr'], Constants::OPTIONS => array(Constants::CD => $aData['cd'], Constants::PICTURE => $aData['pic_name']));

        return $group;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $sqlGroup = "
        SELECT pp.cd, pp.pic_name
        FROM pgroups pg
        LEFT JOIN pgroup_pics pp ON (pg.id = pp.id AND pg.catalog = pp.catalog AND pg.catalog_number = pp.catalog_number)
        WHERE pg.catalog = :regionCode
            AND pg.catalog_number = :modificationCode
            AND pg.id = :groupCode
            AND pg.lang = 1
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlGroup);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aGroup = $query->fetch();

        $sqlPicture = "
            SELECT p.part_code, p.xs, p.ys, p.xe, p.ye
            FROM pictures p
            WHERE p.cd = :cd
              AND p.pic_name = :picName
        ";

        $query = $this->conn->prepare($sqlPicture);
        $query->bindValue('cd', $aGroup['cd']);
        $query->bindValue('picName', $aGroup['pic_name']);
        $query->execute();

        $aPicture = $query->fetchAll();

        $labels = array();
        foreach ($aPicture as $label){
            $labels[$label['part_code']] = $label;
        }

        $sqlSubgroups = "
        SELECT sg.sgroup, sg.descr
        FROM sgroup sg
        WHERE sg.catalog = :regionCode
            AND sg.catalog_number = :modificationCode
            AND sg.pgroup = :groupCode
            AND sg.lang = 1
        ";

        $query = $this->conn->prepare($sqlSubgroups);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subgroups = array();
        foreach($aData as $item){
            $subgroups[$item['sgroup']] = array(
                Constants::NAME => $item['descr'],
                Constants::OPTIONS => array(
                    Constants::X1 => $labels[substr($item['sgroup'], 0, 4)]['xs'],
                    Constants::X2 => $labels[substr($item['sgroup'], 0, 4)]['xe'],
                    Constants::Y1 => $labels[substr($item['sgroup'], 0, 4)]['ys'],
                    Constants::Y2 => $labels[substr($item['sgroup'], 0, 4)]['ye']
                )
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode)
    {
        $sqlSchemas = "
        SELECT sp.cd, sp.pic_name, sp.descr
        FROM sgroup_pics sp
        WHERE sp.catalog = :regionCode
            AND sp.catalog_number = :modificationCode
            AND sp.sgroup = :subGroupCode
            AND sp.lang = 1
        UNION
        SELECT s3.cd, s3.XC26ILFL, s3.XC26TKT1
        FROM sgroup3 s3
        WHERE s3.catalog = :regionCode
            AND s3.catalog_number = :modificationCode
            AND s3.XC26PSNO = :subGroupCode
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        foreach($aData as $item){
            if($item['pic_name']){
                $schemas[$item['pic_name']] = array(
                    Constants::NAME => $item['descr'],
                    Constants::OPTIONS => array(
                        Constants::CD => $item['cd']
                    )
                );
            }
        }

        return $schemas;
    }
} 