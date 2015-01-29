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
            $regions[] = $item['catalog'];
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

        $models = $this->array_column($aData, 'model_name');

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

        $modifications = $this->array_column($query->fetchAll(), 'catalog_number');

        return $modifications;
    }

    public function getArticulGroups($articulCode, $modificationCode)
    {
        $sqlGroup = "
        SELECT s.pgroup
        FROM sgroup s
        WHERE s.catalog_number = ?
          AND s.sgroup IN (?)
        GROUP BY s.pgroup
        ";

        $query = $this->conn->executeQuery($sqlGroup, array(
            $modificationCode,
            $this->getArticulSubGroups($articulCode, $modificationCode)
        ), array(
            \PDO::PARAM_STR,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));


        $aData = $query->fetchAll();

        $groups = $this->array_column($aData, 'pgroup');

        return $groups;
    }

    public function getArticulSubGroups($articulCode, $modificationCode)
    {
        $sqlSubGroup = "
        SELECT pc.sgroup
        FROM part_catalog pc
        WHERE pc.part_name = :articulCode
          AND pc.catalog_number = :modificationCode
        GROUP BY pc.sgroup
        ";

        $query = $this->conn->prepare($sqlSubGroup);
        $query->bindValue('articulCode', $articulCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $subGroups = $this->array_column($query->fetchAll(), 'sgroup');

        return $subGroups;
    }

    public function getArticulSchemas($articulCode, $modificationCode, $subGroupCode)
    {
        $sqlSchema = "
        SELECT p.pic_name
        FROM pictures p
        WHERE p.part_code IN (?) OR p.part_code = ?
        GROUP BY p.pic_name
        ";

        $query = $this->conn->executeQuery($sqlSchema, array(
            $this->getArticulPncs($articulCode, $modificationCode, $subGroupCode),
            $articulCode
        ), array(
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            \PDO::PARAM_STR
        ));

        $aData = $query->fetchAll();

        $schemas = $this->array_column($aData, 'pic_name');

        return $schemas;
    }

    public function getArticulPncs($articulCode, $modificationCode, $subGroupCode)
    {
        $sqlPnc = "
        SELECT pc.dcod
        FROM part_catalog pc
        WHERE pc.part_name = :articulCode
            AND pc.catalog_number = :modificationCode
            AND pc.sgroup = :subGroupCode
        GROUP BY pc.dcod
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('articulCode', $articulCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $pncs = $this->array_column($query->fetchAll(), 'dcod');

        return $pncs;
    }
} 