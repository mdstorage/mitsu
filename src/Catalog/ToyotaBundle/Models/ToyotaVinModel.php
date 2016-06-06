<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\ToyotaBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\ToyotaBundle\Components\ToyotaConstants;

class ToyotaVinModel extends ToyotaCatalogModel {

    public function getVinFinderResult($vin)
    {


        $sqlVin = "
        SELECT JAT.compl_code, JAT.catalog, JAT.catalog_code, JAT.model_code, shamei.model_name, shamei.models_codes, frames.vdate, frames.color_trim_code
        from johokt JAT
        INNER JOIN frames ON (frames.frame_code = JAT.frame AND frames.serial_number = RIGHT (:vin,7) AND JAT.model_code LIKE CONCAT ('%', frames.ext, '-',frames.model2, '%')
        AND frames.catalog = 'OV')
        INNER JOIN shamei ON (shamei.catalog = JAT.catalog AND shamei.catalog_code = JAT.catalog_code)
        where JAT.vin8 = SUBSTRING(:vin,1,8)
        ";

        $query = $this->conn->prepare($sqlVin);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetchAll();





        $complectations = $this->getComplectations($aData[0]['catalog'], $aData[0]['model_name'], $aData[0]['catalog_code']);
        $complectation = $complectations[$aData[0]['compl_code']];




       for ($i = 1; $i < 8; $i++)
        {
            $OnlyCompl[] = $complectation['options']['OPTION'.$i];
        }




        $result = array();

        if ($aData) {
            $result = array(
                'model' => urlencode($aData[0]['model_name']),
                'modelf' => ($aData[0]['model_name']),
                'modif' => $aData[0]['models_codes'],
                'complectation' => $OnlyCompl,
                Constants::PROD_DATE => $aData[0]['vdate'],

                'region' => $aData[0]['catalog'],
                ToyotaConstants::INTCOLOR => substr($aData[0]['color_trim_code'], 0, 3),
                'cvet_salona' => substr($aData[0]['color_trim_code'], 0, 3),
                'cvet_kuzova' => substr($aData[0]['color_trim_code'], -4),
                'kod_complektacii' => $aData[0]['compl_code'],
                'kod_modifikacii' => $aData[0]['catalog_code']
                );
        }



        return $result;
    }


} 