<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\SaabBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\SaabBundle\Components\SaabConstants;

class SaabCatalogModel extends CatalogModel{

    public function getRegions(){


        $aData = array('EU'=>'EU');

        $regions = array();


            $regions[$aData['EU']] = array(Constants::NAME=>$aData['EU'], Constants::OPTIONS=>array());


        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql  = "
        SELECT TYPE_OF_CAR, MODEL_NO
        FROM model
        WHERE TYPE_OF_CAR NOT LIKE 'EXCH.'
        ORDER BY TYPE_OF_CAR
        ";


        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item) {

            $models[$item['MODEL_NO']] = array(Constants::NAME => ($item['TYPE_OF_CAR']),
                Constants::OPTIONS => array());

        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sql = "
        SELECT nYear, Code
        FROM vin_year, model
        WHERE MODEL_NO = :modelCode
        AND nYear >= FROM_MODEL_YEAR
        AND nYear <= TO_MODEL_YEAR
        ORDER BY nYear
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['nYear']] = array(
                Constants::NAME     => $item['nYear'].' ('.$item['Code'].')',
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
                    FROM saablex
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

         $sql = "
        SELECT
        GROUP_NO, DESCRIPTION_TEXT
        FROM saab.group, descr
        WHERE CATALOGUE_NO = :modelCode and GROUP_DESC = DESCRIPTION_NO AND LANGUAGE_CODE = 16
        ORDER BY ABS(GROUP_NO)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();
        $aData = $query->fetchAll();

        $groups = array();


        foreach($aData as $item){
            $groups[$item['GROUP_NO']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['DESCRIPTION_TEXT']),'utf8'),
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
        SELECT
DESCRIPTION_TEXT, HEAD_LINE_1, SECTION_NO
FROM saab.section, descr
WHERE  :modificationCode BETWEEN FROM_YEAR AND TO_YEAR AND CATALOGUE_NO = :modelCode AND GROUP_NO = :groupCode AND HEAD_LINE_1 = DESCRIPTION_NO AND LANGUAGE_CODE = 16
ORDER BY ABS (SECTION_NO)
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();


        $subgroups = array();
        foreach($aData as $item){

            $subgroups[$item['HEAD_LINE_1']] = array(

                Constants::NAME => mb_strtoupper(iconv('cp1251', 'utf8', $item ['DESCRIPTION_TEXT']),'utf8'),
                Constants::OPTIONS => array()
            );

        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $sql = "
        SELECT
        dr.DESCRIPTION_TEXT dr,
        br.DESCRIPTION_TEXT br,
        HEAD_LINE_2,
        saab.section.SECTION_NO Sec,
        IMAGE_NO,
        FROM_YEAR,
        TO_YEAR,
        FOOTNOTE_TEXT,
        saab.section.CATALOGUE_NO catsec,
        saab.section.GROUP_NO groupsec,
        saab.section_footnote.CATALOGUE_NO catsecfoot,
        saab.section_footnote.FOOTNOTE_NO footsecfoot
        FROM saab.section
        LEFT JOIN descr dr ON (HEAD_LINE_2 = dr.DESCRIPTION_NO AND dr.LANGUAGE_CODE = 16)
        LEFT JOIN descr br ON (HEAD_LINE_3 = br.DESCRIPTION_NO AND br.LANGUAGE_CODE = 16)
        LEFT JOIN section_footnote ON (saab.section.CATALOGUE_NO = saab.section_footnote.CATALOGUE_NO
         AND saab.section.GROUP_NO = section_footnote.GROUP_NO and saab.section.SECTION_NO = section_footnote.SECTION_NO)
        LEFT JOIN foodnote ON (saab.section_footnote.CATALOGUE_NO = foodnote.CATALOGUE_NO and foodnote.LANGUAGE_CODE = 16 and saab.section_footnote.FOOTNOTE_NO = foodnote.FOOTNOTE_NO)
        WHERE  :modificationCode BETWEEN FROM_YEAR AND TO_YEAR AND HEAD_LINE_1 = :subGroupCode and saab.section.GROUP_NO = :groupCode and saab.section.CATALOGUE_NO = :modelCode
        ORDER BY ABS (Sec)
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('subGroupCode',  $subGroupCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        foreach($aData as &$item)
        {

                $schemas[$item['IMAGE_NO']] = array(
                    Constants::NAME => $item['Sec'] . '. ' . mb_strtoupper(iconv('cp1251', 'utf8', $item ['dr']), 'utf8').' ('. $item['FROM_YEAR'].'-'.$item['TO_YEAR'].')'.
                        ($item['br']?'- '.iconv('cp1251', 'utf8', $item ['br']):''),
                    Constants::OPTIONS => array('option1' => iconv('cp1251', 'utf8', $item['FOOTNOTE_TEXT']),
                                                'Sec' => $item['Sec'])
                );

        }


        return $schemas;
    }

    public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {
        $sql = "
        SELECT
        IMAGE_NO
        FROM saab.section
        WHERE  :modificationCode BETWEEN FROM_YEAR AND TO_YEAR AND CATALOGUE_NO = :modelCode AND GROUP_NO = :groupCode AND SECTION_NO = :schemaCode
        ORDER BY ABS (SECTION_NO)
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('schemaCode',  $schemaCode);
        $query->execute();

        $aData = $query->fetchAll(); var_dump($aData); die;

        $schema = array();

			
		            $schema[$schemaCode] = array(
                    Constants::NAME => $schemaCode,
                        Constants::OPTIONS => array()
                );

        return $schema;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
    {


        $sqlPnc = "
select textlines.POSITION pos, DESCRIPTION_TEXT
from textlines, descr
where CATALOGUE_NO = :modelCode
and GROUP_NO = :groupCode
and SECTION_NO = :schemaCode
AND textlines.DESCRIPTION_NO = descr.DESCRIPTION_NO AND descr.LANGUAGE_CODE = 16
ORDER BY ABS (pos)
";

    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('schemaCode',  $options['Sec']);
        $query->execute();

        $aPncs = $query->fetchAll();


        foreach ($aPncs as &$aPnc)
        {
            $aPnc['coords'] = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
        }


        $pncs = array();


        foreach ($aPncs as $item) {

            $pncs[$item['pos']][Constants::OPTIONS][Constants::COORDS][$item['pos']] = array(
                Constants::X1 => 0,
                Constants::Y1 => 0,
                Constants::X2 => 0,
                Constants::Y2 => 0);
         	
				$pncs[$item['pos']][Constants::NAME] = mb_strtoupper(iconv('cp1251', 'utf8', $item['DESCRIPTION_TEXT']), 'utf8');
			
			
           
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
      /*  $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));


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
        $sqlPnc = "
        SELECT textlines.PART_NO art, DESCRIPTION_TEXT, FROM_YEAR, TO_YEAR, MODEL_YEAR, QUANTITY, textlines.LINE_NO li, FOOTNOTE_TEXT, SEQUENCE_NO seq, saab.replace.REPLACE_PART rep
        FROM textlines
        LEFT JOIN saab.replace ON (textlines.PART_NO = saab.replace.PART_NO)
        LEFT JOIN descr ON (textlines.DESCRIPTION_NO = descr.DESCRIPTION_NO AND descr.LANGUAGE_CODE = 16)
        LEFT JOIN textline_year ON (textlines.CATALOGUE_NO = textline_year.CATALOGUE_NO AND textlines.GROUP_NO = textline_year.GROUP_NO
        AND textlines.SECTION_NO = textline_year.SECTION_NO AND textlines.LINE_NO = textline_year.LINE_NO)

        LEFT JOIN textline_footnote ON (textlines.CATALOGUE_NO = textline_footnote.CATALOGUE_NO
        AND textlines.GROUP_NO = textline_footnote.GROUP_NO AND textlines.SECTION_NO = textline_footnote.SECTION_NO AND textlines.LINE_NO = textline_footnote.LINE_NO)
        LEFT JOIN foodnote ON (textline_footnote.CATALOGUE_NO = foodnote.CATALOGUE_NO AND foodnote.LANGUAGE_CODE = 16 AND textline_footnote.FOOTNOTE_NO = foodnote.FOOTNOTE_NO)

        WHERE textlines.POSITION = :pncCode
        AND textlines.CATALOGUE_NO = :modelCode
        AND textlines.GROUP_NO = :groupCode
        AND textlines.SECTION_NO = :schemaCode
      ";


        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('pncCode',  $pncCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('schemaCode',  $options['Sec']);
        $query->execute();

        $aArticuls = $query->fetchAll();


        $articuls = array();
      
        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['art'].$item['li'].$item['seq']] = array(
                Constants::NAME => iconv('cp1251', 'utf8', $item ['DESCRIPTION_TEXT']),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['QUANTITY'],
                    Constants::START_DATE => ($item['FROM_YEAR'] != 'NULL')?$item['FROM_YEAR']:$item['MODEL_YEAR'],
                    Constants::END_DATE => ($item['TO_YEAR'] != 'NULL')?$item['TO_YEAR']:'',
                    'articul' => $item['art'],
                    'dopinf' => iconv('cp1251', 'utf8', $item['FOOTNOTE_TEXT']),
                    'replace' => $item['rep']

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