<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\LandRoverBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\LandRoverBundle\Components\LandRoverConstants;

class LandRoverCatalogModel extends CatalogModel{

    public function getRegions(){

        $aData = array('EU' => 'Европа');



        $regions = array();
        foreach($aData as $index => $value)
        {
            $regions[$index] = array(Constants::NAME=>$value, Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {

            $sql = "
        SELECT auto_name, model_id, engine_type
        FROM lrec
        ORDER by ABS(model_id)
        ";



        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();
        $aDatas = array();

        foreach($aData as $item) {

            if ($item['model_id'] !== NULL)
            $aDatas[] = $item['model_id'];
        }

            $models = array();


        foreach($aData as $item){

            if ($item['model_id'] !== null)

            $models[$item['model_id'].'_'.(ctype_alpha($item['engine_type'])?'GC'.$item['engine_type']:$item['engine_type'])] = array(Constants::NAME=>strtoupper($item['auto_name']),
            Constants::OPTIONS=>array(
                'key' => (array_search($item['model_id'],$aDatas)!=0)?array_search($item['model_id'],$aDatas):"0",
                'type' => ctype_alpha($item['engine_type'])?'GC'.$item['engine_type']:$item['engine_type']

            ));
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {


        $modifications = array();

            $modifications['1'] = array(
                Constants::NAME     => '1',
                Constants::OPTIONS  => array());


        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {


        $complectations = array();


                $complectations['1'] = array(
                    Constants::NAME => '1',
                    Constants::OPTIONS => array());


         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));

        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));


            $sql = "
        SELECT SUBSTRING(name_group,1,1) as ngroup, lex_name, coordinates.num_index
        FROM coord_header_info
        LEFT JOIN lex ON (lex.index_lex = coord_header_info.id_main AND lex.lang = 'EN')
        INNER JOIN coordinates ON (coordinates.model_id = coord_header_info.model_id AND coordinates.label_name = coord_header_info.name_group
        AND LENGTH(coordinates.label_name) > 1)

        WHERE coord_header_info.model_id = :modelCode
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('modelCode',  $modelCode);

            $query->execute();
            $aData = $query->fetchAll();



        $groups = array();


        foreach($aData as $item){

            $groups[$item['ngroup']] = array(
                Constants::NAME     =>$item ['lex_name'],
                Constants::OPTIONS => array(
                    'picture' => $item['num_index'],
                    'pictureFolder' => $pictureFolder,
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
        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));
        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));



        $sql = "
        SELECT SUBSTRING(coord_header_info.name_group,2,2) as nsubgroup, lex_name, coordinates.num_index, coordinates.x1, coordinates.x2, coordinates.y1, coordinates.y2
        FROM coord_header_info
        INNER JOIN lex ON (lex.index_lex = coord_header_info.id_sector AND lex.lang = 'EN')
        INNER JOIN coordinates ON (coordinates.model_id = coord_header_info.model_id AND coordinates.label_name = CONCAT(:groupCode, SUBSTRING(coord_header_info.name_group,3,1)))
        WHERE coord_header_info.model_id = :modelCode
        AND SUBSTRING(coord_header_info.name_group,1,1) = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('groupCode',  $groupCode);


        $query->execute();
        $aData = $query->fetchAll();



           $subgroups = array();


           foreach($aData as $item)
           {




               $subgroups[$item['nsubgroup']] = array(

               Constants::NAME => $item['lex_name'],
                   Constants::OPTIONS => array(
                       'picture' => $item['num_index'],
                       'pictureFolder' => $pictureFolder,
                       Constants::X1 => floor($item['x1']),
                       Constants::X2 => floor($item['x2']),
                       Constants::Y1 => floor($item['y1']),
                       Constants::Y2 => floor($item['y2']),
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