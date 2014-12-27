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
        for ($i=1; $i<10; $i++){
            $models[$i] = array(Constants::NAME=>$regionCode.' model'.$i, Constants::OPTIONS=>array());
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modifications = array();
        for ($i=1; $i<10; $i++){
            $modifications[$i] = array(Constants::NAME=>$regionCode.$modelCode.' modification'.$i, Constants::OPTIONS=>array());
        }

        return $modifications;
    }
} 