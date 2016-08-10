<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\SubaruBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\SubaruBundle\Components\SubaruConstants;

class SubaruVinModel extends SubaruCatalogModel {

    public function getVinFinderResult($vin)
    {

        $sqlRegion = "
        SELECT catalog
        FROM vin
        WHERE vin = :vin
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlRegion);
        $query->bindValue('vin', $vin);
        $query->execute();

        $sRegion = $query->fetchColumn(0);

        if ($sRegion == 'JP'){
            $table = 'model_desc_jp';
            $models = 'models_jp';
            $model_changes = 'model_changes_jp';
            $lang = 'jp';
        }
        else
        {
            $table = 'model_desc';
            $models = 'models';
            $model_changes = 'model_changes';
            $lang = 'en';
        }


        $sqlVin = "
        SELECT vin.catalog, vin.sub_wheel, vin.Model_code, vin.date1, vin.color_code, vin.Trim_code, $models.desc_$lang m_desc,
        $model_changes.change_abb, $model_changes.desc_$lang mc_desc,
        body_desc.f1 as compl_code,
        body_desc.body p1,
        body_desc.engine1 p2,
        body_desc.train p3,
        body_desc.trans p4,
        body_desc.grade p5,
        body_desc.sus p6,
        body_desc.f2 p7,
        body_desc.f3 p8,

        kig1.param_name ken1,
        kig2.param_name ken2,
        kig3.param_name ken3,
        kig4.param_name ken4,
        kig5.param_name ken5,
        kig6.param_name ken6,
        kig7.param_name ken7,
        kig8.param_name ken8
        FROM vin

        INNER JOIN $models ON ($models.model_code = vin.Model_code AND $models.catalog = vin.catalog)
        INNER JOIN body ON (body.body = vin.Body_model)
        LEFT JOIN body_desc ON (body_desc.catalog = body.catalog AND body_desc.model_code = body.model_code AND TRIM(body_desc.id) = TRIM(body.body_desc_id))

        LEFT JOIN $table kig1 ON (kig1.catalog = body_desc.catalog AND kig1.model_code = body_desc.model_code AND kig1.param_abb = body_desc.body)
        LEFT JOIN $table kig2 ON (kig2.catalog = body_desc.catalog AND kig2.model_code = body_desc.model_code AND kig2.param_abb = body_desc.engine1)
        LEFT JOIN $table kig3 ON (kig3.catalog = body_desc.catalog AND kig3.model_code = body_desc.model_code AND kig3.param_abb = body_desc.train)
        LEFT JOIN $table kig4 ON (kig4.catalog = body_desc.catalog AND kig4.model_code = body_desc.model_code AND kig4.param_abb = body_desc.trans)
        LEFT JOIN $table kig5 ON (kig5.catalog = body_desc.catalog AND kig5.model_code = body_desc.model_code AND kig5.param_abb = body_desc.grade)
        LEFT JOIN $table kig6 ON (kig6.catalog = body_desc.catalog AND kig6.model_code = body_desc.model_code AND kig6.param_abb = body_desc.sus)
        LEFT JOIN $table kig7 ON (kig7.catalog = body_desc.catalog AND kig7.model_code = body_desc.model_code AND kig7.param_abb = body_desc.f2)
        LEFT JOIN $table kig8 ON (kig8.catalog = body_desc.catalog AND kig8.model_code = body_desc.model_code AND kig8.param_abb = body_desc.f3)

        LEFT JOIN  $model_changes ON ($model_changes.catalog = vin.catalog
        AND $model_changes.model_code = body.model_code
        AND $model_changes.change_abb = SUBSTRING(vin.Body_model, 4, 1))

        WHERE vin = :vin
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlVin);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();

        $sql = "
        SELECT dif_code, dif_fields
        FROM $models
        WHERE $models.catalog = :regionCode
        AND $models.model_code = :model_code
        AND $models.sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $aData['catalog']);
        $query->bindValue('model_code', $aData['Model_code']);
        $query->bindValue('wheel', $aData['sub_wheel']);
        $query->execute();

        $aAgregateNames = $query->fetchAll();

        $aAgregateData = array();

        $aVozmozhnyeZna4s = array('STEERING', 'DESTINAT', 'TRAIN', 'ENGINE', 'GRADE', 'ROOF', 'VEHICLE', 'SPECIFIC', 'MISSION', 'DOOR', 'SUS', 'BODY');




        $aAgregate = array();
        $adif_code = array();

        if ($sRegion != 'JP')
        {
            foreach ($aAgregateNames as $aAgregateName){
                $sdif_fields = $aAgregateName['dif_fields'];
                foreach(str_split($aAgregateName['dif_code']) as $index => $value){
                    $adif_code[$index] = $value;
                }
            }

            foreach ($aVozmozhnyeZna4s as $aVozmozhnyeZna4)
            {
                foreach($adif_code as $index => $value)
                {
                        if (strpos(trim($sdif_fields), $aVozmozhnyeZna4) !== false)
                        {
                            $aAgregate[strpos(trim($sdif_fields), $aVozmozhnyeZna4)] =  $aVozmozhnyeZna4;

                        }

                }

            }
            ksort($aAgregate);
            reset($aAgregate);

        }

        else
        {
            foreach ($aAgregateNames as $aAgregateName)
            {

                $aAgregate = explode(' ',  $aAgregateName['dif_fields']);

            }
        }



        foreach ($aAgregateNames as $aAgregateName)
        {
            $aAgregateData = array_combine(str_split($aAgregateName['dif_code']) , ($sRegion == 'JP')?array_values(array_diff($aAgregate, array(''))) : array_values(array_diff(array_unique($aAgregate), array(''))));
        }


        $aComplectation = array();


        $result = array();
        $psevd = array();

        $af = array();


            for ($i = 1; $i < 9; $i++) {
                if ($aData['p' . $i]) {
                    $af[$i][$aData['p' . $i]] = '(' . $aData['p' . $i] . ') ' . $aData['ken' . $i];
                    $result['p'.$i] = $af[$i];
                    $psevd['p'.$i] = str_replace('ENGINE 1', 'ENGINE', $aAgregateData[$i]);

                    foreach ($result['p'.$i] as $index => $value)
                    {
                        $aComplectation[$i] = $psevd['p'.$i].": ".$value;

                    }

                }
            }


            $result = array();

            if ($aData){
                $result = array(
                'region' => $aData['catalog'].'_'.$aData['sub_wheel'],
                'model' => '('.$aData['Model_code'].') '.$aData['m_desc'],
                'prod_year' => $aData['date1'],
                'modification' => $aData['change_abb'].$aData['mc_desc'],
                'complectation' => $aData['compl_code'],
                'com' => $aComplectation,
                'ext_color' => $aData['color_code'],
                'Trim_code'=>$aData['Trim_code'],
                Constants::PROD_DATE => $aData['date1']
            );
            }

        return $result;

    }
    
     public function getVinCompl($regionCode, $modelCode, $complectationCode)
     {
	 	 $sql = "
        SELECT *
        FROM body_desc
        WHERE catalog = :regionCode 
        AND model_code = :model_code
        AND f1 = :f1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('f1', $complectationCode);
        $query->execute();

        $aCompl = $query->fetch();
        return $aCompl;
	 }
    
    public function getVinSchemas($regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));
        
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