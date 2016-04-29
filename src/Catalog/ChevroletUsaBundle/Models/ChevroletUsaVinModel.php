<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\ChevroletUsaBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\ChevroletUsaBundle\Components\ChevroletUsaConstants;

class ChevroletUsaVinModel extends ChevroletUsaCatalogModel {

    public function getVinFinderResult($vin)
    {


        $sql = "
        SELECT *
        FROM vin_archive2
        WHERE vin_archive2.VIN_CHAR9 = SUBSTRING(:vin, 1, 9)
        AND vin_archive2.VIN_CHAR2 = SUBSTRING(:vin, 10, 2)
        AND vin_archive2.VIN_CHAR6 = SUBSTRING(:vin, 12, 6)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetchAll();

        var_dump($aData); die;



        $result = array();

        if ($aData) {
            $result = array(
                'brand' => $aData['title'],
                'model' => $aData['cmg_dsc'],
                'modif' => $aData['cat_dsc'],
                'modif_for_group' => $aData['cat_cod'],
                'model_for_group' => $aData['cmg_cod'],
                Constants::PROD_DATE => $aData['date'],
                'region' => 'EU',
                'motor' => $aData['motor'],
                'mvs_dsc' => $aData['mvs_dsc']
                );
        }


        return $result;
    }


} 