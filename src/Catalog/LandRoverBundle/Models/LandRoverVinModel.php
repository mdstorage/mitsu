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
        INNER JOIN eng ON (:vin LIKE CONCAT(eng.vin_part, '%' ))
        INNER JOIN avsmodel ON (avsmodel.model_code = eng.eng_part)
        INNER JOIN lrec ON (lrec.engine_type = SUBSTRING(avsmodel.model_auto, 3, 2))

        where vin.vin = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);

        $query->execute();

        $aData = $query->fetchAll();








        $result = array();

        if ($aData) {
            $result = array(
                'model_for_groups' => $aData[0]['model_id'].'_'.(ctype_alpha($aData[0]['engine_type'])?'GC'.$aData[0]['engine_type']:$aData[0]['engine_type']),

                Constants::PROD_DATE => $aData[0]['date_output'],

                );
        }



        return $result;
    }


} 