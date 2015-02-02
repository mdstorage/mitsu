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
        GROUP BY catalog";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[$item['catalog']] = array(Constants::NAME=>$item['catalog'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getArticulModels($articul)
    {
        $sql = "
        SELECT model_name
        FROM catalog
        WHERE lang = 1 AND prod_year = '1994'
        GROUP BY model_name
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $models = array_values($aData);

        return $models;
    }
} 