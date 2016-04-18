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
        SELECT lrec.model_id, lrec.engine_type, vin.date_output, lrec.auto_name, lex_en.lex_name lexen, vin_description.Manual_Auto_Transmission_Desc as lextr
        FROM vin
        LEFT JOIN vin_group ON (vin_group.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN vin_description ON (vin_description.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN eng ON (:vin LIKE CONCAT(eng.vin_part, '%'))
        LEFT JOIN avsmodel ON (avsmodel.model_code = eng.eng_part)
        LEFT JOIN lrec ON (lrec.engine_type = SUBSTRING(avsmodel.model_auto, 3, 2))
        LEFT JOIN lex lex_en ON (lex_en.lex_code = eng.eng_part AND lex_en.lang = 'EN')

        where vin.vin = :vin
        ";

        }

        else
        {

            $sqlvin = "
        SELECT lrec.model_id, lrec.engine_type, vin.date_output, lrec.auto_name, lex_en.lex_name lexen, lex_tr.lex_name lextr
        FROM vin
        LEFT JOIN vin_group ON (vin_group.vin_desc_offset = vin.vin_desc_offset)
        LEFT JOIN vin_evvl ON (:vin LIKE CONCAT(vin_evvl.vin_vmi, '%'))
        LEFT JOIN lrec ON (lrec.auto_code = vin_evvl.power_model)
        LEFT JOIN vin_detail vin_detail_en ON (vin_detail_en.vin_index = vin.vin_index AND vin_detail_en.detail_name LIKE CONCAT('EN', '%'))
        LEFT JOIN vin_detail vin_detail_tr ON (vin_detail_tr.vin_index = vin.vin_index AND vin_detail_tr.detail_name LIKE CONCAT('TR', '%'))
        LEFT JOIN lex lex_en ON (lex_en.lex_code IN (vin_detail_en.detail_name) AND lex_en.lang = 'EN')
        LEFT JOIN lex lex_tr ON (lex_tr.lex_code IN (vin_detail_tr.detail_name) AND lex_tr.lang = 'EN')

        where vin.vin = :vin
        ";

        }
        $query = $this->conn->prepare($sqlvin);
        $query->bindValue('vin', $vin);

        $query->execute();

        $aData = $query->fetchAll();




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
                'engine' => $aData[0]['lexen'],
                'transmission' => $aData[0]['lextr'],

                Constants::PROD_DATE => $aData[0]['date_output'],

                );
        }



        return $result;
    }


} 