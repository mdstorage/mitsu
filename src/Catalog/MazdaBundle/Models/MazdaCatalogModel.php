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

class MazdaCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT catalog
        FROM models
        GROUP BY catalog";

        $smth = $this->conn->query($sql);

        $aData = $smth->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[$item['catalog']] = array(Constants::NAME=>$item['catalog'], Constants::OPTIONS=>array());
        }
        $regions['JP'] = array(Constants::NAME=>'JP', Constants::OPTIONS=>array());

        return $regions;

    }
    public function getModels($regionCode)
    {
        $models = array();
        $models['1'] = array(Constants::NAME=>$regionCode.' model', Constants::OPTIONS=>array());

        return $models;
    }
} 