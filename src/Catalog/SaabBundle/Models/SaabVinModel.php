<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\SaabBundle\Models;

use Catalog\CommonBundle\Components\Constants;

class SaabVinModel extends SaabCatalogModel
{
    public function getVinFinderResult($vin)
    {
        $sql = "
       select distinct
vin_carline.CarLine carline,
vin_market.RUS market,
vin_bodytype.RUS bodytype,
vin_gearbox.RUS gearbox,
vin_engine.RUS engine,
vin_assemblyplant.RUS assemblyplant,
vin_year.nYear nYear,
model.MODEL_NO model_no
from vin_carline, vin_year, vin_market, vin_bodytype, vin_gearbox, vin_engine, vin_assemblyplant, model
where vin_carline.id = SUBSTRING(:vin,4,1) and vin_year.nYear BETWEEN vin_carline.FROM_YEAR AND vin_carline.TO_YEAR
and vin_year.Code = SUBSTRING(:vin,10,1)
and vin_market.ID = SUBSTRING(:vin,5,1) and vin_year.nYear BETWEEN vin_market.From_Year AND vin_market.To_Year
and vin_bodytype.ID = SUBSTRING(:vin,6,1) and vin_year.nYear BETWEEN vin_bodytype.From_Year AND vin_bodytype.To_Year
and vin_gearbox.ID = SUBSTRING(:vin,7,1) and vin_year.nYear BETWEEN vin_gearbox.From_Year AND vin_gearbox.To_Year
and vin_engine.ID = SUBSTRING(:vin,8,1) and vin_year.nYear BETWEEN vin_engine.From_Year AND vin_engine.To_Year
and vin_assemblyplant.ID = SUBSTRING(:vin,11,1) and vin_year.nYear BETWEEN vin_assemblyplant.From_Year AND vin_assemblyplant.To_Year
and REPLACE (vin_carline.CarLine , 'Saab ' , '') = model.TYPE_OF_CAR and vin_year.nYear BETWEEN model.FROM_MODEL_YEAR AND model.TO_MODEL_YEAR
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();
        if ($aData) {
            $result = [
                'model'              => $aData['carline'],
                'market'             => iconv('cp1251', 'utf8', $aData['market']),
                Constants::PROD_DATE => $aData['nYear'],
                'bodytype'           => iconv('cp1251', 'utf8', $aData['bodytype']),
                'gearbox'            => iconv('cp1251', 'utf8', $aData['gearbox']),
                'engine'             => iconv('cp1251', 'utf8', $aData['engine']),
                'assemblyplant'      => iconv('cp1251', 'utf8', $aData['assemblyplant']),
                'model_no'           => $aData['model_no'],
                'serial'             => substr($vin, strlen($vin) - 6, strlen($vin)),
            ];
        }
        return $result;
    }
} 
