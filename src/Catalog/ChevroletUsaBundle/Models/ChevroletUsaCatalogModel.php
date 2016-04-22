<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\ChevroletUsaBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\ChevroletUsaBundle\Components\ChevroletUsaConstants;

class ChevroletUsaCatalogModel extends CatalogModel{

    public function getRegions(){



        $aData = array('USA' => 'USA');



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
        SELECT CATALOG_CODE, MODEL_ID, MODEL_DESC
        FROM model
        WHERE MAKE_DESC = 'Chevrolet'
        GROUP BY MODEL_DESC
        ";

        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[$item['CATALOG_CODE'].'_'.$item['MODEL_ID']] = array(Constants::NAME=>strtoupper($item['MODEL_DESC']),
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));

        $sql = "
        SELECT FIRST_YEAR, LAST_YEAR
        FROM model
        WHERE MAKE_DESC = 'Chevrolet'
        AND CATALOG_CODE = :modelCode
        GROUP BY MODEL_DESC
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);

        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){

            foreach(range($item['FIRST_YEAR'], $item['LAST_YEAR'], 1) as $value)
            {
                $modifications[$value] = array(
                    Constants::NAME     => $value,
                    Constants::OPTIONS  => array());

            }


        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {
        $complectations = array();


         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {



        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));

        $sql = "
        SELECT major_group.MAJOR_GROUP, major_group.MAJOR_DESC, group_master.GROUP_ID
        FROM major_group, group_usage, group_master
        WHERE group_usage.CATALOG_CODE = :modelCode
        and group_usage.GROUP_ID = group_master.GROUP_ID and group_master.MAJOR_GROUP = major_group.MAJOR_GROUP and group_usage.GROUP_TYPE = 'B'
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();


        $groups = array();


        foreach($aData as $item){

            $groups[$item['MAJOR_GROUP']] = array(
                Constants::NAME     => $item ['MAJOR_DESC'],
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

           $subgroups = array();

               $subgroups['1'] = array(

                   Constants::NAME => '1',
                   Constants::OPTIONS => array()

               );

           return $subgroups;
       }

       public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
       {


           $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));


           $sql = "
        SELECT callout_legend.ART_NBR, CAPTION_DESC, callout_legend.IMAGE_NAME
        FROM callout_legend, category, art, caption
        WHERE callout_legend.CATALOG_CODE = :modelCode and CAPTION_GROUP = :groupCode
        and :modificationCode BETWEEN CAPTION_FIRST_YEAR AND CAPTION_LAST_YEAR
        and category.CATEGORY_ID = art.CATEGORY_ID and art.ART_ID = callout_legend.ART_ID
        AND caption.ART_NBR = callout_legend.ART_NBR
        and :modificationCode BETWEEN caption.FIRST_YEAR AND caption.LAST_YEAR
        AND caption.COUNTRY_LANG = 'EN'
        AND caption.CATALOG_CODE = callout_legend.CATALOG_CODE
        GROUP BY callout_legend.ART_NBR
        ";


           $query = $this->conn->prepare($sql);
           $query->bindValue('modelCode',  $modelCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('modificationCode',  $modificationCode);

           $query->execute();

           $aData = $query->fetchAll();



           $schemas = array();

           foreach($aData as $item)
           {

               $schemas[$item['ART_NBR']] = array(

                   Constants::NAME => $item['CAPTION_DESC'],
                   Constants::OPTIONS => array('IMAGE_NAME' => $item['IMAGE_NAME'])

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
           $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));

           var_dump($modelCode); die;


           $sql = "
        SELECT callout_legend.ART_NBR, CAPTION_DESC, callout_legend.IMAGE_NAME
        FROM callout_legend, category, art, caption
        WHERE callout_legend.CATALOG_CODE = :modelCode and CAPTION_GROUP = :groupCode
        and :modificationCode BETWEEN CAPTION_FIRST_YEAR AND CAPTION_LAST_YEAR
        and category.CATEGORY_ID = art.CATEGORY_ID and art.ART_ID = callout_legend.ART_ID
        AND caption.ART_NBR = callout_legend.ART_NBR
        and :modificationCode BETWEEN caption.FIRST_YEAR AND caption.LAST_YEAR
        AND caption.COUNTRY_LANG = 'EN'
        AND caption.CATALOG_CODE = callout_legend.CATALOG_CODE
        GROUP BY callout_legend.ART_NBR
        ";


           $query = $this->conn->prepare($sql);
           $query->bindValue('modelCode',  $modelCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('modificationCode',  $modificationCode);

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