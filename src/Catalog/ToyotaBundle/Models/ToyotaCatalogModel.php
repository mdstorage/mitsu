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
        GROUP BY catalog

        ";

        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item)
        {
            $regions[$item['catalog']] = array(Constants::NAME=>$item['catalog'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {


            $sql = "
        SELECT model_name
        FROM shamei
        WHERE shamei.catalog = :regionCode
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

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {
        $sql = "
        SELECT johokt.engine1 as f1, johokt.engine2 as f2, johokt.body as f3, johokt.grade as f4, johokt.atm_mtm as f5, johokt.trans as f6, johokt.f1 as f7, johokt.f2 as f8, johokt.f3 as f9, compl_code, model_code, prod_start, prod_end,
        kig1.desc_en ken1,
        kig2.desc_en ken2,
        kig3.desc_en ken3,
        kig4.desc_en ken4,
        kig5.desc_en ken5,
        kig6.desc_en ken6,
        kig7.desc_en ken7,
        kig8.desc_en ken8,

        tkm1.desc_en ten1,
        tkm2.desc_en ten2,
        tkm3.desc_en ten3,
        tkm4.desc_en ten4,
        tkm5.desc_en ten5,
        tkm6.desc_en ten6,
        tkm7.desc_en ten7,
        tkm8.desc_en ten8


        FROM johokt
        LEFT JOIN kig kig1 ON (kig1.catalog = johokt.catalog AND kig1.catalog_code = johokt.catalog_code AND kig1.type = '01' AND kig1.sign = johokt.engine1)
        LEFT JOIN kig kig2 ON (kig2.catalog = johokt.catalog AND kig2.catalog_code = johokt.catalog_code AND kig2.type = '02' AND kig2.sign = johokt.engine2)
        LEFT JOIN kig kig3 ON (kig3.catalog = johokt.catalog AND kig3.catalog_code = johokt.catalog_code AND kig3.type = '03' AND kig3.sign = johokt.body)
        LEFT JOIN kig kig4 ON (kig4.catalog = johokt.catalog AND kig4.catalog_code = johokt.catalog_code AND kig4.type = '04' AND kig4.sign = johokt.grade)
        LEFT JOIN kig kig5 ON (kig5.catalog = johokt.catalog AND kig5.catalog_code = johokt.catalog_code AND kig5.type = '05' AND kig5.sign = johokt.atm_mtm)
        LEFT JOIN kig kig6 ON (kig6.catalog = johokt.catalog AND kig6.catalog_code = johokt.catalog_code AND kig6.type = '06' AND kig6.sign = johokt.trans)
        LEFT JOIN kig kig7 ON (kig7.catalog = johokt.catalog AND kig7.catalog_code = johokt.catalog_code AND kig7.type = '07' AND kig7.sign = johokt.f1)
        LEFT JOIN kig kig8 ON (kig8.catalog = johokt.catalog AND kig8.catalog_code = johokt.catalog_code AND kig8.type = '08' AND kig8.sign = johokt.f2)

        LEFT JOIN tkm tkm1 ON (tkm1.catalog = kig1.catalog AND tkm1.catalog_code = kig1.catalog_code AND tkm1.type = kig1.type)
        LEFT JOIN tkm tkm2 ON (tkm2.catalog = kig2.catalog AND tkm2.catalog_code = kig2.catalog_code AND tkm2.type = kig2.type)
        LEFT JOIN tkm tkm3 ON (tkm3.catalog = kig3.catalog AND tkm3.catalog_code = kig3.catalog_code AND tkm3.type = kig3.type)
        LEFT JOIN tkm tkm4 ON (tkm4.catalog = kig4.catalog AND tkm4.catalog_code = kig4.catalog_code AND tkm4.type = kig4.type)
        LEFT JOIN tkm tkm5 ON (tkm5.catalog = kig5.catalog AND tkm5.catalog_code = kig5.catalog_code AND tkm5.type = kig5.type)
        LEFT JOIN tkm tkm6 ON (tkm6.catalog = kig6.catalog AND tkm6.catalog_code = kig6.catalog_code AND tkm6.type = kig6.type)
        LEFT JOIN tkm tkm7 ON (tkm7.catalog = kig7.catalog AND tkm7.catalog_code = kig7.catalog_code AND tkm7.type = kig7.type)
        LEFT JOIN tkm tkm8 ON (tkm8.catalog = kig8.catalog AND tkm8.catalog_code = kig8.catalog_code AND tkm8.type = kig8.type)

        WHERE johokt.catalog = :regionCode
        AND johokt.catalog_code = :modificationCode
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();




 /*       $aDataCompl = array();
        $arr = array('compl_code', 'model_code', 'prod_start', 'prod_end');



        foreach($aData as &$item)
        {

            $aDataChisl = array();

            foreach($item as $index1=>$value1)
            {

                {
                    $aDataChisl[] = $value1;
                }


            foreach($aDataChisl as $index=>$value)
            {
                $sql = "
                SELECT tkm.desc_en ten, kig.desc_en ken
                FROM kig
                LEFT JOIN tkm ON (tkm.catalog = kig.catalog AND tkm.catalog_code = kig.catalog_code AND tkm.type = :index)
                WHERE kig.type = :index
                AND kig.sign = :value
                AND kig.catalog = :regionCode
                AND kig.catalog_code = :modificationCode
                        ";
                $query = $this->conn->prepare($sql);
                $query->bindValue('value',  $value);
                $query->bindValue('index',  str_pad($index+1, 2, "0", STR_PAD_LEFT));
                $query->bindValue('regionCode',  $regionCode);
                $query->bindValue('modificationCode',  $modificationCode);
                $query->execute();

                $aData1 = $query->fetch();




                if (!in_array($index1, $arr))

                $item[$index1] = $aData1['ten'].': ('.$value.') '.$aData1['ken'];

            }
            }


            unset ($item);
        }*/


        $complectations = array();
        $trans = array();




        foreach($aData as $item){


                $complectations[$item['compl_code']] = array(
                    Constants::NAME => $item['model_code'],
                    Constants::OPTIONS => array(
                        'FROMDATE' => $item['prod_start'],
                        'UPTODATE' => $item['prod_end'],
                        'OPTION1' => $item['f1']?$item['ten1'].': ('.$item['f1'].') '.$item['ken1']:'',
                        'OPTION2' => $item['f2']?$item['ten2'].': ('.$item['f2'].') '.$item['ken2']:'',
                        'OPTION3' => $item['f3']?$item['ten3'].': ('.$item['f3'].') '.$item['ken3']:'',
                        'OPTION4' => $item['f4']?$item['ten4'].': ('.$item['f4'].') '.$item['ken4']:'',
                        'OPTION5' => $item['f5']?$item['ten5'].': ('.$item['f5'].') '.$item['ken5']:'',
                        'OPTION6' => $item['f6']?$item['ten6'].': ('.$item['f6'].') '.$item['ken6']:'',
                        'OPTION7' => $item['f7']?$item['ten7'].': ('.$item['f7'].') '.$item['ken7']:'',
                        'OPTION8' => $item['f8']?$item['ten8'].': ('.$item['f8'].') '.$item['ken8']:'',
                        'trans' => ''
                                            ));


        }

         return $complectations;
     
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
        SELECT emi.part_group, emi.pic_code, figmei.desc_en
        FROM emi
        INNER JOIN figmei ON (figmei.catalog = emi.catalog and figmei.part_group = emi.part_group)
        WHERE emi.catalog = :regionCode
        AND emi.catalog_code = :modificationCode
        AND SUBSTRING(emi.part_group, 1, 1) BETWEEN :min1 AND :max1
        ORDER BY emi.part_group
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
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

           var_dump($aData); die;



           $schemas = array();
           foreach($aData as $item)
           {

                       $schemas[strtoupper($item['PIMGSTR'])] = array(
                       Constants::NAME => $item['REC2'].' '.$item['REC3'],
                       Constants::OPTIONS => array('figure' => trim($item['FIGURE'], ('*()')),
                           'FROMDATE' => $item['FROM_DATE'],
                           'UPTODATE' => $item['UPTO_DATE'],
                           'SECNO' => $item['SECNO'])
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

           $FIGURE_WITH_PREFIX = $options['figure'];
           $SECNO = $options['SECNO'];

           $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");


         /*  $sqlPnc = "
           SELECT PARTNAME_E, patcode.PARTCODE
            FROM patcode,  pcodenes
            where patcode.CATALOG = :regionCode
            and patcode.MDLDIR = :MDLDIR
            and patcode.FIGURE LIKE :FIGURE_WITHOUT_PREFIX
            and patcode.CATALOG = pcodenes.CATALOG
            and patcode.MDLDIR = pcodenes.MDLDIR
            and pcodenes.FIGURE = :FIGURE_WITH_PREFIX
            and patcode.PARTCODE = pcodenes.PARTCODE
            and pcodenes.SECNO = :SECNO
            group by patcode.PARTCODE

           ";*/
           $sqlPnc = "
           SELECT PARTNAME_E, patcode.PARTCODE
            FROM patcode,  pcodenes
            where patcode.CATALOG = :regionCode
            and patcode.MDLDIR = :MDLDIR
            and patcode.FIGURE LIKE :FIGURE_WITHOUT_PREFIX
            and patcode.CATALOG = pcodenes.CATALOG
            and patcode.MDLDIR = pcodenes.MDLDIR
            and pcodenes.FIGURE LIKE :FIGURE_WITH_PREFIX
            and patcode.PARTCODE = pcodenes.PARTCODE
            and pcodenes.SECNO = :SECNO

           ";


           $query = $this->conn->prepare($sqlPnc);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('MDLDIR',  $MDLDIR);
           $query->bindValue('FIGURE_WITH_PREFIX',  '%'.substr($FIGURE_WITH_PREFIX,0,strlen($FIGURE_WITH_PREFIX)).'%');
           $query->bindValue('FIGURE_WITHOUT_PREFIX',  '%'.substr($FIGURE_WITH_PREFIX,0,3).'%');
           $query->bindValue('SECNO',  $SECNO);

           $query->execute();

           $aPncs = $query->fetchAll();




           foreach ($aPncs as &$aPnc)
           {

               $sqlSchemaLabels = "
           SELECT `pcodenes`.LABEL_X, `pcodenes`.LABEL_Y
           FROM `pcodenes`
           WHERE `CATALOG`= :regionCode
            and `MDLDIR` = :MDLDIR and `FIGURE` LIKE :FIGURE_WITH_PREFIX
            and `pcodenes`.`PARTCODE` = :pnc
            and `pcodenes`.`SECNO` LIKE :SECNO
           ";

               $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('regionCode',  $regionCode);
               $query->bindValue('MDLDIR',  $MDLDIR);
               $query->bindValue('FIGURE_WITH_PREFIX',  '%'.substr($FIGURE_WITH_PREFIX,0,strlen($FIGURE_WITH_PREFIX)).'%');
               $query->bindValue('SECNO',  $SECNO);
               $query->bindValue('pnc',  $aPnc['PARTCODE']);


               $query->execute();

               $aPnc['clangjap'] = $query->fetchAll();
               unset($aPnc);

           }
           $kp = 1;

           if ($regionCode != 'JP')
           {
               $kp = 2;
           }

           else
           {
               $kp = 2.5;
           }

           $pncs = array();
           $str = array();
           foreach ($aPncs as $index=>$value) {
               {
                   if (!$value['clangjap'])
                   {
                       unset ($aPncs[$index]);
                   }

                   foreach ($value['clangjap'] as $item1)
                   {
                       $pncs[$value['PARTCODE']][Constants::OPTIONS][Constants::COORDS][$item1['LABEL_X']] = array(
                           Constants::X2 => floor((($item1['LABEL_X']))/$kp),
                           Constants::Y2 => ($item1['LABEL_Y']-5)/$kp,
                           Constants::X1 => floor($item1['LABEL_X']/$kp)+80,
                           Constants::Y1 => $item1['LABEL_Y']/$kp + 15);

                   }



               }
           }


           foreach ($aPncs as $item) {



               $pncs[$item['PARTCODE']][Constants::NAME] = $item['PARTNAME_E'];



           }


            return $pncs;
       }

       public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
       {

           $FIGURE_WITH_PREFIX = $options['figure'];
           $SECNO = $options['SECNO'];

           $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");


           $sqlPnc = "
           SELECT `patcode`.`PARTCODE`  FROM `patcode`,  `pcodenes` where `patcode`.`CATALOG`= :regionCode
            and `patcode`.`MDLDIR` = :MDLDIR and `patcode`.`FIGURE` like :FIGURE_WITHOUT_PREFIX
            and `patcode`.`CATALOG` = `pcodenes`.`CATALOG` and `patcode`.`MDLDIR` = `pcodenes`.`MDLDIR`
            and `pcodenes`.`FIGURE` = :FIGURE_WITH_PREFIX and `patcode`.`PARTCODE` = `pcodenes`.`PARTCODE`
            and `pcodenes`.`SECNO` LIKE :SECNO group by `patcode`.`PARTCODE`

           ";

           $query = $this->conn->prepare($sqlPnc);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('MDLDIR',  $MDLDIR);
           $query->bindValue('FIGURE_WITH_PREFIX',  $FIGURE_WITH_PREFIX);
           $query->bindValue('FIGURE_WITHOUT_PREFIX',  '%'.substr($FIGURE_WITH_PREFIX,0,3).'%');

           $query->bindValue('SECNO',  $SECNO);

           $query->execute();

           $aPncs = $query->fetchAll();
           $aPncsCompare = array();

           foreach ($aPncs as $item)
           {
               $aPncsCompare[] = $item['PARTCODE'];
           }


           $sqlPnc2= "
          SELECT `pcodenes`.`PARTCODE` FROM `pcodenes`
          left outer join `patcode` on
          (`patcode`.`PARTCODE` = `pcodenes`.`PARTCODE` and `patcode`.`CATALOG` = `pcodenes`.`CATALOG` and `patcode`.`MDLDIR` = `pcodenes`.`MDLDIR` and `patcode`.`FIGURE` like :FIGURE_WITHOUT_PREFIX)
          where `pcodenes`.`CATALOG`= :regionCode and `pcodenes`.`MDLDIR` = :MDLDIR  and `pcodenes`.`FIGURE` = :FIGURE_WITH_PREFIX and `pcodenes`.`SECNO` LIKE :SECNO group by `pcodenes`.`PARTCODE`

           ";

           $query = $this->conn->prepare($sqlPnc2);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('MDLDIR',  $MDLDIR);
           $query->bindValue('FIGURE_WITH_PREFIX',  $FIGURE_WITH_PREFIX);
           $query->bindValue('FIGURE_WITHOUT_PREFIX',  '%'.substr($FIGURE_WITH_PREFIX,0,3).'%');

           $query->bindValue('SECNO',  $SECNO);

           $query->execute();

           $aPncs2 = $query->fetchAll();
           $aPncs2Compare = array();

           foreach ($aPncs2 as $item)
           {
               $aPncs2Compare[] = $item['PARTCODE'];
           }


           $arrayArticuls = array();
           $arrayArticuls1 = array();
           $arrayArticuls = array_diff($aPncs2Compare, $aPncsCompare);




           foreach ($arrayArticuls as &$aPnc)
           {



               $sqlSchemaLabels = "
           SELECT `pcodenes`.LABEL_X, `pcodenes`.LABEL_Y
           FROM `pcodenes`
           WHERE `CATALOG`= :regionCode
            and `MDLDIR` = :MDLDIR and `FIGURE` = :FIGURE_WITH_PREFIX
            and `pcodenes`.`PARTCODE` = :pnc
            and `pcodenes`.`SECNO` LIKE :SECNO
           ";

               $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('regionCode',  $regionCode);
               $query->bindValue('MDLDIR',  $MDLDIR);
               $query->bindValue('FIGURE_WITH_PREFIX',  $FIGURE_WITH_PREFIX);
               $query->bindValue('SECNO',  $SECNO);
               $query->bindValue('pnc',  $aPnc);


               $query->execute();

               $arrayArticuls1[$aPnc]['clangjap'] = $query->fetchAll();


           }

           $kp = 1;

           if ($regionCode != 'JP')
           {
               $kp = 2;
           }

           else
           {
               $kp = 2.5;
           }


           $pncs = array();
           $str = array();

           foreach ($arrayArticuls1 as $index=>$value) {


               if (!$value['clangjap'] || strlen($index) == 3) {
                   unset ($arrayArticuls1[$index]);
               }
           }

           foreach ($arrayArticuls1 as $index=>$value) {


                   if (!$value['clangjap'])
                   {
                       unset ($arrayArticuls1[$index]);
                   }

                   foreach ($value['clangjap'] as $item1)
                   {
                       $pncs[$index][Constants::OPTIONS][Constants::COORDS][$item1['LABEL_X']] = array(
                           Constants::X2 => floor(($item1['LABEL_X'])/$kp),
                           Constants::Y2 => ($item1['LABEL_Y']-5)/$kp,
                           Constants::X1 => floor($item1['LABEL_X']/$kp)+120,
                           Constants::Y1 => $item1['LABEL_Y']/$kp + 15);

                   }




           }



           foreach ($arrayArticuls1 as $index=>$value) {



               $pncs[$index][Constants::NAME] = $index;



           }

           return $pncs;

    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
    {

        $FIGURE_WITH_PREFIX = $options['figure'];
        $SECNO = $options['SECNO'];

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");


        $sqlPnc = "
           SELECT `patcode`.`PARTCODE`  FROM `patcode`,  `pcodenes` where `patcode`.`CATALOG`= :regionCode
            and `patcode`.`MDLDIR` = :MDLDIR and `patcode`.`FIGURE` like :FIGURE_WITHOUT_PREFIX
            and `patcode`.`CATALOG` = `pcodenes`.`CATALOG` and `patcode`.`MDLDIR` = `pcodenes`.`MDLDIR`
            and `pcodenes`.`FIGURE` = :FIGURE_WITH_PREFIX and `patcode`.`PARTCODE` = `pcodenes`.`PARTCODE`
            and `pcodenes`.`SECNO` LIKE :SECNO group by `patcode`.`PARTCODE`

           ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('MDLDIR',  $MDLDIR);
        $query->bindValue('FIGURE_WITH_PREFIX',  $FIGURE_WITH_PREFIX);
        $query->bindValue('FIGURE_WITHOUT_PREFIX',  '%'.substr($FIGURE_WITH_PREFIX,0,3).'%');

        $query->bindValue('SECNO',  $SECNO);

        $query->execute();

        $aPncs = $query->fetchAll();
        $aPncsCompare = array();

        foreach ($aPncs as $item)
        {
            $aPncsCompare[] = $item['PARTCODE'];
        }


        $sqlPnc2= "
          SELECT `pcodenes`.`PARTCODE` FROM `pcodenes`
          left outer join `patcode` on
          (`patcode`.`PARTCODE` = `pcodenes`.`PARTCODE` and `patcode`.`CATALOG` = `pcodenes`.`CATALOG` and `patcode`.`MDLDIR` = `pcodenes`.`MDLDIR` and `patcode`.`FIGURE` like :FIGURE_WITHOUT_PREFIX)
          where `pcodenes`.`CATALOG`= :regionCode and `pcodenes`.`MDLDIR` = :MDLDIR  and `pcodenes`.`FIGURE` = :FIGURE_WITH_PREFIX and `pcodenes`.`SECNO` LIKE :SECNO group by `pcodenes`.`PARTCODE`

           ";

        $query = $this->conn->prepare($sqlPnc2);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('MDLDIR',  $MDLDIR);
        $query->bindValue('FIGURE_WITH_PREFIX',  $FIGURE_WITH_PREFIX);
        $query->bindValue('FIGURE_WITHOUT_PREFIX',  '%'.substr($FIGURE_WITH_PREFIX,0,3).'%');

        $query->bindValue('SECNO',  $SECNO);

        $query->execute();

        $aPncs2 = $query->fetchAll();
        $aPncs2Compare = array();

        foreach ($aPncs2 as $item)
        {
            $aPncs2Compare[] = $item['PARTCODE'];
        }


        $arrayArticuls = array();
        $arrayArticuls1 = array();
        $arrayArticuls = array_diff($aPncs2Compare, $aPncsCompare);




        foreach ($arrayArticuls as &$aPnc)
        {



            $sqlSchemaLabels = "
           SELECT `pcodenes`.LABEL_X, `pcodenes`.LABEL_Y
           FROM `pcodenes`
           WHERE `CATALOG`= :regionCode
            and `MDLDIR` = :MDLDIR and `FIGURE` = :FIGURE_WITH_PREFIX
            and `pcodenes`.`PARTCODE` = :pnc
            and `pcodenes`.`SECNO` LIKE :SECNO
           ";

            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('MDLDIR',  $MDLDIR);
            $query->bindValue('FIGURE_WITH_PREFIX',  $FIGURE_WITH_PREFIX);
            $query->bindValue('SECNO',  $SECNO);
            $query->bindValue('pnc',  $aPnc);


            $query->execute();

            $arrayArticuls1[$aPnc]['clangjap'] = $query->fetchAll();


        }

        $kp = 1;

        if ($regionCode != 'JP')
        {
            $kp = 2;
        }

        else
        {
            $kp = 2.5;
        }




        $pncs = array();
        $str = array();
        foreach ($arrayArticuls1 as $index=>$value) {


            if (!$value['clangjap'] || strlen($index) > 3) {
                unset ($arrayArticuls1[$index]);
            }
        }

        foreach ($arrayArticuls1 as $index=>$value) {

            foreach ($value['clangjap'] as $item1)
            {
                $pncs[$index][Constants::OPTIONS][Constants::COORDS][$item1['LABEL_X']] = array(
                    Constants::X2 => floor(($item1['LABEL_X'])/$kp),
                    Constants::Y2 => ($item1['LABEL_Y']-5)/$kp,
                    Constants::X1 => floor($item1['LABEL_X']/$kp)+40,
                    Constants::Y1 => $item1['LABEL_Y']/$kp + 15);

            }




        }



        foreach ($arrayArticuls1 as $index=>$value) {




            $pncs[$index][Constants::NAME] = $index;



        }


        return $pncs;
    }



    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode, $options)
    {

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");
        $FIGURE_PREFIX = substr($options['figure'],-1);


        $datas = array();
        $datas = explode('_', $complectationCode);

        $complectation = $this->getComplForSchemas($regionCode, ltrim($datas[0],'0'), $datas[1], $datas[2]);


        $sqlPnc = "
         SELECT OEMCODE, FROM_DATE, UPTO_DATE, PER_COUNT, REC3, REP_OEM, REC1, abbrev.DESCRSTR
          FROM catalog
          LEFT JOIN abbrev ON (abbrev.CATALOG = catalog.CATALOG and abbrev.MDLDIR = catalog.MDLDIR AND (abbrev.ABBRSTR = CONCAT('C', catalog.REC1) OR abbrev.ABBRSTR = catalog.REC1))
          WHERE catalog.CATALOG = :regionCode
          AND catalog.MDLDIR = :MDLDIR
          AND catalog.PARTCODE = :pncCode
         ";

         $query = $this->conn->prepare($sqlPnc);
         $query->bindValue('regionCode',  $regionCode);
         $query->bindValue('MDLDIR',  $MDLDIR);
         $query->bindValue('pncCode',  $pncCode);


        $query->execute();

         $aArticuls = $query->fetchAll();





        $plus = array();


       if ($regionCode != 'JP') {


           foreach ($aArticuls as $index => $value) {
               $ct = 0;
               $schemaOptions = $this->multiexplode(array('+', ' +', '+ '), $value['REC3']);


               foreach ($schemaOptions as $item) {

                   $item = trim($item, ('*()'));
                   if (strpos($item, ".")) {
                       $plus = explode('.', $item);


                       if (count($plus) == count(array_intersect($plus, $complectation[0]))) {
                           $ct = $ct + 1;
                       }


                   } else {

                       if (in_array($item, $complectation[0])) {
                           $ct = $ct + 1;
                       }

                   }


               }


               if ($ct === 0) {
                   unset ($aArticuls[$index]);
               }

           }
       }


$articuls = array();

        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['OEMCODE']] = array(
                Constants::NAME => $item['OEMCODE'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['PER_COUNT'],
                    Constants::START_DATE => $item['FROM_DATE'],
                    Constants::END_DATE => $item['UPTO_DATE'],
                    'DESCR' => $item['REC3'],
                    'REPLACE' => $item['REP_OEM'],
                    'COLOR' => ($item['REC1'])?'('.$item['REC1'].') '.$item['DESCRSTR']:'',
                    'ColorCode' => $item['REC1']


                )
            );
            
        }


        return $articuls;
    }

    public function getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");



        if ($regionCode != 'JP')
        {
            $sql = "
        SELECT PICGROUP
        FROM gsecloc_all
        WHERE FIGURE = :subgroupCode
        AND MDLDIR = :MDLDIR
        AND CATALOG = :regionCode
        ";
        }

        else
        {
            $sql = "
        SELECT PICGROUP
        FROM esecloc_jp
        WHERE FIGURE = :subgroupCode
        AND MDLDIR = :MDLDIR
        AND CATALOG = :regionCode
        ";
        }




        $query = $this->conn->prepare($sql);
        $query->bindValue('subgroupCode',  $subGroupCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('MDLDIR',  $MDLDIR);
        $query->execute();

        $groupCode = $query->fetchColumn(0);

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