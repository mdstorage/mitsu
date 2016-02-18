<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\FiatBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\FiatBundle\Components\FiatConstants;

class FiatVinModel extends FiatCatalogModel {

    public function getVinFinderResult($vin)
    {

        $sql = "
        SELECT vin_chassis.mvs, motor, vin_chassis.date, mvs.cat_cod, catalogues.cat_dsc, comm_modgrp.cmg_dsc, brands.title, comm_modgrp.cmg_cod
        FROM vin_chassis, mvs, catalogues, comm_modgrp, brands
        WHERE vin = :vin
        AND mvs.mod_cod = SUBSTRING(vin_chassis.mvs, 1, 3)
        AND mvs.mvs_version = SUBSTRING(vin_chassis.mvs, 4, 3)
        AND mvs.mvs_serie = SUBSTRING(vin_chassis.mvs, 7, 1)
        AND catalogues.cat_cod = mvs.cat_cod
        AND comm_modgrp.cmg_cod = catalogues.cmg_cod
        AND comm_modgrp.mk2_cod = catalogues.mk2_cod
        AND comm_modgrp.mk2_cod = brands.eper_submake
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();



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
                );
        }


        return $result;
    }


} 