<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\AudiBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\AudiBundle\Components\AudiConstants;

class AudiCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT markt
        FROM all_overview
        WHERE catalog = 'au'
        AND markt = 'RDW'
        OR markt = 'USA'
        UNION
        SELECT markt
        FROM all_overview
        WHERE catalog = 'au'
        AND markt NOT LIKE 'RDW'
        AND markt NOT LIKE 'USA'
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();



        $regions = array();
        foreach($aData as $item)
        {
            $regions[$item['markt']] = array(Constants::NAME=>$item['markt'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT modell, bezeichnung
        FROM all_overview
        WHERE catalog = 'au'
        and markt = :regionCode
        GROUP BY modell
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[urlencode($item['modell'])] = array(Constants::NAME=>strtoupper('('.$item['modell'].') '.$item['bezeichnung']),
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        $sql = "
        SELECT einsatz, epis_typ
        FROM all_overview
        WHERE all_overview.catalog = 'au'
        and markt = :regionCode
        and modell = :modelCode
        and bezeichnung = ''
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['einsatz'].'_'.$item['epis_typ']] = array(
                Constants::NAME     => $item['einsatz'],
                Constants::OPTIONS  => array());

        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {   $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $modelCode = rawurldecode($modelCode);
       $sql = "
        SELECT *
        FROM vin_model
        WHERE model =:modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();
             
        $aForPNC = array();
        $aIndexes = array('body_type', 'engine_capacity', 'engine_type', 'fuel_type', 'transaxle', 'field14');
        foreach($aData as &$item)
        {
        foreach($item as $index => $value)
            {
        if (in_array($index, $aIndexes))
                {
                    $item[str_pad((array_search($index, $aIndexes)+1), 2, "0", STR_PAD_LEFT)] = $value;
                    $aForPNC[$item['model_index']][] = $value;
                }

		    }
        }

        $complectations = array();

        foreach ($aData as &$item) {
            $aData1 = array();
            $aOptions = array();
            foreach ($item as $index => $value)
            {
                $sql = "
        SELECT ucc_type, ucc_type_code, ucc_code_short
        FROM cats_0_ucc
        WHERE model =:modificationCode
        AND ucc = :value
        AND ucc_type = :index
        ";

                $query = $this->conn->prepare($sql);
                $query->bindValue('modificationCode', $modificationCode);
                $query->bindValue('index', $index);
                $query->bindValue('value', $value);
                $query->execute();

                $aData1[] = $query->fetch();
            }
            foreach ($aData1 as $index1 => $value1)
            {
                if ($value1 == '')
                {
                    unset ($aData1[$index1]);
                }
            }

            $aProm = array();
            foreach ($aData1 as $item1)
            {
                $aProm[$item1['ucc_type']] = $item1;

            }


            foreach ($aProm as &$item2)
            {
                foreach ($item2 as &$item3)
                {

                    $sql = "
                    SELECT lex_name
                    FROM hywlex
                    WHERE lex_code =:item3
                    AND lang = 'EN'
                    ";

                    $query = $this->conn->prepare($sql);
                    $query->bindValue('item3', $item3);
                    $query->execute();
                    $sData2 = $query->fetch();
                    if ($sData2)
                    {
                        $item3 = $sData2['lex_name'];
                    }

                }

            }

            foreach ($aProm as $item4)
            {
                $aOptions[$item['model_index']][] = ($item4['ucc_type_code'].': '.$item4['ucc_code_short']);
            }


            $complectations[$item['model_index']] = array(
                Constants::NAME => $item['model_code'],
                Constants::OPTIONS => array(

                    'option1' => $aOptions[$item['model_index']],
                    Constants::START_DATE   => $item['start_data'],
                    Constants::END_DATE   => $item['finish_data'],
                    'option2' => $aForPNC[$item['model_index']], /*Добавлена для последующего использования в выборе нужного артикула в методе getArticuls*/
                )
            );
        }

         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        SELECT hauptgruppen
        FROM all_overview
        WHERE all_overview.catalog = 'au'
        and markt = :regionCode
        and modell = :modelCode
        and einsatz = :modificationCode
        and bezeichnung = ''
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchColumn(0);
        $sData = preg_split('//', $aData, -1, PREG_SPLIT_NO_EMPTY);

        $aGroup = array();
        foreach ($sData as $item)
        {
            $sql = "
        SELECT text, hg
        FROM all_duden, all_hg
        WHERE all_hg.hgts = all_duden.ts and all_hg.hg = :item and all_hg.catalog = 'au' and all_duden.catalog = all_hg.catalog and all_duden.lang = 'R'
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('item',  $item);
            $query->execute();
            $aGroup[] = $query->fetch();
        }

        $groups = array();


        foreach($aGroup as $item){

            $groups[$item['hg']=='0'?'10':$item['hg']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['text']),'utf8'),
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
        $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $groupCode = (($groupCode == '10')?'0':$groupCode);

        $sql = "
        SELECT all_katalog.id, all_katalog.bildtafel, all_katalog.grafik, all_katalog.bildtafel2
        FROM all_katalog
        WHERE all_katalog.catalog = 'au'
        and all_katalog.epis_typ = :modificationCode
        and  LEFT(hg_ug, 1) = :groupCode
        and all_katalog.bildtafel <> ''
        and dir_name = 'R'
        ";


/*
        $sql = "
        SELECT all_katalog.hg_ug, all_katalog.tsben, all_katalog.bildtafel2, all_katalog.modellangabe, ou
        FROM all_katalog
        WHERE all_katalog.catalog = 'au'
        and all_katalog.epis_typ = :modificationCode
        and  LEFT(hg_ug, 1) = :groupCode
        ";*/

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();
        $ObDvig = array();


        foreach($aData as $item)

        {
            $sqlSub = "
        SELECT all_katalog.hg_ug, all_katalog.tsben, all_katalog.bildtafel2, all_katalog.modellangabe, ou, all_katalog.tsbem
        FROM all_katalog
        WHERE all_katalog.id = :item +1
        ";

            $query = $this->conn->prepare($sqlSub);
            $query->bindValue('item',  $item['id']);
            $query->execute();

            $aDataSub[$item['bildtafel2']] = $query->fetch();
            $aDataSub[$item['bildtafel2']]['grafik'] = $item['grafik'];

           $ObDvig[]=$this->getDesc($aDataSub[$item['bildtafel2']]['tsbem'], 'R');

            /*    $ObDvig[] = $aDataSub[$item['bildtafel2']]['tsbem'];*/
           }
        $sDataLitr = array();

        foreach($ObDvig as $index=>&$value)
                    {
                        preg_match_all("/\d{1}\,\d{1}/x",
                            $value, $sDataLitr);

                         foreach($sDataLitr as $item)
                        {
                            foreach($item as $item1)
                            {
                                $sDataLitr0[] = $item1;
                            }

                        }


        }


           $subgroups = array();

           foreach($aDataSub as $item)
           {

               $subgroups[$item['bildtafel2']] = array(

                   Constants::NAME => $this->getDesc($item['tsben'], 'R'),
                   Constants::OPTIONS => array('dannye'=>$item['modellangabe'],
                                               'podgr'=>$item['hg_ug'],
                                               'prime4'=>$this->getDesc($item['tsbem'], 'R'),
                                               'grafik'=>substr($item['grafik'],strlen($item['grafik'])-3,3).'/'.substr($item['grafik'],strlen($item['grafik'])-3,3).substr($item['grafik'],1,5).substr($item['grafik'],0,1),
                                               'ObDvig'=>array_unique($sDataLitr0))

               );

           }
           return $subgroups;
       }

       public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
       {
           $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

           $groupCode = (($groupCode == '10')?'0':$groupCode);

           $sql = "
           SELECT all_katalog.id, all_katalog.bildtafel, all_katalog.grafik, all_katalog.bildtafel2
           FROM all_katalog
           WHERE all_katalog.catalog = 'au'
           and all_katalog.epis_typ = :modificationCode
           and  LEFT(hg_ug, 1) = :groupCode
           and all_katalog.bildtafel <> ''
           and dir_name = 'R'
           and bildtafel2 = :subGroupCode

           ";

           $query = $this->conn->prepare($sql);
           $query->bindValue('modificationCode',  $modificationCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('subGroupCode',  $subGroupCode);
           $query->execute();

           $aData = $query->fetchAll();

           $schemas = array();
           foreach($aData as $item)
           {

                       $schemas[substr($item['grafik'],strlen($item['grafik'])-3,3).substr($item['grafik'],1,5).substr($item['grafik'],0,1)] = array(
                       Constants::NAME => urlencode(substr($item['grafik'],strlen($item['grafik'])-3,3).'/'.substr($item['grafik'],strlen($item['grafik'])-3,3).substr($item['grafik'],1,5).substr($item['grafik'],0,1)),
                       Constants::OPTIONS => array('cd'=>substr($item['grafik'],strlen($item['grafik'])-3,3))
                   );
           }


           return $schemas;
       }

       public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
       {
           print_r($schemaCode); die;

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

           $modificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

           $groupCode = (($groupCode == '10')?'0':$groupCode);

           $sqlPnc = "
           SELECT all_katalog.btpos, all_katalog.tsben, all_katalog.bemerkung
           FROM all_katalog
           WHERE all_katalog.catalog = 'au'
           and all_katalog.epis_typ = :modificationCode
           and  LEFT(hg_ug, 1) = :groupCode

           and all_katalog.bildtafel = ''
           and ou = ''
           and dir_name = 'R'
           and bildtafel2 = :subGroupCode

           ";

           $query = $this->conn->prepare($sqlPnc);
           $query->bindValue('modificationCode',  $modificationCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('subGroupCode',  $subGroupCode);
           $query->execute();

           $aPncs = $query->fetchAll();

           foreach ($aPncs as $index=>$value)
           {
               if ($value['tsben'] == '22358')
               {
                   unset ($aPncs[$index]);
               }
           }



           foreach ($aPncs as &$aPnc)
           {

           $sqlSchemaLabels = "
           SELECT cLeft, cTop, cWidth, cHeight
           FROM all_coord
           WHERE dirname = :cd
             AND cPoint = :pnc_code
             AND filename =  :schemaCode
           ";

           $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('schemaCode', $schemaCode);
               $query->bindValue('cd', $options['cd']);
           $query->bindValue('pnc_code', str_replace(array('(', ')'),'',$aPnc['btpos']));
           $query->execute();

           $aPnc['clangjap'] = $query->fetchAll();

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
                   $pncs[str_replace(array('(', ')'),'',$value['btpos'])][Constants::OPTIONS][Constants::COORDS][$item1['cLeft']] = array(
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
        WHERE all_katalog.catalog = 'au'
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
        WHERE :item = all_duden.ts and all_duden.catalog = 'au' and all_duden.lang = 'R'
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