<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\AudiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\AudiBundle\Components\AudiConstants;

class AudiVinModel extends AudiCatalogModel {

    public function getVinFinderResult($vin)
    {
        
        $sql = "
        SELECT *
        FROM all_vincode
        LEFT JOIN all_vkbz on (all_vkbz.vkbz = all_vincode.verkaufstyp and all_vkbz.mkb_4 = all_vincode.motorkennbuchstable)
        left join all_overview on (all_overview.einsatz = all_vincode.model_year and all_overview.epis_typ = all_vkbz.epis_typ)
        WHERE vin = :vin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();


        $result = array();

        if ($aData) {
            $result = array(
             /*   'model_for_groups' => urlencode($aDataModif['family']),*/
                'model' => $this->getDesc($aData['vkb_ts_text'],'R'),
                Constants::PROD_DATE => $aData['prod_date'],
                'mod_god' => $aData['model_year'],
                'mod_kod' => $aData['verkaufstyp'],
                'dvig_kod' => $aData['motorkennbuchstable'],
                'kpp_kod' => $aData['getriebekkenbuchstable'],
                'osn' => substr($aData['int_roof_ext'], strlen($aData['int_roof_ext'])-2, 2),
                'kuzovcolor' => substr($aData['int_roof_ext'], 0, 2),
                'kry6acolor' => substr($aData['int_roof_ext'], 2, 2),
                'modif' => $aData['einsatz'].'_'.$aData['epis_typ'],
                'model_for_groups' => $aData['modell'],
                'region' => $aData['markt']
            );
        }


        return $result;
    }
    
    
    public function getVinSchemas($regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $sqlSchemas = "
        SELECT *
        FROM part_images
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND sec_group = :subGroupCode
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        
        foreach($aData as $item){
		
		 if ((substr_count($item['desc_en'],'MY')>0)&&(substr_count($item['desc_en'], $modificationCode)!=0)||(substr_count($item['desc_en'],'MY')==0))
		           
                $schemas[] = $item['image_file'];
        }

        return $schemas;
    }
   
   
        
} 