<?php
namespace Catalog\MercedesBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\MercedesBundle\Components\MercedesConstants;

class MercedesArticulModel extends MercedesCatalogModel{

    public function getArticulRegions($articul){

        $regions = array();

        $sqlRegions = "
        SELECT
            group_concat(APPINF SEPARATOR '') string
        FROM
            (SELECT DISTINCT
                trim(' ' FROM APPINF) APPINF
            FROM
                alltext_models_v
            WHERE CATNUM = '048') t;
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

    public function getArticulModels()
    {
        $models = array();
        return $models;
    }

} 