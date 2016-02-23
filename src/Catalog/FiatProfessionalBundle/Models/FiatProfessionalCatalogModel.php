<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\FiatProfessionalBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\FiatProfessionalBundle\Components\FiatProfessionalConstants;

class FiatProfessionalCatalogModel extends CatalogModel{

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
        SELECT comm_modgrp.cmg_cod, comm_modgrp.cmg_dsc
        FROM comm_modgrp, catalogues
        WHERE catalogues.cmg_cod = comm_modgrp.cmg_cod
        and comm_modgrp.mk2_cod LIKE 'T'
        GROUP BY comm_modgrp.cmg_dsc
        ";

        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[urlencode($item['cmg_cod'])] = array(Constants::NAME=>strtoupper($item['cmg_dsc']),
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        $sql = "
        SELECT cat_dsc, cat_cod
        FROM catalogues
        WHERE cmg_cod = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['cat_cod']] = array(
                Constants::NAME     => $item['cat_dsc'],
                Constants::OPTIONS  => array());

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


        $sql = "
        SELECT groups_dsc.grp_cod, groups_dsc.grp_dsc, groups.img_name
        FROM groups_dsc, catalogues, groups
        WHERE catalogues.cat_cod = :modificationCode
        and catalogues.cat_cod = groups.cat_cod and groups.grp_cod = groups_dsc.grp_cod and groups_dsc.lng_cod = 'N'
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();



        $aGroup = array();

        $groups = array();


        foreach($aData as $item){
            $schCode = substr($item['img_name'], strpos($item['img_name'], '/')+1, strlen($item['img_name']));
            $schCatalog = substr($item['img_name'], 0, strpos($item['img_name'], '/'));

            $groups[$item['grp_cod']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['grp_dsc']),'utf8'),
                Constants::OPTIONS => array('catalog' => $schCatalog,
                    'picture' => $schCode
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
        $sql = "
        SELECT subgroups_dsc.sgrp_cod Cod, subgroups_dsc.sgrp_dsc Dsc, groups.img_name, hs_figurini.x1, hs_figurini.y1, hs_figurini.x2, hs_figurini.y2
        FROM subgroups_dsc, subgroups_by_cat, hs_figurini, groups
        WHERE subgroups_by_cat.cat_cod = :modificationCode
        and subgroups_by_cat.grp_cod = subgroups_dsc.grp_cod and subgroups_by_cat.sgrp_cod = subgroups_dsc.sgrp_cod and subgroups_dsc.grp_cod = :groupCode and subgroups_dsc.lng_cod = 'N'
        and groups.cat_cod = :modificationCode and groups.grp_cod = :groupCode and groups.img_name = hs_figurini.img_name and hs_figurini.code = CONCAT(:groupCode,LPAD(subgroups_dsc.sgrp_cod,2,'0'))
        ";



        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();



           $subgroups = array();

           foreach($aData as $item)
           {

               $subgroups[$groupCode.str_pad($item['Cod'], 2, "0", STR_PAD_LEFT)] = array(

                   Constants::NAME => mb_strtoupper(iconv('cp1251', 'utf8', $item['Dsc']),'utf8'),
                   Constants::OPTIONS => array(
                       Constants::X1 => floor($item['x1']),
                       Constants::X2 => $item['x2'],
                       Constants::Y1 => $item['y1'],
                       Constants::Y2 => $item['y2'],
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