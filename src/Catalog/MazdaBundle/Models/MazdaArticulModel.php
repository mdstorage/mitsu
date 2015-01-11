<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\MazdaBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\MazdaBundle\Components\MazdaConstants;

class MazdaArticulModel extends MazdaCatalogModel{

    public function getArticulRegions($articul){

        $sql = "
        SELECT catalog
        FROM models
        GROUP BY catalog
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[$item['catalog']] = array(Constants::NAME=>$item['catalog'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getArticulModels($articulCode)
    {
        $sql = "
        SELECT c.model_name
        FROM catalog c
        WHERE c.lang = 1 AND c.catalog_number IN (?)
        GROUP BY c.model_name
        ";

        $query = $this->conn->executeQuery($sql, array(
            $this->getArticulModifications($articulCode)
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));


        $aData = $query->fetchAll();

        $models = array_column($aData, 'model_name');

        return $models;
    }

    public function getArticulModifications($articulCode)
    {
        $sqlArticul = "
        SELECT pc.catalog_number
        FROM part_catalog pc
        WHERE pc.part_name = :articulCode
        GROUP BY pc.catalog_number
        ";

        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $modifications = array_column($query->fetchAll(), 'catalog_number');

        return $modifications;
    }
} 