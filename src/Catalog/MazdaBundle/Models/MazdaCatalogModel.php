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
} 