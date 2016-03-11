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
        GROUP BY CATALOG
        ";

        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item)
        {
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
         VARIATION1, VARIATION2, VARIATION3, VARIATION4, VARIATION5, VARIATION6, VARIATION7, VARIATION8, NNO, abbrev1.DESCRIPTION ABBREV1, abbrev2.DESCRIPTION ABBREV2, abbrev3.DESCRIPTION ABBREV3,
         abbrev4.DESCRIPTION ABBREV4, abbrev5.DESCRIPTION ABBREV5, abbrev6.DESCRIPTION ABBREV6, abbrev7.DESCRIPTION ABBREV7, abbrev8.DESCRIPTION ABBREV8, posname.MDLDIR
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
                if ($item['DATA2'.$i]=='TRANS')
                {
                    $trans[] = $item['VARIATION'.$i];
                }
            }


        }

        foreach($aData as $item){
            $complectations[$item['MDLDIR'].'_'.$item['NNO']] = array(
                Constants::NAME     => $item['NNO'],
                Constants::OPTIONS  => array(
                    'OPTION1' =>  $item['DATA21'].': ('.$item['VARIATION1'].') '.$item['ABBREV1'],
                    'OPTION2' => $item['DATA22'].': ('.$item['VARIATION2'].') '.$item['ABBREV2'],
                    'OPTION3' => ($item['VARIATION3'])?($item['DATA23'].': ('.$item['VARIATION3'].') '.$item['ABBREV3']):'',
                    'OPTION4' => ($item['VARIATION4'])?($item['DATA24'].': ('.$item['VARIATION4'].') '.$item['ABBREV4']):'',
                    'OPTION5' => ($item['VARIATION5'])?($item['DATA25'].': ('.$item['VARIATION5'].') '.$item['ABBREV5']):'',
                    'OPTION6' => ($item['VARIATION6'])?($item['DATA26'].': ('.$item['VARIATION6'].') '.$item['ABBREV6']):'',
                    'OPTION7' => ($item['VARIATION7'])?($item['DATA27'].': ('.$item['VARIATION7'].') '.$item['ABBREV7']):'',
                    'OPTION8' => ($item['VARIATION8'])?($item['DATA28'].': ('.$item['VARIATION8'].') '.$item['ABBREV8']):'',
                    'trans' => array_unique($trans),

                ));

        }

         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $MDLDIR = substr($complectationCode, 0, strpos($complectationCode, '_'));


        $sql = "
        SELECT PICGROUP, PARTNAME_E, PIMGSTR
        FROM genloc_all
        WHERE CATALOG = :regionCode
        and MDLDIR = :MDLDIR
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('MDLDIR',  $MDLDIR);

        $query->execute();

        $aData = $query->fetchAll();


        $aGroup = array();

        $groups = array();


        foreach($aData as $item){

            $groups[$item['PICGROUP']] = array(
                Constants::NAME     =>$item ['PARTNAME_E'],
                Constants::OPTIONS => array('picture' => strtoupper($item ['PIMGSTR'])
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
        $MDLDIR = substr($complectationCode, 0, strpos($complectationCode, '_'));


        $sql = "
        SELECT id, FIGURE, PARTNAME_E, X_LT, Y_LT
        FROM gsecloc_all
        WHERE PICGROUP = :groupCode
        AND MDLDIR = :MDLDIR
        AND CATALOG = :regionCode
        ORDER BY FIGURE
        ";



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

                   Constants::NAME => $item['PARTNAME_E'],
                   Constants::OPTIONS => array(
                       Constants::X1 => floor($item['X_LT']/2),
                       Constants::X2 => floor($item['X_LT']/2)+30,
                       Constants::Y1 => floor($item['Y_LT']/2),
                       Constants::Y2 => floor($item['Y_LT']/2)+20,
                   )

               );

           }
           return $subgroups;
       }

       public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
       {


           $sql = "
           SELECT *
           FROM drawings, tables_dsc
           WHERE cat_cod = :modificationCode
           and  grp_cod = :groupCode
           and sgrp_cod = :subGroupCode
           and tables_dsc.lng_cod = 'N'
           and tables_dsc.cod = drawings.table_dsc_cod
           ";

           $query = $this->conn->prepare($sql);
           $query->bindValue('modificationCode',  $modificationCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('subGroupCode',  str_replace($groupCode,'',$subGroupCode));
           $query->execute();

           $aData = $query->fetchAll();
           $schemas = array();
           foreach($aData as $item)
           {
               $schCode = substr($item['img_path'], strpos($item['img_path'], '/')+1, strlen($item['img_path']));
               $schCatalog = substr($item['img_path'], 0, strpos($item['img_path'], '/'));

                       $schemas[$item['variante'].'_'.$item['sgs_cod']] = array(
                       Constants::NAME => mb_strtoupper(iconv('cp1251', 'utf8', $item['dsc']),'utf8'),
                       Constants::OPTIONS => array('catalog' => $schCatalog,
                                                    'picture' => $schCode,
                                                    'pattern' => $item['pattern']
                                                  )
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

          $sgs_cod = substr($schemaCode, strpos($schemaCode, '_')+1, strlen($schemaCode));
           $variante = substr($schemaCode, 0, strpos($schemaCode, '_'));


           $sqlPnc = "
           SELECT tbd_rif, hotspots, cds_dsc
            FROM `tbdata`, codes_dsc
            WHERE `cat_cod` LIKE :modificationCode
            AND `grp_cod` = :groupCode
            AND `sgrp_cod` = :subGroupCode
            and variante = :variante
            and sgs_cod = :sgs_cod
            and tbdata.cds_cod = codes_dsc.cds_cod
            and codes_dsc.lng_cod = 'N'
            order by tbd_rif

           ";

           $query = $this->conn->prepare($sqlPnc);
           $query->bindValue('modificationCode',  $modificationCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('subGroupCode',  str_replace($groupCode,'',$subGroupCode));
           $query->bindValue('variante',  $variante);
           $query->bindValue('sgs_cod',  $sgs_cod);

           $query->execute();

           $aPncs = $query->fetchAll();


            $singleCoords = array();
            $aCoords = array();
           $aCoords1 = array();

           foreach ($aPncs as &$aPnc)
           {
               $singleCoords[$aPnc['tbd_rif']] = explode(';', $aPnc['hotspots']);
           }


           foreach ($singleCoords as $index=>$value)
           {
               foreach ($value as $index_1=>$value_1) {
                   $aCoords1[$index_1] = explode(',', $value_1);

                   $aCoords[$index][$index_1] = $aCoords1[$index_1];
               }

           }





           $pncs = array();
           $str = array();
         foreach ($aPncs as $index=>$value) {
               {

                   foreach ($aCoords[$value['tbd_rif']] as $item1)
                   {
                   $pncs[$value['tbd_rif']][Constants::OPTIONS][Constants::COORDS][$item1[0]] = array(
                       Constants::X1 => floor(($item1[0])),
                       Constants::Y1 => $item1[1],
                       Constants::X2 => $item1[2],
                       Constants::Y2 => $item1[3]);

                   }

               }
           }


           foreach ($aPncs as $item) {



                   $pncs[$item['tbd_rif']][Constants::NAME] = iconv('cp1251', 'utf8', $item['cds_dsc']);



           }
            return $pncs;
       }

       public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
       {
        /*   $sqlSchemaLabels = "
           SELECT p.part_code, p.xs, p.ys, p.xe, p.ye
           FROM pictures p
           WHERE p.catalog = :regionCode
             AND p.cd = :cd
             AND p.pic_name = :schemaCode
             AND p.XC26ECHK = 2
           ";

           $query = $this->conn->prepare($sqlSchemaLabels);
           $query->bindValue('regionCode', $regionCode);
           $query->bindValue('cd', $cd);
           $query->bindValue('schemaCode', $schemaCode);
           $query->execute();

           $aDataLabels = $query->fetchAll();

           $articuls = array();
           foreach ($aDataLabels as $item) {
               $articuls[$item['part_code']][Constants::NAME] = $item['part_code'];

               $articuls[$item['part_code']][Constants::OPTIONS][Constants::COORDS][] = array(
                   Constants::X1 => $item['xs'],
                   Constants::Y1 => $item['ys'],
                   Constants::X2 => $item['xe'],
                   Constants::Y2 => $item['ye'],
               );
           }*/
$articuls = array();
        return $articuls;
    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
     /*   $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));


        $sqlSchemaLabels = "
        SELECT name, x1, y1, x2, y2
        FROM cats_coord
        WHERE catalog_code =:catCode
          AND compl_name =:schemaCode
          AND quantity = 5
          ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();
        foreach ($aData as $item)
        {
            $groups[$item['name']][Constants::NAME] = $item['name'];
            $groups[$item['name']][Constants::OPTIONS][Constants::COORDS][] = array(
                Constants::X1 => ($item['x1']),
                Constants::Y1 => $item['y1'],
                Constants::X2 => $item['x2'],
                Constants::Y2 => $item['y2']);
        }*/

        $groups = array();
        return $groups;
    }

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $pncCode, $options)
    {
         $sgs_cod = substr($schemaCode, strpos($schemaCode, '_')+1, strlen($schemaCode));
          $variante = substr($schemaCode, 0, strpos($schemaCode, '_'));

         $sqlPnc = "
         SELECT prt_cod, tbd_qty, cds_dsc
          FROM `tbdata`, codes_dsc
          WHERE `cat_cod` LIKE :modificationCode
          AND `grp_cod` = :groupCode
          AND `sgrp_cod` = :subGroupCode
          and variante = :variante
          and sgs_cod = :sgs_cod
          and tbdata.cds_cod = codes_dsc.cds_cod
          and codes_dsc.lng_cod = 'N'
          and tbd_rif = :pncCode

         ";

         $query = $this->conn->prepare($sqlPnc);
         $query->bindValue('modificationCode',  $modificationCode);
         $query->bindValue('groupCode',  $groupCode);
         $query->bindValue('subGroupCode',  str_replace($groupCode,'',$subGroupCode));
         $query->bindValue('variante',  $variante);
         $query->bindValue('sgs_cod',  $sgs_cod);
         $query->bindValue('pncCode',  $pncCode);

         $query->execute();

         $aArticuls = $query->fetchAll();

$articuls = array();

        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['prt_cod']] = array(
                Constants::NAME => iconv('cp1251', 'utf8', $item['cds_dsc']),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['tbd_qty'],
                    Constants::START_DATE => '',
                    Constants::END_DATE => '',

                )
            );
            
        }


        return $articuls;
    }

    public function getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode)
    {


        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $sqlGroup = "
        SELECT part
        FROM cats_map
        WHERE sector_name = :subGroupCode
          AND catalog_name = :catCode
        ";

        $query = $this->conn->prepare($sqlGroup);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('catCode', $catCode);
        $query->execute();

        $groupCode = $query->fetchColumn(0);

        return $groupCode;

    }

    
} 