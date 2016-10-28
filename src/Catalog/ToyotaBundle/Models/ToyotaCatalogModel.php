<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\ToyotaBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\ToyotaBundle\Components\ToyotaConstants;

class ToyotaCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT catalog
        FROM shamei
        WHERE shamei.model_name NOT LIKE CONCAT('%', 'lexus', '%')
        GROUP BY catalog

        ";

        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();



        $name = array();
        $regions = array();

        foreach($aData as $item) {
            switch ($item['catalog']) {
                case 'EU':
                    $name[$item['catalog']] = 'ЕВРОПА';
                    break;
                case 'US':
                    $name[$item['catalog']] = 'США';
                    break;
                case 'JP':
                    $name[$item['catalog']] = 'ЯПОНИЯ';
                    break;
                case 'GR':
                    $name[$item['catalog']] = 'БЛИЖНИЙ ВОСТОК';
                    break;

            }
        }


        foreach($aData as $item)
        {
            $regions[$item['catalog']] = array(Constants::NAME=>$name[$item['catalog']], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {


            $sql = "
        SELECT model_name
        FROM shamei
        WHERE shamei.catalog = :regionCode
        and shamei.model_name NOT LIKE CONCAT('%', 'lexus', '%')
        ORDER by model_name
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[urlencode($item['model_name'])] = array(Constants::NAME=>strtoupper($item['model_name']),
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);



        $sql = "
        SELECT catalog_code, models_codes, prod_start, prod_end
        FROM shamei
        WHERE shamei.catalog = :regionCode and model_name =:modelCode
        ORDER by prod_start
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['catalog_code']] = array(
                Constants::NAME     => $item['models_codes'],
                Constants::OPTIONS  => array(
                    'models_codes' => $item['models_codes'],
                    Constants::START_DATE => $item['prod_start'],
                    Constants::END_DATE => $item['prod_end'],

                ));

        }

        return $modifications;
    }

    public function getComplectations1($regionCode, $modelCode, $modificationCode)
   
    {
        $sql = "
        SELECT johokt.engine1 as f1, johokt.engine2 as f2, johokt.body as f3, johokt.grade as f4, johokt.atm_mtm as f5, johokt.trans as f6,
        johokt.f1 as f7, johokt.f2 as f8, johokt.f3 as f9, johokt.f4 as f10, SUBSTRING_INDEX(johokt.f5, ' ', 1) as f11, compl_code, model_code, prod_start, prod_end,
        kig1.desc_en ken1,
        kig2.desc_en ken2,
        kig3.desc_en ken3,
        kig4.desc_en ken4,
        kig5.desc_en ken5,
        kig6.desc_en ken6,
        kig7.desc_en ken7,
        kig8.desc_en ken8,
        kig9.desc_en ken9,
        kig10.desc_en ken10,
        kig11.desc_en ken11,

        tkm1.desc_en ten1,
        tkm2.desc_en ten2,
        tkm3.desc_en ten3,
        tkm4.desc_en ten4,
        tkm5.desc_en ten5,
        tkm6.desc_en ten6,
        tkm7.desc_en ten7,
        tkm8.desc_en ten8,
        tkm9.desc_en ten9,
        tkm10.desc_en ten10,
        tkm11.desc_en ten11


        FROM johokt
        LEFT JOIN kig kig1 ON (kig1.catalog = johokt.catalog AND kig1.catalog_code = johokt.catalog_code AND kig1.type = '01' AND kig1.sign = johokt.engine1)
        LEFT JOIN kig kig2 ON (kig2.catalog = johokt.catalog AND kig2.catalog_code = johokt.catalog_code AND kig2.type = '02' AND kig2.sign = johokt.engine2)
        LEFT JOIN kig kig3 ON (kig3.catalog = johokt.catalog AND kig3.catalog_code = johokt.catalog_code AND kig3.type = '03' AND kig3.sign = johokt.body)
        LEFT JOIN kig kig4 ON (kig4.catalog = johokt.catalog AND kig4.catalog_code = johokt.catalog_code AND kig4.type = '04' AND kig4.sign = johokt.grade)
        LEFT JOIN kig kig5 ON (kig5.catalog = johokt.catalog AND kig5.catalog_code = johokt.catalog_code AND kig5.type = '05' AND kig5.sign = johokt.atm_mtm)
        LEFT JOIN kig kig6 ON (kig6.catalog = johokt.catalog AND kig6.catalog_code = johokt.catalog_code AND kig6.type = '06' AND kig6.sign = johokt.trans)
        LEFT JOIN kig kig7 ON (kig7.catalog = johokt.catalog AND kig7.catalog_code = johokt.catalog_code AND kig7.type = '07' AND kig7.sign = johokt.f1)
        LEFT JOIN kig kig8 ON (kig8.catalog = johokt.catalog AND kig8.catalog_code = johokt.catalog_code AND kig8.type = '08' AND kig8.sign = johokt.f2)
        LEFT JOIN kig kig9 ON (kig9.catalog = johokt.catalog AND kig9.catalog_code = johokt.catalog_code AND kig9.type = '09' AND kig9.sign = johokt.f3)
        LEFT JOIN kig kig10 ON (kig10.catalog = johokt.catalog AND kig10.catalog_code = johokt.catalog_code AND kig10.type = '10' AND kig10.sign = johokt.f4)
        LEFT JOIN kig kig11 ON (kig11.catalog = johokt.catalog AND kig11.catalog_code = johokt.catalog_code AND kig11.type = '11' AND kig11.sign = SUBSTRING_INDEX(johokt.f5, ' ', 1)
        )

        LEFT JOIN tkm tkm1 ON (tkm1.catalog = kig1.catalog AND tkm1.catalog_code = kig1.catalog_code AND tkm1.type = kig1.type)
        LEFT JOIN tkm tkm2 ON (tkm2.catalog = kig2.catalog AND tkm2.catalog_code = kig2.catalog_code AND tkm2.type = kig2.type)
        LEFT JOIN tkm tkm3 ON (tkm3.catalog = kig3.catalog AND tkm3.catalog_code = kig3.catalog_code AND tkm3.type = kig3.type)
        LEFT JOIN tkm tkm4 ON (tkm4.catalog = kig4.catalog AND tkm4.catalog_code = kig4.catalog_code AND tkm4.type = kig4.type)
        LEFT JOIN tkm tkm5 ON (tkm5.catalog = kig5.catalog AND tkm5.catalog_code = kig5.catalog_code AND tkm5.type = kig5.type)
        LEFT JOIN tkm tkm6 ON (tkm6.catalog = kig6.catalog AND tkm6.catalog_code = kig6.catalog_code AND tkm6.type = kig6.type)
        LEFT JOIN tkm tkm7 ON (tkm7.catalog = kig7.catalog AND tkm7.catalog_code = kig7.catalog_code AND tkm7.type = kig7.type)
        LEFT JOIN tkm tkm8 ON (tkm8.catalog = kig8.catalog AND tkm8.catalog_code = kig8.catalog_code AND tkm8.type = kig8.type)
        LEFT JOIN tkm tkm9 ON (tkm9.catalog = kig9.catalog AND tkm9.catalog_code = kig9.catalog_code AND tkm9.type = kig9.type)
        LEFT JOIN tkm tkm10 ON (tkm10.catalog = kig10.catalog AND tkm10.catalog_code = kig10.catalog_code AND tkm10.type = kig10.type)
        LEFT JOIN tkm tkm11 ON (tkm11.catalog = kig11.catalog AND tkm11.catalog_code = kig11.catalog_code AND tkm11.type = kig11.type)

        WHERE johokt.catalog = :regionCode
        AND johokt.catalog_code = :modificationCode
        ORDER BY compl_code
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();


        $complectations = array();
        $com = array();


        $result = array();
        $psevd = array();

        $af = array();

        foreach($aData as $item) {
            for ($i = 1; $i < 12; $i++) {
                if ($item['f' . $i]) {
                    $af[$i][$item['f' . $i]] = '(' . $item['f' . $i] . ') ' . $item['ken' . $i];
                    $result['f'.$i] = $af[$i];
                    $psevd['f'.$i] = str_replace('ENGINE 1', 'ENGINE', $item['ten' . $i]);

                }
            }


            $com[$item['compl_code']] = array(
                Constants::NAME => $result,
                Constants::OPTIONS => array('option1'=>$psevd)
            );
        }



 /*       foreach ($result as $index => $value) {

            $complectations[$index] = array(
                Constants::NAME => $value,
                Constants::OPTIONS => array('option1'=>$psevd)
            );
        }*/



        return $com;
     
    }

    public function getComplectationsForForm($complectations)
    {
        $result = array();


        foreach ($complectations as $index => $value) {

            for ($i = 1; $i < 12; $i++) {

                if (!empty($value['name']['f' . $i])) {
                    foreach ($value['name']['f' . $i] as $ind => $val) {
                        $result['f' . $i]['name'][urlencode($ind)] = $val;
                    }
                }


                if (!empty($value['options']['option1']['f' . $i])) {
                    foreach ($value['options']['option1'] as $ind => $val) {
                        $result['f' . $i]['options']['option1'][urlencode($ind)] = $val;
                    }
                }


            }

        }

        return ($result);


    }



    public function getComplectationsKorobka($regionCode, $modelCode, $modificationCode, $priznak, $engine)
    {

        switch($priznak)

        {
            case ('f1'): $pole = 'johokt.engine1';break;
            case ('f2'): $pole = 'johokt.engine2';break;
            case ('f3'): $pole = 'johokt.body';break;
            case ('f4'): $pole = 'johokt.grade';break;
            case ('f5'): $pole = 'johokt.atm_mtm';break;
            case ('f6'): $pole = 'johokt.trans';break;
            case ('f7'): $pole = 'johokt.f1';break;
            case ('f8'): $pole = 'johokt.f2';break;
            case ('f9'): $pole = 'johokt.f3';break;
            case ('f10'): $pole = 'johokt.f4';break;
            case ('f11'): $pole = 'SUBSTRING_INDEX(johokt.f5, " ", 1)';break;
        }

        $sql = "
        SELECT johokt.engine1 as f1, johokt.engine2 as f2, johokt.body as f3, johokt.grade as f4, johokt.atm_mtm as f5, johokt.trans as f6,
        johokt.f1 as f7, johokt.f2 as f8, johokt.f3 as f9, johokt.f4 as f10, johokt.f5 as f11, compl_code, model_code, prod_start, prod_end,
        kig1.desc_en ken1,
        kig2.desc_en ken2,
        kig3.desc_en ken3,
        kig4.desc_en ken4,
        kig5.desc_en ken5,
        kig6.desc_en ken6,
        kig7.desc_en ken7,
        kig8.desc_en ken8,
        kig9.desc_en ken9,
        kig10.desc_en ken10,
        kig11.desc_en ken11,

        tkm1.desc_en ten1,
        tkm2.desc_en ten2,
        tkm3.desc_en ten3,
        tkm4.desc_en ten4,
        tkm5.desc_en ten5,
        tkm6.desc_en ten6,
        tkm7.desc_en ten7,
        tkm8.desc_en ten8,
        tkm9.desc_en ten9,
        tkm10.desc_en ten10,
        tkm11.desc_en ten11


        FROM johokt
        LEFT JOIN kig kig1 ON (kig1.catalog = johokt.catalog AND kig1.catalog_code = johokt.catalog_code AND kig1.type = '01' AND kig1.sign = johokt.engine1)
        LEFT JOIN kig kig2 ON (kig2.catalog = johokt.catalog AND kig2.catalog_code = johokt.catalog_code AND kig2.type = '02' AND kig2.sign = johokt.engine2)
        LEFT JOIN kig kig3 ON (kig3.catalog = johokt.catalog AND kig3.catalog_code = johokt.catalog_code AND kig3.type = '03' AND kig3.sign = johokt.body)
        LEFT JOIN kig kig4 ON (kig4.catalog = johokt.catalog AND kig4.catalog_code = johokt.catalog_code AND kig4.type = '04' AND kig4.sign = johokt.grade)
        LEFT JOIN kig kig5 ON (kig5.catalog = johokt.catalog AND kig5.catalog_code = johokt.catalog_code AND kig5.type = '05' AND kig5.sign = johokt.atm_mtm)
        LEFT JOIN kig kig6 ON (kig6.catalog = johokt.catalog AND kig6.catalog_code = johokt.catalog_code AND kig6.type = '06' AND kig6.sign = johokt.trans)
        LEFT JOIN kig kig7 ON (kig7.catalog = johokt.catalog AND kig7.catalog_code = johokt.catalog_code AND kig7.type = '07' AND kig7.sign = johokt.f1)
        LEFT JOIN kig kig8 ON (kig8.catalog = johokt.catalog AND kig8.catalog_code = johokt.catalog_code AND kig8.type = '08' AND kig8.sign = johokt.f2)
        LEFT JOIN kig kig9 ON (kig9.catalog = johokt.catalog AND kig9.catalog_code = johokt.catalog_code AND kig9.type = '09' AND kig9.sign = johokt.f3)
        LEFT JOIN kig kig10 ON (kig10.catalog = johokt.catalog AND kig10.catalog_code = johokt.catalog_code AND kig10.type = '10' AND kig10.sign = johokt.f4)
        LEFT JOIN kig kig11 ON (kig11.catalog = johokt.catalog AND kig11.catalog_code = johokt.catalog_code AND kig11.type = '11' AND kig11.sign = SUBSTRING_INDEX(johokt.f5, ' ', 1))

        LEFT JOIN tkm tkm1 ON (tkm1.catalog = kig1.catalog AND tkm1.catalog_code = kig1.catalog_code AND tkm1.type = kig1.type)
        LEFT JOIN tkm tkm2 ON (tkm2.catalog = kig2.catalog AND tkm2.catalog_code = kig2.catalog_code AND tkm2.type = kig2.type)
        LEFT JOIN tkm tkm3 ON (tkm3.catalog = kig3.catalog AND tkm3.catalog_code = kig3.catalog_code AND tkm3.type = kig3.type)
        LEFT JOIN tkm tkm4 ON (tkm4.catalog = kig4.catalog AND tkm4.catalog_code = kig4.catalog_code AND tkm4.type = kig4.type)
        LEFT JOIN tkm tkm5 ON (tkm5.catalog = kig5.catalog AND tkm5.catalog_code = kig5.catalog_code AND tkm5.type = kig5.type)
        LEFT JOIN tkm tkm6 ON (tkm6.catalog = kig6.catalog AND tkm6.catalog_code = kig6.catalog_code AND tkm6.type = kig6.type)
        LEFT JOIN tkm tkm7 ON (tkm7.catalog = kig7.catalog AND tkm7.catalog_code = kig7.catalog_code AND tkm7.type = kig7.type)
        LEFT JOIN tkm tkm8 ON (tkm8.catalog = kig8.catalog AND tkm8.catalog_code = kig8.catalog_code AND tkm8.type = kig8.type)
        LEFT JOIN tkm tkm9 ON (tkm9.catalog = kig9.catalog AND tkm9.catalog_code = kig9.catalog_code AND tkm9.type = kig9.type)
        LEFT JOIN tkm tkm10 ON (tkm10.catalog = kig10.catalog AND tkm10.catalog_code = kig10.catalog_code AND tkm10.type = kig10.type)
        LEFT JOIN tkm tkm11 ON (tkm11.catalog = kig11.catalog AND tkm11.catalog_code = kig11.catalog_code AND tkm11.type = kig11.type)

        WHERE johokt.catalog = :regionCode
    AND johokt.catalog_code = :modificationCode
        AND $pole = :engine
        ";

        $query = $this->conn->prepare($sql);

        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('engine', $engine);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();
        $result = array();

        $af = array();

        foreach($aData as $item) {
            for ($i = 1; $i < 12; $i++) {
                if ($item['f' . $i]) {
                    $af[$i][$item['f' . $i]] = '(' . $item['f' . $i] . ') ' . $item['ken' . $i];
                    $result['f'.$i] = $af[$i];
                }
            }
        }


        foreach ($result as $index => $value) {

            $complectations[($index)] = $value;
        }

        return $complectations;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)

    {

        $sql = "
        SELECT johokt.compl_code, johokt.model_code
        FROM johokt
        WHERE johokt.catalog = :regionCode
        AND johokt.catalog_code = :modificationCode
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();

        foreach($aData as $item){


            $complectations[$item['compl_code']] = array(
                Constants::NAME => $item['model_code'],
                Constants::OPTIONS => array());

        }

        return $complectations;

    }

    public function getComplectationCodeFromFormaData($aDataFromForm, $regionCode, $modificationCode)

    {

        $pole = array();
                $pole['engine1'] = '';
                $pole['engine2'] = '';
                $pole['body'] = '';
                $pole['grade'] = '';
                $pole['atm_mtm'] = '';
                $pole['trans'] = '';
                $pole['f1'] = '';
                $pole['f2'] = '';
                $pole['f3'] = '';
                $pole['f4'] = '';
                $pole['f5'] = '';

        foreach ($aDataFromForm as $index => $value)
        {
            switch($index)

            {
                case ('f1'): $pole['engine1'] = $value;break;
                case ('f2'): $pole['engine2'] = $value;break;
                case ('f3'): $pole['body'] = $value;break;
                case ('f4'): $pole['grade'] = $value;break;
                case ('f5'): $pole['atm_mtm'] = $value;break;
                case ('f6'): $pole['trans'] = $value;break;
                case ('f7'): $pole['f1'] = $value;break;
                case ('f8'): $pole['f2'] = $value;break;
                case ('f9'): $pole['f3'] = $value;break;
                case ('f10'): $pole['f4'] = $value;break;
                case ('f11'): $pole['f5'] = $value;break;
            }
        }

            $sql = "
        SELECT johokt.compl_code
        FROM johokt
        WHERE johokt.catalog = :regionCode
        AND johokt.catalog_code = :modificationCode
        AND johokt.engine1 = :engine1
        AND johokt.engine2 = :engine2 OR johokt.engine2 = ''
        AND johokt.body = :body
        AND johokt.grade = :grade
        AND johokt.atm_mtm = :atm_mtm
        AND johokt.trans = :trans
        AND johokt.f1 = :f1
        AND johokt.f2 = :f2
        AND johokt.f3 = :f3
        AND johokt.f4 = :f4
        AND SUBSTRING_INDEX(johokt.f5, ' ', 1) = :f5

        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('modificationCode',  $modificationCode);
            $query->bindValue('engine1',  $pole['engine1']);
            $query->bindValue('engine2',  $pole['engine2']);
            $query->bindValue('body',  $pole['body']);
            $query->bindValue('grade',  $pole['grade']);
            $query->bindValue('atm_mtm',  $pole['atm_mtm']);
            $query->bindValue('trans',  $pole['trans']);
            $query->bindValue('f1',  $pole['f1']);
            $query->bindValue('f2',  $pole['f2']);
            $query->bindValue('f3',  $pole['f3']);
            $query->bindValue('f4',  $pole['f4']);
            $query->bindValue('f5',  $pole['f5']);



            $query->execute();

            $aData = $query->fetch();

            $complectation = $aData['compl_code'];


            return $complectation;

    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {


        $sql = "
        SELECT SUBSTRING(emi.part_group, 1, 1) groupCodes
        FROM emi
        WHERE emi.catalog = :regionCode
        AND emi.catalog_code = :modificationCode
        GROUP BY SUBSTRING(part_group, 1, 1)
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('modificationCode',  $modificationCode);

            $query->execute();
            $aData = $query->fetchAll();



        $aDataGroups = array('ИНСТРУМЕНТЫ', 'ДВИГАТЕЛЬ', 'ТОПЛИВНАЯ СИСТЕМА', 'ТРАНСМИССИЯ, ПОДВЕСКА, ТОРМОЗНАЯ СИСТЕМА', 'КУЗОВНЫЕ ДЕТАЛИ, ЭКСТЕРЬЕР, ИНТЕРЬЕР', 'ЭЛЕКТРИКА, КЛИМАТ-КОНТРОЛЬ');


        $groups = array();


        foreach($aDataGroups as $index => $value){

            $groups[$index+1] = array(
                Constants::NAME     =>$value,
                Constants::OPTIONS => array()
            );
        }

        return $groups;
    }

    public function getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode)
    {
  /*      $sqlNumPrigroup = "
        SELECT *
        FROM pri_groups_full
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND pri_group = :groupCode
        ";
    	$query = $this->conn->prepare($sqlNumPrigroup);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetch();  
       
        $sqlNumModel = "
        SELECT num_model
        FROM part_images
        WHERE catalog = :regionCode
            AND model_code =:model_code
        GROUP BY num_model
        ";
    	$query = $this->conn->prepare($sqlNumModel);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->execute();

        $aNumModel = $query->fetch();

        $groupSchemas = array();
    /*    foreach ($aData as $item)*//* {
            $groupSchemas[$aData['num_image']] = array(Constants::NAME => $aData['num_image'], Constants::OPTIONS => array(
              Constants::CD => $aData['catalog'].$aData['sub_dir'].$aData['sub_wheel'],
                    	'num_model' => $aNumModel['num_model'],
                        'num_image' => $aData['num_image']
                ));
        }*/
		$groupSchemas = array();
        return $groupSchemas;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {

        switch ($groupCode){
            case 1:
                $min = 0;
                $max = 0;
                break;
            case 2:
                $min = 1;
                $max = 1;
                break;
            case 3:
                $min = 2;
                $max = 2;
                break;
            case 4:
                $min = 3;
                $max = 4;
                break;
            case 5:
                $min = 5;
                $max = 7;
                break;
            case 6:
                $min = 8;
                $max = 9;
                break;
        }



        $sql = "
        SELECT bzi.part_group, emi.pic_code, figmei.desc_en
        FROM kpt
        INNER JOIN bzi ON (bzi.catalog = kpt.catalog AND bzi.catalog_code = kpt.catalog_code AND bzi.ipic_code = kpt.ipic_code AND SUBSTRING(bzi.part_group, 1, 1) BETWEEN :min1 AND :max1)
        INNER JOIN emi ON (emi.catalog = bzi.catalog AND emi.catalog_code = bzi.catalog_code AND emi.part_group = bzi.part_group)
        INNER JOIN figmei ON (figmei.catalog = bzi.catalog and figmei.part_group = bzi.part_group)

        WHERE kpt.catalog = :regionCode
        AND kpt.catalog_code = :modificationCode
        AND kpt.compl_code = :complectationCode
        ORDER BY emi.part_group
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('min1',  $min);
        $query->bindValue('max1',  $max);
        $query->execute();

        $aData = $query->fetchAll();




           $subgroups = array();


           foreach($aData as $item)
           {

               $subgroups[$item['part_group']] = array(

               Constants::NAME => $item['desc_en'],
                   Constants::OPTIONS => array(
                       'picture' => $item['pic_code']
                   )

               );

           }

           return $subgroups;
       }

       public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
       {

           $sql = "
           SELECT bzi.illust_no, bzi.pic_code, images.disk, inm.desc_en, bzi.ipic_code
           FROM kpt
           INNER JOIN bzi ON (bzi.catalog = kpt.catalog AND bzi.catalog_code = kpt.catalog_code AND bzi.ipic_code = kpt.ipic_code AND bzi.part_group = :subGroupCode)
           INNER JOIN images ON (images.catalog = bzi.catalog AND images.pic_code = bzi.pic_code)
           INNER JOIN inm ON (inm.catalog = bzi.catalog AND inm.catalog_code = bzi.catalog_code AND inm.pic_desc_code = bzi.pic_desc_code
           AND inm.op1 = bzi.op1 AND inm.op2 = bzi.op2 AND inm.op3 = bzi.op3)
           WHERE kpt.catalog = :regionCode
           AND kpt.catalog_code = :modificationCode
           AND kpt.compl_code = :complectationCode

           ";

           $query = $this->conn->prepare($sql);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('modificationCode',  $modificationCode);
           $query->bindValue('complectationCode',  $complectationCode);
           $query->bindValue('subGroupCode',  $subGroupCode);
           $query->execute();

           $aData = $query->fetchAll();


           $schemas = array();
           foreach($aData as $index => $item)
           {

                       $schemas[$item['pic_code']] = array(
                       Constants::NAME => 'Схема'.($index+1).' из'. count($aData),
                       Constants::OPTIONS => array('figure' => $item['pic_code'],
                           'disk' => $item['disk'],
                           'desc' => $item['desc_en'])
                   );
           }


           return $schemas;
       }

       public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
       {

           $schema = array();


                       $schema[$schemaCode] = array(
                       Constants::NAME => $schemaCode,
                           Constants::OPTIONS => array(
                               Constants::CD => $schemaCode
                           )
                   );



           return $schema;
       }

       public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
       {


            $sqlPnc = "
            SELECT img_nums.number, hinmei.desc_en
            FROM img_nums
            INNER JOIN hinmei ON (hinmei.catalog = img_nums.catalog and hinmei.pnc = img_nums.number)
            where img_nums.catalog = :regionCode
            and img_nums.disk = :disc
            and img_nums.pic_code = :schemaCode
            and img_nums.number_type = '3'
            ";


           $query = $this->conn->prepare($sqlPnc);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('schemaCode',  $schemaCode);
           $query->bindValue('disc',  $options['disk']);

           $query->execute();

           $aPncs = $query->fetchAll();



           foreach ($aPncs as &$aPnc)
           {

               $sqlSchemaLabels = "
           SELECT x1, x2, y1, y2
           FROM img_nums
           where img_nums.catalog = :regionCode
            and img_nums.disk = :disc
            and img_nums.pic_code = :schemaCode
            and img_nums.number_type = '3'
            and img_nums.number = :pnc
           ";

               $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('regionCode',  $regionCode);
               $query->bindValue('schemaCode',  $schemaCode);
               $query->bindValue('disc',  $options['disk']);
               $query->bindValue('pnc',  $aPnc['number']);


               $query->execute();

               $aPnc['clangjap'] = $query->fetchAll();
               unset($aPnc);

           }



           $pncs = array();

           foreach ($aPncs as $index=>$value) {
               {
                   if (!$value['clangjap'])
                   {
                       unset ($aPncs[$index]);
                   }

                   foreach ($value['clangjap'] as $item1)
                   {
                       $pncs[$value['number']][Constants::OPTIONS][Constants::COORDS][$item1['x1']] = array(
                           Constants::X2 => floor((($item1['x2']))),
                           Constants::Y2 => $item1['y1'],
                           Constants::X1 => floor($item1['x1']),
                           Constants::Y1 => $item1['y2']);

                   }



               }
           }


           foreach ($aPncs as $item) {



               $pncs[$item['number']][Constants::NAME] = $item['desc_en'];



           }


            return $pncs;
       }

       public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
       {

           $sqlPnc = "
            SELECT img_nums.number
            FROM img_nums
            where img_nums.catalog = :regionCode
            and img_nums.disk = :disc
            and img_nums.pic_code = :schemaCode
            and img_nums.number_type = '4'
            ";


           $query = $this->conn->prepare($sqlPnc);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('schemaCode',  $schemaCode);
           $query->bindValue('disc',  $options['disk']);

           $query->execute();

           $aPncs = $query->fetchAll();



           foreach ($aPncs as &$aPnc)
           {

               $sqlSchemaLabels = "
            SELECT x1, x2, y1, y2
            FROM img_nums
            where img_nums.catalog = :regionCode
            and img_nums.disk = :disc
            and img_nums.pic_code = :schemaCode
            and img_nums.number_type = '4'
            and img_nums.number = :pnc
           ";

               $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('regionCode',  $regionCode);
               $query->bindValue('schemaCode',  $schemaCode);
               $query->bindValue('disc',  $options['disk']);
               $query->bindValue('pnc',  $aPnc['number']);


               $query->execute();

               $aPnc['clangjap'] = $query->fetchAll();
               unset($aPnc);

           }



           $pncs = array();

           foreach ($aPncs as $index=>$value) {
               {
                   if (!$value['clangjap'])
                   {
                       unset ($aPncs[$index]);
                   }

                   foreach ($value['clangjap'] as $item1)
                   {
                       $pncs[$value['number']][Constants::OPTIONS][Constants::COORDS][$item1['x1']] = array(
                           Constants::X2 => floor((($item1['x2']))),
                           Constants::Y2 => $item1['y2'],
                           Constants::X1 => floor($item1['x1']),
                           Constants::Y1 => $item1['y1']);

                   }



               }
           }


           foreach ($aPncs as $index=>$value) {



               $pncs[$value['number']][Constants::NAME] = $index;



           }

           return $pncs;

    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
    {

        $sqlPnc = "
            SELECT img_nums.number, figmei.desc_en
            FROM img_nums
            INNER JOIN figmei ON (figmei.catalog = img_nums.catalog and figmei.part_group = img_nums.number)
            where img_nums.catalog = :regionCode
            and img_nums.disk = :disc
            and img_nums.pic_code = :schemaCode
            and img_nums.number_type = '1'
            ";


        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('schemaCode',  $schemaCode);
        $query->bindValue('disc',  $options['disk']);

        $query->execute();

        $aPncs = $query->fetchAll();



        foreach ($aPncs as &$aPnc)
        {

            $sqlSchemaLabels = "
           SELECT x1, x2, y1, y2
           FROM img_nums
           where img_nums.catalog = :regionCode
            and img_nums.disk = :disc
            and img_nums.pic_code = :schemaCode
            and img_nums.number_type = '1'
            and img_nums.number = :pnc
           ";

            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('schemaCode',  $schemaCode);
            $query->bindValue('disc',  $options['disk']);
            $query->bindValue('pnc',  $aPnc['number']);


            $query->execute();

            $aPnc['clangjap'] = $query->fetchAll();
            unset($aPnc);

        }



        $pncs = array();

        foreach ($aPncs as $index=>$value) {
            {
                if (!$value['clangjap'])
                {
                    unset ($aPncs[$index]);
                }

                foreach ($value['clangjap'] as $item1)
                {
                    $pncs[$value['number']][Constants::OPTIONS][Constants::COORDS][$item1['x1']] = array(
                        Constants::X2 => $item1['x2'],
                        Constants::Y2 => $item1['y2'],
                        Constants::X1 => $item1['x1'],
                        Constants::Y1 => $item1['y1']
                    );

                }



            }
        }


        foreach ($aPncs as $index=>$value) {



            $pncs[$value['number']][Constants::NAME] = $value['desc_en'];



        }


        return $pncs;
    }



    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode, $options)
    {


        $sqlArticuls = "
            SELECT hnb_desc.quantity, hnb_desc.start_date, hnb_desc.end_date, hnb_desc.add_desc, hnb_desc.part_code, hnb_desc.hnb_id
            FROM kpt
            INNER JOIN hnb hnb_code ON (hnb_code.catalog = kpt.catalog and hnb_code.catalog_code = kpt.catalog_code AND hnb_code.pnc = :pnc AND hnb_code.add_desc LIKE CONCAT('%', kpt.ipic_code, '%'))
            INNER JOIN hnb hnb_desc ON (hnb_desc.catalog = kpt.catalog and hnb_desc.catalog_code = kpt.catalog_code AND hnb_desc.hnb_id = hnb_code.hnb_id +1)
            WHERE kpt.catalog = :regionCode
            AND kpt.catalog_code = :modificationCode
            AND kpt.compl_code = :complectationCode
            ";


        $query = $this->conn->prepare($sqlArticuls);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('pnc', $pncCode);

        $query->execute();

         $aArticuls = $query->fetchAll();




$articuls = array();

        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['hnb_id']] = array(
                Constants::NAME => $item['part_code'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['quantity'],
                    Constants::START_DATE => $item['start_date'],
                    Constants::END_DATE => $item['end_date'],
                    'DESC' => $item['add_desc']
                )
            );
            
        }


        return $articuls;
    }

    public function getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {


        switch (substr($subGroupCode, 0, 1)){
            case 0:
                $groupCode = 1;
                break;
            case 1:
                $groupCode = 2;
                break;
            case 2:
                $groupCode = 3;
                break;
            case 3:
                $groupCode = 4;
                break;
            case 4:
                $groupCode = 4;
                break;
            case 5:
                $groupCode = 5;
                break;
            case 6:
                $groupCode = 5;
                break;
            case 7:
                $groupCode = 5;
                break;
            case 8:
                $groupCode = 6;
                break;
            case 9:
                $groupCode = 6;
                break;
        }

        return $groupCode;

    }

    public function getComplForSchemas($catalog, $mdldir, $nno, $data1)

    {
        $sql = "
        SELECT VARIATION1, VARIATION2, VARIATION3, VARIATION4, VARIATION5, VARIATION6, VARIATION7, VARIATION8
        FROM posname
        WHERE CATALOG = :CATALOG
          AND MDLDIR = :MDLDIR
             AND NNO = :NNO
              AND DATA1 = :DATA1
             ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('CATALOG', $catalog);
        $query->bindValue('MDLDIR', $mdldir);
        $query->bindValue('NNO', $nno);
        $query->bindValue('DATA1', $data1);

        $query->execute();

        $complectation = $query->fetchAll();

        return $complectation;
    }

    public function multiexplode ($delimiters,$string) {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }




    
} 