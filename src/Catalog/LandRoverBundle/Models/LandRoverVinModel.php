<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\LandRoverBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\LandRoverBundle\Components\LandRoverConstants;

class LandRoverVinModel extends LandRoverCatalogModel {

    public function getVinFinderResult($vin)
    {

        $sql = "
        SELECT *
        FROM vin
        INNER JOIN vin_group ON (vin_group.vin_desc_offset = vin.vin_desc_offset)
        INNER JOIN vin_description ON (vin_description.vin_desc_offset = vin.vin_desc_offset)

        where vin.vin = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);

        $query->execute();

        $aData = $query->fetchAll();

        var_dump($aData); die;




        $complectations = $this->getComplectations($aData[0]['CATALOG'], $aData[0]['SHASHUKO'], $aData[0]['MODSERIES']);
        $complectation = $complectations[str_pad($aData[0]['MDLDIR'], 3, "0", STR_PAD_LEFT).'_'.$aData[0]['POSNUM'].'_'.$aData[0]['DATA1']];


       for ($i = 1; $i < 9; $i++)
        {
            $OnlyCompl[] = $complectation['options']['OPTION'.$i];
        }




        $result = array();

        if ($aData) {
            $result = array(
                'model' => urlencode($aData[0]['SHASHUKO']),
                'modif' => $aData[0]['MODSERIES'],
                'complectation' => $OnlyCompl,
                Constants::PROD_DATE => $aData[0]['PRODYM'],

                'region' => $aData[0]['CATALOG'],
                LandRoverConstants::INTCOLOR => $aData[0]['COLOR1'],
                'cvet_salona' => $aData[0]['COL1'],
                'cvet_kuzova' => $aData[0]['COL2'],
                'kod_complektacii' => str_pad($aData[0]['MDLDIR'], 3, "0", STR_PAD_LEFT).'_'.$aData[0]['POSNUM'].'_'.$aData[0]['DATA1'],
                );
        }



        return $result;
    }


} 