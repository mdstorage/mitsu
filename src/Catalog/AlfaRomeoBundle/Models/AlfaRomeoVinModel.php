<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\AlfaRomeoBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\AlfaRomeoBundle\Components\AlfaRomeoConstants;

class AlfaRomeoVinModel extends AlfaRomeoCatalogModel {

    public function getVinFinderResult($vin)
    {

        $sql = "
        SELECT vin_chassis.mvs, motor, vin_chassis.date, mvs.cat_cod, catalogues.cat_dsc, comm_modgrp.cmg_dsc, brands.title, comm_modgrp.cmg_cod, mvs.mvs_dsc
        FROM vin_chassis, mvs, catalogues, comm_modgrp, brands, vin
        WHERE (vin_chassis.vin = :vin or (vin_chassis.chassy = RIGHT(:vin,8) AND SUBSTRING(vin_chassis.mvs, 1, 3) = SUBSTRING(:vin, 4, 3))
        OR (vin_chassis.chassy = RIGHT(:vin,8) AND vin.vin_cod = SUBSTRING(:vin, 4, 3)
        AND SUBSTRING(vin_chassis.mvs, 1, 3) = vin.mod_cod)
        )
        AND mvs.mod_cod = SUBSTRING(vin_chassis.mvs, 1, 3)
        AND mvs.mvs_version = SUBSTRING(vin_chassis.mvs, 4, 3)
        AND mvs.mvs_serie = SUBSTRING(vin_chassis.mvs, 7, 1)
        AND catalogues.cat_cod = mvs.cat_cod
        AND comm_modgrp.cmg_cod = catalogues.cmg_cod
        AND comm_modgrp.mk2_cod = catalogues.mk2_cod
        AND comm_modgrp.mk2_cod = brands.eper_submake
        AND brands.title = 'ALFA ROMEO'
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetchAll();


        $result = array();

        if ($aData) {
            foreach ($aData as $index => $value)
            {
                $result[Constants::PROD_DATE]=$value['date'];
                $result[$index] = array(
                    'brand' => $value['title'],
                    'model' => $value['cmg_dsc'],
                    'modif' => $value['cat_dsc'],
                    'modif_for_group' => $value['cat_cod'],
                    'model_for_group' => $value['cmg_cod'],
                    Constants::PROD_DATE => $value['date'],
                    'region' => 'EU',
                    'motor' => $value['motor'],
                    'mvs_dsc' => $value['mvs_dsc']
                );
            }

        }

        return $result;
    }


} 