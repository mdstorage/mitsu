<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\MitsubishiBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\MitsubishiBundle\Components\MitsubishiConstants;
use Catalog\MitsubishiBundle\Twig\Extension\DateConvertorExtension;

class MitsubishiCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT m.catalog FROM models m GROUP BY m.catalog
        ";
        $query = $this->conn->prepare($sql);

        $query->execute();

        $aData = $query->fetchAll();


        $regions = array();

        foreach($aData as $item)
        {
            $regions[$item['catalog']] = array(Constants::NAME => $item['catalog'], Constants::OPTIONS => array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT
          m.Catalog_Num as catalogNum,
          m.Rep_Model as repModel,
          md.StartDate as startDate,
          md.EndDate as endDate,
          d.desc_en as descEn
        FROM `models` m
        LEFT JOIN `model_desc` md ON m.Catalog_Num = TRIM(md.catalog_num)
        LEFT JOIN `descriptions` d ON TRIM(md.name) = d.TS
        WHERE m.catalog = :regionCode
        AND TRIM(md.catalog) = :regionCode
        AND d.catalog = :regionCode
        GROUP BY m.Catalog_Num
        ORDER BY d.desc_en, m.Rep_Model
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);

        $query->execute();

        $aData = $query->fetchAll();
        $twig = new DateConvertorExtension();


        $models = array();
        foreach($aData as $item){


                    $models[urlencode($item['catalogNum'])] =
                        array(Constants::NAME=>strtoupper($item['descEn']).' ('.$item['repModel'].') '.$twig->dateMitsubishiConvertor(trim($item['startDate'])).'-'.$twig->dateMitsubishiConvertor(trim($item['endDate'])),
                            Constants::OPTIONS=>array(
                                Constants::START_DATE => $item['startDate'],
                                Constants::END_DATE => $item['endDate']));

        }


        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {

        $sql = "
        SELECT
          m.Model as model,
          d.desc_en as descEn
        FROM `models` m
        LEFT JOIN `descriptions` d ON m.Name1 = d.TS
        WHERE m.catalog = :regionCode
        AND m.Catalog_Num = :modelCode
        AND d.catalog = :regionCode
        GROUP BY m.Model;
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);

        $query->execute();

        $aData = $query->fetchAll();


        $modifications = array();


            foreach($aData as $item)
            {
                    $modifications[$item['model']] = array(
                        Constants::NAME     => '('.$item['model'].') '.$item['descEn'],
                        Constants::OPTIONS  => array());
            }


        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {

        $sql = "
        SELECT
          m.Classification as classification,
          d.desc_en as descEn
        FROM `models` m
        LEFT JOIN `descriptions` d ON m.name = d.TS
        WHERE m.catalog = :regionCode
        AND m.Catalog_NUM = :modelCode
        AND m.Model = :modificationCode
        AND d.catalog = :regionCode
        GROUP BY m.Classification
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);


        $query->execute();

        $aData = $query->fetchAll();



        $complectations = array();

        foreach($aData as $item){

            $complectations[($item['classification'])] = array(
                Constants::NAME     => $item['classification'].' ('.strtoupper($item['descEn']).')',
                Constants::OPTIONS => array()
            );
        }


         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {


        $sql = "
        SELECT
          CONCAT(mg.MainGroup, SUBSTRING(mg.MajorGroup, 1, 1)) as MainGroup,
          d.desc_en as descEn,
          illustration,
          startX, startY, endX, endY
        FROM
          `mgroup` mg
        LEFT JOIN `descriptions` d ON mg.TS = d.TS
        LEFT JOIN pictures ON (pictures.catalog = mg.catalog AND pictures.desc_code1 = CONCAT(mg.MainGroup, SUBSTRING(mg.MajorGroup, 1, 1)) AND pictures.picture_file = mg.illustration)
        WHERE mg.catalog = :regionCode
        AND mg.Catalog_Num = :modelCode
        AND mg.Model = :modificationCode
        AND (mg.Complectation = :complectationCode OR mg.Complectation = '')
        AND d.catalog = :regionCode
        GROUP BY CONCAT(mg.MainGroup, SUBSTRING(mg.MajorGroup, 1, 1))
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();

        $aData = $query->fetchAll();


        $groups = array();
        foreach($aData as $item){

            $groups[$item['MainGroup']] = array(
                Constants::NAME     =>$item ['descEn'],
                Constants::OPTIONS => array('picture' => $item['illustration'],
                    Constants::X1 => floor($item['startX']),
                    Constants::X2 => floor($item['startX'] + $item['endX']),
                    Constants::Y1 => floor($item['startY']),
                    Constants::Y2 => floor($item['startY'] + $item['endY']),
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
        SELECT
          s.SubGroup as subGroup,
          s.Illustration as illustration,
          d.desc_en as descEn
        FROM
          `sgroup` s
        LEFT JOIN `descriptions` d ON d.TS = s.TS
        WHERE
          s.Catalog = :regionCode
        AND s.`Catalog Num` = :modelCode
        AND s.Model = :modificationCode
        AND s.MainGroup = :groupCode
        AND (s.Classicfication = '' OR s.Classicfication = :complectationCode)
        AND d.catalog = :regionCode
        GROUP BY s.SubGroup
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('groupCode',  $groupCode);

        $query->execute();

        $aData = $query->fetchAll();


           $subgroups = array();

        foreach($aData as $item){

            $subgroups[($item['subGroup'])] = array(
                Constants::NAME     => $item['descEn'].' ('.$item['subGroup'].')',
                Constants::OPTIONS => array('picture' => $item['illustration'])
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

           $sql = "
        SELECT
          bg.Illustration as illustration
        FROM
          `bgroup` bg
        WHERE bg.catalog = :regionCode
        AND bg.Catalog_Num = :modelCode
        AND bg.Model = :modificationCode
        AND bg.MainGroup = :groupCode
        AND bg.SubGroup = :subgroupCode
        AND (bg.Classicfication = :complectationCode OR bg.Classicfication = '')
        GROUP BY bg.Illustration
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('subgroupCode',  $subGroupCode);

        $query->execute();

           $aData = $query->fetchAll();



           $schemas = array();

           foreach($aData as $item)
           {

               $schemas[$item['illustration']] = array(

                   Constants::NAME => $item['illustration'],
                   Constants::OPTIONS => array()

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

           $sql = "
            SELECT
              pg.PNC as pnc,
              pg.PartNumber as partNumber,
              pg.StartDate as startDate,
              pg.EndDate as endDate,
              pg.Qty as quantity,
              d.desc_en

              FROM
              `part_catalog` pg
              LEFT JOIN pnc ON (pnc.pnc = pg.PNC AND pnc.catalog = pg.catalog)
              LEFT JOIN descriptions d ON (d.TS = pnc.desc_code AND d.catalog = pnc.catalog)
              WHERE
              pg.catalog = :regionCode
              AND (pg.Model = :modificationCode)
              AND pg.MainGroup = :groupCode
              AND pg.SubGroup = :subgroupCode
              AND (pg.Classification = :complectationCode OR pg.Classification = '')
              ORDER BY pg.PNC, pg.StartDate
            ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('modificationCode',  $modificationCode);
            $query->bindValue('complectationCode',  $complectationCode);
            $query->bindValue('groupCode',  $groupCode);
            $query->bindValue('subgroupCode',  $subGroupCode);

            $query->execute();


           $aPncs = $query->fetchAll();


           foreach ($aPncs as &$aPnc)
           {

               $sqlSchemaLabels = "
              SELECT
              p.startX,
              p.startY,
              p.endX,
              p.endY

              FROM `pictures` p
              WHERE p.catalog = :regionCode
              AND p.picture_file = :schemaCode
              AND p.desc_code1 = :pnc
              AND p.type = '1'
              ORDER BY p.desc_code1
           ";

               $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('regionCode',  $regionCode);
               $query->bindValue('schemaCode',  $schemaCode);
               $query->bindValue('pnc',  $aPnc['pnc']);


               $query->execute();

               $aPnc['clangjap'] = $query->fetchAll();

               unset ($aPnc);
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
                     /*  if ($value['PART_NAME'] != NULL)*/
                       $pncs[($value['pnc'])][Constants::OPTIONS][Constants::COORDS][($item1['startY'])] = array(
                           Constants::X2 => floor($item1['startX'] + $item1['endX']),
                           Constants::Y2 => $item1['startY'] + $item1['endY'],
                           Constants::X1 => floor($item1['startX']),
                           Constants::Y1 => $item1['startY']
                       );

                   }



               }
           }


           foreach ($aPncs as $item) {

               $pncs[$item['pnc']][Constants::NAME] = strtoupper($item['desc_en']);

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

        $sqlSchemaLabels = "
              SELECT
              p.desc_code1

              FROM `pictures` p
              WHERE p.catalog = :regionCode
              AND p.picture_file = :schemaCode
              AND p.type = '2'
              ORDER BY p.desc_code1
           ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('schemaCode',  $schemaCode);

        $query->execute();

        $aPncs = $query->fetchAll();

        $groups = array();

        foreach ($aPncs as &$aPnc)
        {

            $sqlSchemaLabels = "
              SELECT
              p.startX,
              p.startY,
              p.endX,
              p.endY

              FROM `pictures` p
              WHERE p.catalog = :regionCode
              AND p.picture_file = :schemaCode
              AND p.desc_code1 = :pnc
              AND p.type = '2'
              ORDER BY p.desc_code1
           ";

            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('regionCode',  $regionCode);
            $query->bindValue('schemaCode',  $schemaCode);
            $query->bindValue('pnc',  $aPnc['desc_code1']);


            $query->execute();

            $aPnc['clangjap'] = $query->fetchAll();

            unset ($aPnc);
        }

        foreach ($aPncs as $index=>$value) {

            if (!$value['clangjap'])
            {
                unset ($aPncs[$index]);

            }

            foreach ($value['clangjap'] as $item1)
            {
                $groups[$value['desc_code1']][Constants::OPTIONS][Constants::COORDS][$item1['startY']] = array(
                    Constants::X2 => floor($item1['startX'] + $item1['endX']),
                    Constants::Y2 => $item1['startY'] + $item1['endY'],
                    Constants::X1 => floor($item1['startX']),
                    Constants::Y1 => $item1['startY']
                );
            }
        }

        foreach ($aPncs as $item)
        {
            $groups[$item['desc_code1']][Constants::NAME] = explode(' ', $item['desc_code1'])[0].' / '.explode(' ', $item['desc_code1'])[1];
        }

        return $groups;
    }

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $pncCode, $options)
    {
        $sql = "
            SELECT
              pg.PNC as pnc,
              pg.PartNumber as partNumber,
              pg.StartDate as startDate,
              pg.EndDate as endDate,
              pg.Qty as quantity,
              d.desc_en as desc_en,
              dadd.desc_en as add_desc_en

              FROM
              `part_catalog` pg
              INNER JOIN pnc ON (pnc.pnc = pg.PNC AND pnc.catalog = pg.catalog)
              INNER JOIN descriptions d ON (d.TS = pnc.desc_code AND d.catalog = pnc.catalog)
              LEFT JOIN pbook ON (pbook.Partnumber = pg.PartNumber AND pbook.Catalog = pg.catalog)
              LEFT JOIN descriptions dadd ON (dadd.TS = pbook.PartSpec AND dadd.catalog = pnc.catalog)
              WHERE
              pg.catalog = :regionCode
              AND (pg.Model = :modificationCode)
              AND pg.MainGroup = :groupCode
              AND pg.SubGroup = :subgroupCode
              AND (pg.Classification = :complectationCode OR pg.Classification = '')
              AND pg.PNC = :pnc
              GROUP BY pg.PartNumber, pg.StartDate, pg.EndDate
              ORDER BY pg.PNC, pg.StartDate
            ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('subgroupCode',  $subGroupCode);
        $query->bindValue('pnc',  $pncCode);

        $query->execute();


        $aArticuls = $query->fetchAll();


        $articuls = array();

        foreach ($aArticuls as $item) {

				$articuls[$item['partNumber']] = array(
                Constants::NAME => $item['partNumber'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['quantity'],
                    Constants::START_DATE => $item['startDate'],
                    Constants::END_DATE => $item['endDate'],
                    'add_desc_en' => $item['add_desc_en']

                )
            );
            
        }


        return $articuls;
    }

    
} 