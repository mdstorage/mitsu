<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\FiatBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\FiatBundle\Components\FiatConstants;

class FiatCatalogModel extends CatalogModel{

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
        and comm_modgrp.mk2_cod LIKE 'F' or comm_modgrp.mk2_cod LIKE 'T'
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
        SELECT groups_dsc.grp_cod, groups_dsc.grp_dsc
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

            $groups[$item['grp_cod']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['grp_dsc']),'utf8'),
                Constants::OPTIONS  => array()
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
        SELECT subgroups_dsc.sgrp_cod Cod, subgroups_dsc.sgrp_dsc Dsc
        FROM subgroups_dsc, subgroups_by_cat
        WHERE subgroups_by_cat.cat_cod = :modificationCode
        and subgroups_by_cat.grp_cod = subgroups_dsc.grp_cod and subgroups_by_cat.sgrp_cod = subgroups_dsc.sgrp_cod and subgroups_dsc.grp_cod = :groupCode and subgroups_dsc.lng_cod = 'N'
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();



           $subgroups = array();

           foreach($aData as $item)
           {

               $subgroups[$groupCode.$item['Cod']] = array(

                   Constants::NAME => mb_strtoupper(iconv('cp1251', 'utf8', $item['Dsc']),'utf8'),
                   Constants::OPTIONS => array()

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
           }var_dump($singleCoords); die;
           foreach ($singleCoords as $index=>$value)
           {
               foreach ($value as $index_1=>$value_1)
               {
                   $aCoords1[$index_1] = explode(',', $value_1);
               }
               $aCoords[$index] = $aCoords1[0];

           }

           var_dump($aCoords); die;

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
                   $pncs[$value['tbd_rif']][Constants::OPTIONS][Constants::COORDS][$item1['cLeft']] = array(
                       Constants::X2 => floor(($item1['cLeft'])),
                       Constants::Y2 => $item1['cTop'],
                       Constants::X1 => $item1['cWidth'] + $item1['cLeft'],
                       Constants::Y1 => $item1['cHeight'] + $item1['cTop']);

                   }

                   if (strpos($value['tsben'],'16529'))
                   {
                       $str[str_replace(array('(', ')'),'',$value['btpos'])] = str_replace(';',' ',$value['bemerkung']);
                   }
                   else {
                       $str[str_replace(array('(', ')'),'',$value['btpos'])] = '';
                   }


               }
           }


           foreach ($aPncs as $item) {
               if (strpos($this->getDesc($item['tsben'], 'R'),';'))
               {
                   $name = substr($this->getDesc($item['tsben'], 'R'),0,strpos($this->getDesc($item['tsben'], 'R'),';')).$str[str_replace(array('(', ')'),'',$item['btpos'])];

               }
               else
               {
                   $name = $this->getDesc($item['tsben'], 'R').$str[str_replace(array('(', ')'),'',$item['btpos'])];

               }



                   $pncs[str_replace(array('(', ')'),'',$item['btpos'])][Constants::NAME] = $name;



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

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode, $options)
    {

        $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $groupCode = (($groupCode == '10')?'0':$groupCode);

        $sqlPnc = "
        SELECT all_katalog.teilenummer, all_katalog.tsben, all_katalog.tsbem, all_katalog.modellangabe, all_katalog.stuck, einsatz, auslauf, mv_data, all_stamm.gruppen_data newArt,
        all_stamm.entfalldatum dataOtmeny, all_katalog.bemerkung
        FROM all_katalog
        left join all_stamm on (all_stamm.catalog = all_katalog.catalog and (all_stamm.markt = :regionCode or all_stamm.markt = '') and all_stamm.teilenummer = all_katalog.teilenummer)
        WHERE all_katalog.catalog = 'se'
        and all_katalog.epis_typ = :modificationCode
        and  LEFT(hg_ug, 1) = :groupCode
        and all_katalog.bildtafel = ''
        and dir_name = 'R'
        and bildtafel2 = :subGroupCode
        and (btpos = :pncCode or btpos = :pncCodemod)

        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('subGroupCode',  $subGroupCode);
        $query->bindValue('pncCode', '('.$pncCode.')');
        $query->bindValue('pncCodemod', $pncCode);
        $query->execute();


        $aArticuls = $query->fetchAll();


        $articuls = array();
      
        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['teilenummer']] = array(
                Constants::NAME => $this->getDesc($item['tsben'], 'R'),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['stuck'],
                    Constants::START_DATE => $item['einsatz'],
                    Constants::END_DATE => $item['auslauf'],
                    'prime4' => ($this->getDesc($item['tsbem'], 'R'))?$this->getDesc($item['tsbem'], 'R'):$item['bemerkung'],
                    'dannye' => $item['modellangabe'],
                    'with' => $item['mv_data'],
                    'zamena' => substr($item['newArt'], 0, strpos($item['newArt'], '~')),
                    'zamenakoli4' => substr($item['newArt'], strpos($item['newArt'], '~'), strlen($item['newArt'])),
                    'dataOtmeny' => $item['dataOtmeny']


                )
            );
            
        }


        return $articuls;
    }

    public function getDesc($sitemCode, $language)
    {
        $aitemCode = array();
        $aGroup = array();


        $aitemCode = explode(';',$sitemCode);

        foreach ($aitemCode as $index=>&$value)
        {
            if ($value == '')
            {
               unset ($aitemCode[$index]);
            }
            $value = str_replace('~', '', $value);

        }

        foreach ($aitemCode as $item)
        {
            $sqlLex = "
        SELECT text
        FROM all_duden
        WHERE :item = all_duden.ts and all_duden.catalog = 'se' and all_duden.lang = 'R'
        ";

            $query = $this->conn->prepare($sqlLex);
            $query->bindValue('item',  $item);
            $query->execute();
            $aGroup[] = $query->fetchColumn(0);

        }

        $sGroup = implode('; ', array_unique($aGroup));


        return mb_strtoupper(iconv('cp1251', 'utf8', $sGroup), 'utf8');

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