<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\NissanBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\NissanBundle\Components\NissanConstants;

class NissanCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT CATALOG
        FROM cdindex
        WHERE CATALOG LIKE 'EL'
        OR CATALOG LIKE 'ER'
        OR CATALOG LIKE 'JP'
        OR CATALOG LIKE 'US'

        UNION
        SELECT CATALOG
        FROM cdindex
        WHERE CATALOG NOT LIKE 'EL'
        OR CATALOG NOT LIKE 'ER'
        OR CATALOG NOT LIKE 'JP'
        OR CATALOG NOT LIKE 'US'

        ";

        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item)
        {

            if (substr($item['CATALOG'], -3) != 'INF')
            $regions[$item['CATALOG']] = array(Constants::NAME=>$item['CATALOG'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        if ($regionCode !== 'JP')
        {
            $sql = "
        SELECT cdindex.SHASHU, cdindex.SHASHUKO
        FROM cdindex
        WHERE cdindex.CATALOG = :regionCode
        ORDER by SHASHUKO
        ";
        }
        else
        {
            $sql = "
        SELECT cdindex.SHASHU, cdindex_jp_en.SHASHUKO
        FROM cdindex
        INNER JOIN cdindex_jp_en ON (cdindex.SHASHU = cdindex_jp_en.SHASHU)
        WHERE cdindex.CATALOG = :regionCode
        ORDER by SHASHUKO

        ";
        }




        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[urlencode($item['SHASHUKO'])] = array(Constants::NAME=>strtoupper($item['SHASHUKO']),
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        if ($regionCode !== 'JP') {

            $sql = "
        SELECT cdindex.SHASHU, cdindex.FROM_DATE, cdindex.UPTO_DATE
        FROM cdindex
        WHERE cdindex.CATALOG = :regionCode
        AND SHASHUKO = :modelCode
        ";
        }
        else
        {
            $sql = "
        SELECT cdindex.SHASHU, cdindex.FROM_DATE, cdindex.UPTO_DATE
        FROM cdindex
        INNER JOIN cdindex_jp_en ON (cdindex.SHASHU = cdindex_jp_en.SHASHU and cdindex_jp_en.SHASHUKO = :modelCode)
        WHERE cdindex.CATALOG = :regionCode
        ";
        }

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['SHASHU']] = array(
                Constants::NAME     => $item['SHASHU'],
                Constants::OPTIONS  => array(
                    Constants::START_DATE => $item['FROM_DATE'],
                    Constants::END_DATE => $item['UPTO_DATE'],

                ));

        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {
        $sql = "
        SELECT DATA21, DATA22, DATA23, DATA24, DATA25, DATA26, DATA27, DATA28, destcnt.ShashuCD,
         VARIATION1, VARIATION2, VARIATION3, VARIATION4, VARIATION5, VARIATION6, VARIATION7, VARIATION8, NNO, posname.FROM_DATE, posname.UPTO_DATE, posname.DATA1, abbrev1.DESCRIPTION ABBREV1, abbrev2.DESCRIPTION ABBREV2, abbrev3.DESCRIPTION ABBREV3,
         abbrev4.DESCRIPTION ABBREV4, abbrev5.DESCRIPTION ABBREV5, abbrev6.DESCRIPTION ABBREV6, abbrev7.DESCRIPTION ABBREV7, abbrev8.DESCRIPTION ABBREV8, posname.MDLDIR,
          descEn1.group DECSEN1, descEn2.group DECSEN2, descEn3.group DECSEN3, descEn4.group DECSEN4, descEn5.group DECSEN5, descEn6.group DECSEN6, descEn7.group DECSEN7, descEn8.group DECSEN8
        FROM cdindex
        LEFT JOIN destcnt ON (destcnt.CATALOG = cdindex.CATALOG AND destcnt.SHASHU = cdindex.SHASHU)
        LEFT JOIN posname ON (posname.CATALOG = destcnt.CATALOG AND posname.MDLDIR = destcnt.ShashuCD)
        LEFT JOIN appname abbrev1 ON (abbrev1.CATALOG = posname.CATALOG and abbrev1.MDLDIR = posname.MDLDIR AND abbrev1.MDLDIR = posname.MDLDIR AND abbrev1.VARIATION = posname.VARIATION1)
        LEFT JOIN appname abbrev2 ON (abbrev2.CATALOG = posname.CATALOG and abbrev2.MDLDIR = posname.MDLDIR AND abbrev2.MDLDIR = posname.MDLDIR AND abbrev2.VARIATION = posname.VARIATION2)
        LEFT JOIN appname abbrev3 ON (abbrev3.CATALOG = posname.CATALOG and abbrev3.MDLDIR = posname.MDLDIR AND abbrev3.MDLDIR = posname.MDLDIR AND abbrev3.VARIATION = posname.VARIATION3)
        LEFT JOIN appname abbrev4 ON (abbrev4.CATALOG = posname.CATALOG and abbrev4.MDLDIR = posname.MDLDIR AND abbrev4.MDLDIR = posname.MDLDIR AND abbrev4.VARIATION = posname.VARIATION4)
        LEFT JOIN appname abbrev5 ON (abbrev5.CATALOG = posname.CATALOG and abbrev5.MDLDIR = posname.MDLDIR AND abbrev5.MDLDIR = posname.MDLDIR AND abbrev5.VARIATION = posname.VARIATION5)
        LEFT JOIN appname abbrev6 ON (abbrev6.CATALOG = posname.CATALOG and abbrev6.MDLDIR = posname.MDLDIR AND abbrev6.MDLDIR = posname.MDLDIR AND abbrev6.VARIATION = posname.VARIATION6)
        LEFT JOIN appname abbrev7 ON (abbrev7.CATALOG = posname.CATALOG and abbrev7.MDLDIR = posname.MDLDIR AND abbrev7.MDLDIR = posname.MDLDIR AND abbrev7.VARIATION = posname.VARIATION7)
        LEFT JOIN appname abbrev8 ON (abbrev8.CATALOG = posname.CATALOG and abbrev8.MDLDIR = posname.MDLDIR AND abbrev8.MDLDIR = posname.MDLDIR AND abbrev8.VARIATION = posname.VARIATION8)


        LEFT JOIN data_variation_jp_en descEn1 on (descEn1.data_jp = DATA21)
        LEFT JOIN data_variation_jp_en descEn2 on (descEn2.data_jp = DATA22)
        LEFT JOIN data_variation_jp_en descEn3 on (descEn3.data_jp = DATA23)
        LEFT JOIN data_variation_jp_en descEn4 on (descEn4.data_jp = DATA24)
        LEFT JOIN data_variation_jp_en descEn5 on (descEn5.data_jp = DATA25)
        LEFT JOIN data_variation_jp_en descEn6 on (descEn6.data_jp = DATA26)
        LEFT JOIN data_variation_jp_en descEn7 on (descEn7.data_jp = DATA27)
        LEFT JOIN data_variation_jp_en descEn8 on (descEn8.data_jp = DATA28)

        WHERE cdindex.CATALOG = :regionCode
        AND cdindex.SHASHU = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();


        $complectations = array();
        $trans = array();


        foreach($aData as $item)
        {
            for ($i = 1;$i<9;$i++)
            {
                if (($item['DATA2'.$i]=='TRANS') || ($item['DECSEN'.$i]=='TRANS'))
                {
                    $trans[] = $item['VARIATION'.$i];
                }
            }


        }

        foreach($aData as $item){

            if ($regionCode != 'JP') {
                $complectations[str_pad($item['MDLDIR'], 3, "0", STR_PAD_LEFT) . '_' . $item['NNO']. '_' .$item['DATA1']] = array(
                    Constants::NAME => $item['NNO'],
                    Constants::OPTIONS => array(
                        'OPTION1' => $item['DATA21'] . ': (' . $item['VARIATION1'] . ') ' . $item['ABBREV1'],
                        'OPTION2' => $item['DATA22'] . ': (' . $item['VARIATION2'] . ') ' . $item['ABBREV2'],
                        'OPTION3' => ($item['VARIATION3']) ? ($item['DATA23'] . ': (' . $item['VARIATION3'] . ') ' . $item['ABBREV3']) : '',
                        'OPTION4' => ($item['VARIATION4']) ? ($item['DATA24'] . ': (' . $item['VARIATION4'] . ') ' . $item['ABBREV4']) : '',
                        'OPTION5' => ($item['VARIATION5']) ? ($item['DATA25'] . ': (' . $item['VARIATION5'] . ') ' . $item['ABBREV5']) : '',
                        'OPTION6' => ($item['VARIATION6']) ? ($item['DATA26'] . ': (' . $item['VARIATION6'] . ') ' . $item['ABBREV6']) : '',
                        'OPTION7' => ($item['VARIATION7']) ? ($item['DATA27'] . ': (' . $item['VARIATION7'] . ') ' . $item['ABBREV7']) : '',
                        'OPTION8' => ($item['VARIATION8']) ? ($item['DATA28'] . ': (' . $item['VARIATION8'] . ') ' . $item['ABBREV8']) : '',
                        'trans' => (count(array_unique($trans))>1)?array_unique($trans):'',
                        'FROMDATE' => $item['FROM_DATE'],
                        'UPTODATE' => $item['UPTO_DATE'],
                        'OPTION9' => $item['VARIATION1'].'.'.$item['VARIATION2'].'.'.$item['VARIATION3'].'.'.$item['VARIATION4'].'.'.
                            $item['VARIATION5'].'.'.$item['VARIATION6'].'.'.$item['VARIATION7'].'.'.$item['VARIATION8'],


                    ));
            }
            else
            {
                $complectations[str_pad($item['MDLDIR'], 3, "0", STR_PAD_LEFT) . '_' . $item['NNO']. '_' .$item['DATA1']] = array(
                    Constants::NAME => $item['NNO'],
                    Constants::OPTIONS => array(
                        'OPTION1' => $item['DECSEN1'] . ': (' . $item['VARIATION1'] . ') ' . $item['ABBREV1'],
                        'OPTION2' => $item['DECSEN2'] . ': (' . $item['VARIATION2'] . ') ' . $item['ABBREV2'],
                        'OPTION3' => ($item['VARIATION3']) ? ($item['DECSEN3'] . ': (' . $item['VARIATION3'] . ') ' . $item['ABBREV3']) : '',
                        'OPTION4' => ($item['VARIATION4']) ? ($item['DECSEN4'] . ': (' . $item['VARIATION4'] . ') ' . $item['ABBREV4']) : '',
                        'OPTION5' => ($item['VARIATION5']) ? ($item['DECSEN5'] . ': (' . $item['VARIATION5'] . ') ' . $item['ABBREV5']) : '',
                        'OPTION6' => ($item['VARIATION6']) ? ($item['DECSEN6'] . ': (' . $item['VARIATION6'] . ') ' . $item['ABBREV6']) : '',
                        'OPTION7' => ($item['VARIATION7']) ? ($item['DECSEN7'] . ': (' . $item['VARIATION7'] . ') ' . $item['ABBREV7']) : '',
                        'OPTION8' => ($item['VARIATION8']) ? ($item['DECSEN8'] . ': (' . $item['VARIATION8'] . ') ' . $item['ABBREV8']) : '',
                        'trans' => (count(array_unique($trans))>1)?array_unique($trans):'',
                        'FROMDATE' => $item['FROM_DATE'],
                        'UPTODATE' => $item['UPTO_DATE'],
                        'OPTION9' => $item['VARIATION1'].'.'.$item['VARIATION2'].'.'.$item['VARIATION3'].'.'.$item['VARIATION4'].'.'.
                            $item['VARIATION5'].'.'.$item['VARIATION6'].'.'.$item['VARIATION7'].'.'.$item['VARIATION8']

                    ));

            }

        }

         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");
        $table = 'genloc_all';

        $what = 'PICGROUP';
        $k = 1;

        if ($regionCode != 'JP')
        {
            $sql = "
        SELECT $what, PARTNAME_E, PIMGSTR, X_LT, Y_LT
        FROM $table
        WHERE CATALOG = :regionCode
        and MDLDIR = :MDLDIR
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('MDLDIR',  $MDLDIR);

            $query->execute();
            $aData = $query->fetchAll();
            $k = 2;

        }

        else
        {
            $sql = "
        SELECT emoloc_jp.PICGROUP, emoloc_jp.PIMGSTR, '1' AS X_LT, '1' AS Y_LT
        FROM emoloc_jp
        WHERE emoloc_jp.CATALOG = :regionCode
        and emoloc_jp.MDLDIR = :MDLDIR
        ORDER by emoloc_jp.PICGROUP
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('MDLDIR',  $MDLDIR);

            $query->execute();
            $aData = $query->fetchAll();

            foreach($aData as &$item)
            {
                switch (substr($item['PICGROUP'],0,1))
                {
                    case 'V': $item['PARTNAME_E'] = 'ACCESSORY. PART'.substr($item['PICGROUP'],1,1); unset($item);break;
                    case 'W': $item['PARTNAME_E'] = 'ELECTRICAL. PART'.substr($item['PICGROUP'],1,1); unset($item);break;
                    case 'X': $item['PARTNAME_E'] = 'CHASIS,POWER TRAIN,BRAKE. PART'.substr($item['PICGROUP'],1,1);unset($item);break;
                    case 'Y': $item['PARTNAME_E'] = 'BODY,TRIM. PART'.substr($item['PICGROUP'],1,1);unset($item);break;
                    case 'Z': $item['PARTNAME_E'] = 'ENGINE,FUEL. PART'.substr($item['PICGROUP'],1,1);unset($item);break;



                }
            }



        }

        $groups = array();


        foreach($aData as $item){

            $groups[$item['PICGROUP']] = array(
                Constants::NAME     =>$item ['PARTNAME_E'],
                Constants::OPTIONS => array('picture' => strtoupper($item ['PIMGSTR']),
                    Constants::X1 => floor($item['X_LT']/$k),
                    Constants::X2 => floor($item['X_LT']/$k)+30,
                    Constants::Y1 => floor($item['Y_LT']/$k)-2,
                    Constants::Y2 => floor($item['Y_LT']/$k)+30,
                )
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
        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");



        if ($regionCode != 'JP')
        {
            $sql = "
        SELECT id, FIGURE, PARTNAME_E, X_LT, Y_LT
        FROM gsecloc_all
        WHERE PICGROUP = :groupCode
        AND MDLDIR = :MDLDIR
        AND CATALOG = :regionCode
        ORDER BY FIGURE
        ";

            $nameSubgroup = 'PARTNAME_E';
            $k = 2;
        }

        else
        {
            $sql = "
        SELECT id, FIGURE, PARTNAME, X_LT, Y_LT
        FROM esecloc_jp
        WHERE PICGROUP = :groupCode
        AND MDLDIR = :MDLDIR
        AND CATALOG = :regionCode
        ORDER BY FIGURE
        ";

            $nameSubgroup = 'PARTNAME';
            $k = 2.5;
        }




        $query = $this->conn->prepare($sql);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('MDLDIR',  $MDLDIR);
        $query->execute();

        $aData = $query->fetchAll();



           $subgroups = array();


           foreach($aData as $item)
           {




               $subgroups[$item['FIGURE']] = array(

               Constants::NAME => $item[$nameSubgroup],
                   Constants::OPTIONS => array(
                       Constants::X1 => floor($item['X_LT']/$k),
                       Constants::X2 => floor($item['X_LT']/$k)+30,
                       Constants::Y1 => floor($item['Y_LT']/$k),
                       Constants::Y2 => floor($item['Y_LT']/$k)+20,
                   )

               );

           }

           return $subgroups;
       }

       public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
       {
           $datas = array();
           $datas = explode('_', $complectationCode);

           $complectation = $this->getComplForSchemas($regionCode, ltrim($datas[0],'0'), $datas[1], $datas[2]);




           $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");


           $sql = "
           SELECT *
           FROM illnote
           WHERE illnote.CATALOG= :regionCode
           and  illnote.MDLDIR = :MDLDIR
           and illnote.FIGURE LIKE :subGroupCode
           ";

           $query = $this->conn->prepare($sql);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('MDLDIR',  $MDLDIR);
           $query->bindValue('subGroupCode',  '%'.$subGroupCode.'%');
           $query->execute();

           $aData = $query->fetchAll();
           $schemaOptions = array();
           $plus = array();

           if ($regionCode != 'JP') {

               foreach ($aData as $index => $value) {
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

                           foreach ($complectation[0] as $indexCo => $valueCo)
                           {

                               if (stripos($valueCo, $item) !== false) {
                                   $ct = $ct + 1;
                               }

                           }



                       }


                   }




                   if ($ct === 0) {
                       unset ($aData[$index]);
                   }

               }
           }



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