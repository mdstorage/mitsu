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
        where vin.vin = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);

        $query->execute();

        $aDataVin = $query->fetchAll();



        if ($aDataVin[0]['vin_desc_offset'] != 0)
        {
            $sqlvin = "
        SELECT *
        FROM vin
        LEFT JOIN vin_group ON (vin_group.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN vin_description ON (vin_description.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN eng ON (:vin LIKE CONCAT(eng.vin_part, '%'))
        LEFT JOIN avsmodel ON (avsmodel.model_code = eng.eng_part)
        LEFT JOIN lrec ON (lrec.engine_type = SUBSTRING(avsmodel.model_auto, 3, 2))

        where vin.vin = :vin
        ";

        }

        else
        {

            $sqlvin = "
        SELECT *
        FROM vin
        LEFT JOIN vin_group ON (vin_group.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN vin_evvl ON (:vin LIKE CONCAT(vin_evvl.vin_vmi, '%'))
        LEFT JOIN lrec ON (lrec.auto_code = vin_evvl.power_model)
        LEFT JOIN vin_detail ON (vin_detail.vin_index = vin.vin_index)
        LEFT JOIN lex ON (lex.lex_code IN (vin_detail.detail_name) AND lex.lang = 'EN')

        where vin.vin = :vin
        ";

        }
        $query = $this->conn->prepare($sqlvin);
        $query->bindValue('vin', $vin);

        $query->execute();

        $aData = $query->fetchAll();
        var_dump($aData); die;



   /*     $sql = "
        SELECT *
        FROM vin
        LEFT JOIN vin_group ON (vin_group.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN vin_description ON (vin_description.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN vin_evvl ON (:vin LIKE CONCAT(vin_evvl.vin_vmi, '%'))
        LEFT JOIN lrec lrec_null ON (lrec_null.auto_code = vin_evvl.power_model)

        LEFT JOIN eng ON (:vin LIKE CONCAT(eng.vin_part, '%'))
        LEFT JOIN avsmodel ON (avsmodel.model_code = eng.eng_part)
        LEFT JOIN lrec lrec_n ON (lrec_n.engine_type = SUBSTRING(avsmodel.model_auto, 3, 2))

        where vin.vin = :vin
        ";*/



        $result = array();

        if ($aData) {
            $result = array(


                'model_for_groups' => $aData[0]['model_id'].'_'.(ctype_alpha($aData[0]['engine_type'])?'GC'.$aData[0]['engine_type']:$aData[0]['engine_type']),

                'model' => $aData[0]['auto_name'],

                Constants::PROD_DATE => $aData[0]['date_output'],

                );
        }



        return $result;
    }


} 