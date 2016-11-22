<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\FordBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\FordBundle\Components\FordConstants;

class FordCatalogModel extends CatalogModel{

    public function getRegions(){



        $regions = array();
        $regions['EU'] = array(Constants::NAME => 'Европа',
            Constants::OPTIONS => array());

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql  = "
        SELECT auto_name
        FROM feuc
        ORDER BY auto_name
        ";


        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item) {

            $mod = $item['auto_name'];
            if (stripos($item['auto_name'], ' ') !== false)
            {
                $mod = strtoupper(substr($item['auto_name'], 0 ,stripos($item['auto_name'], ' ')));
            }

            $models[urlencode($mod)] = array(Constants::NAME => $mod,
                Constants::OPTIONS => array());

        }


        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = rawurldecode($modelCode);
        $dir = 'bundles/catalogford/Images';
        $aFiles = scandir($dir, 1);
        foreach($aFiles as $index => $aFile){
            if (stripos($aFile, '.png') == false)
            {
                unset($aFiles[$index]);
            }

        }


        $sql  = "
        SELECT *
        FROM feuc
        /*WHERE SUBSTRING_INDEX(auto_name, ' ', 1) = :modelCode*/
        ORDER BY auto_name
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();

        foreach($aFiles as $aFile) {

            foreach($aData as &$item){
                if (stripos($aFile, $item['engine_type']) !== false)
                {
                    $item['engine_type'] = $aFile;
                }
                unset($item);
            }
        }

        foreach($aData as $item){

            {
                $modifications[$item['model_id']] = array(
                    Constants::NAME     => $item['auto_name'],
                    Constants::OPTIONS  => array('grafik' =>
                        $item['engine_type']));
            }
        }


        return $modifications;
    }

    /**
     Функция getComplectations() учитывает все положения руля и даты производства модификаций. Используется только в каталог_контроллере коммонБандла.
     */

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {$modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        SELECT familyLex.Description famDesc,
        attributeLex.Description attrDesc,
        attribute.FamilyId famId,
        attribute.AttributeId Id
        FROM catalogueattribute
        INNER JOIN attribute ON (attribute.AttributeId = catalogueattribute.AttributeId)
        INNER JOIN lexicon attributeLex ON (attribute.DescriptionId = attributeLex.DescriptionId and attributeLex.LanguageId IN ('1') AND attributeLex.SourceId = '18')
        INNER JOIN attributefamily ON (attributefamily.FamilyId = attribute.FamilyId)
        INNER JOIN lexicon familyLex ON (attributefamily.DescriptionId = familyLex.DescriptionId and familyLex.LanguageId IN ('1') AND familyLex.SourceId = '19')
        WHERE catalogueattribute.CatalogueId = :modificationCode
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $complectations = array();
        $complectationsPartIndexNoUnique = array();
        $complectationsPartIndex = array();
        $result = array();
        $aData = $query->fetchAll();

        foreach ($aData as &$item)
        {
            $item['famDesc'] = str_replace(' ', '_', $item['famDesc']);
            $complectationsPartIndexNoUnique[] = $item ['famId'];

        }
        $complectationsPartIndex = array_unique($complectationsPartIndexNoUnique);


        foreach ($aData as $item)
        {
            foreach ($complectationsPartIndex as $itemPartIndex)
            {

                if ($item['famId'] === $itemPartIndex)
                {

                    $result[($item['famDesc'])][$item['attrDesc']] = $item['attrDesc'];

                }
            }

        }



        foreach ($result as $index => $value) {



            $complectations[($index)] = array(
                Constants::NAME => $value,
                Constants::OPTIONS => array('option1'=>$value)
            );
        }


        return $complectations;

    }

    public function getComplectation ($complectationCode)
    {
        $complectation = array();
        $complectation[$complectationCode]= array(
            Constants::NAME => $complectationCode,
            Constants::OPTIONS => array()
        );

       return  $complectation;
    }


    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $submodificationCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));


         $sql = "
        SELECT attributeLex.Description famDesc, Code
        FROM cataloguecomponent, lexicon attributeLex
       where attributeLex.DescriptionId = cataloguecomponent.DescriptionId and attributeLex.LanguageId IN ('15') AND attributeLex.SourceId = '4'
        and AssemblyLevel = '2' and CatalogueId = :modificationCode



        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);

        $query->execute();
        $aData = $query->fetchAll();


        $aDataExplodeDesc = explode('|', base64_decode($complectationCode));
        $aDataExplodeCode = $this->getCodeByDescription($aDataExplodeDesc);
        $aDataExplodeConditions = $this->getConditions($aDataExplodeCode);

        $aDataGroup = array();
        $aDataLex = array();
        $aDataSourse = array();



            $sqlLex = "
        SELECT attribute.DescriptionId Id, cataloguecomponent.ComponentId
        FROM cataloguecomponent
        LEFT JOIN componentcp on (componentcp.ComponentId = cataloguecomponent.ComponentId)
        LEFT JOIN condition_ on (condition_.ConditionId = componentcp.ConditionId)
        LEFT JOIN attribute on (attribute.AttributeId = condition_.FilterCondition)
        where cataloguecomponent.AssemblyLevel = '1' and cataloguecomponent.CatalogueId IS NULL
        ";

            $query = $this->conn->prepare($sqlLex);
            $query->execute();
            $aDataLex = $query->fetchAll();


        foreach($aDataLex as $item)
        {
            $aDataGroup[$item['ComponentId']] = $item['ComponentId'];
        }

        foreach($aDataLex as $item)
        {
            foreach($aDataGroup as $value)
            {
               if  ($value == $item['ComponentId'])
               {
                   $aDataSourse[$item['ComponentId']][] = $item['Id'];
               }
            }
        }


  /*      $number = array(4,5,6);

        foreach($aDataExplodeCode as $index=>$value)
        {
            if (!in_array($index, $number))
            {
                unset ($aDataExplodeCode[$index]);
            }
        }*/


        foreach($aDataSourse as $index=>$value)
        {
            $n = 0;
            foreach($aDataExplodeCode as $item)

                {
                    if (in_array($item, $value))
                    {
                       $n ++;
                    }
                }
                if ($n == 0)
                {
                    unset ($aDataSourse[$index]);
                }

            }




        $groups = array();


        foreach($aData as $item){
            $groups[$item['Code']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['famDesc']),'utf8'),
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

        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));
        $sql = "
        SELECT attributeLex.Description famDesc, Code, attachmentdata.URL URL
        FROM cataloguecomponent
        INNER JOIN lexicon attributeLex ON (cataloguecomponent.DescriptionId = attributeLex.DescriptionId and attributeLex.LanguageId IN ('15') AND attributeLex.SourceId = '4')
        LEFT JOIN  catalogueshortcuttocomponent ON (cataloguecomponent.ComponentId = catalogueshortcuttocomponent.ComponentId)
        LEFT JOIN  catalogueshortcut ON (catalogueshortcuttocomponent.ShortcutId = catalogueshortcut.ShortcutId)
        LEFT JOIN attachmentdata ON (catalogueshortcut.AttachmentId = attachmentdata.AttachmentId)
        WHERE AssemblyLevel = '3' and cataloguecomponent.CatalogueId = :modificationCode and Code LIKE :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', '%'.$groupCode.'%');
        $query->execute();
        $aData = $query->fetchAll();

        $subgroups = array();


        foreach($aData as $item){
            $subgroups[$item['Code']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['famDesc']),'utf8'),
                Constants::OPTIONS  => array()
            );
        }


        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        SELECT attributeLex.Description famDesc, Code1.Code schemaCode, Code2.Code pncCode, attachmentdata.URL URL, attachmentdata.MIME MIME
        FROM cataloguecomponent Code1
        INNER JOIN lexicon attributeLex ON (Code1.DescriptionId = attributeLex.DescriptionId and attributeLex.LanguageId IN ('15') AND attributeLex.SourceId = '4')
        INNER JOIN  cataloguecomponent Code2 ON (Code1.ComponentId = Code2.ParentComponentId and Code2.AssemblyLevel = 5)
        INNER JOIN  hotspot ON (Code2.HotspotKey = hotspot.HotspotKey and Code2.ParentComponentId = hotspot.ComponentId)
        INNER JOIN attachmentdata ON (hotspot.AttachmentId = attachmentdata.AttachmentId)
        WHERE Code1.AssemblyLevel = '4' and Code1.CatalogueId = :modificationCode and Code1.Code LIKE :subGroupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', '%'.$subGroupCode.'%');
        $query->execute();
        $aData = $query->fetchAll();

        $schemas = array();
        foreach($aData as $item){
            $schemas[$item['schemaCode']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['famDesc']),'utf8'),
                Constants::OPTIONS  => array('grafik' =>
                    substr($item['URL'], strpos($item['URL'], 'cgm')+4, strlen($item['URL'])).'.'.str_replace('cgm', 'jpg',$item['MIME']))
            );
        }

        return $schemas;
    }

    public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {




        $schema = array();

			
		            $schema[urldecode($schemaCode)] = array(
                    Constants::NAME => $schemaCode,
                        Constants::OPTIONS => array()
                );

        return $schema;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
    {
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));


        $sqlPnc = "
        SELECT attributeLex.Description famDesc, cataloguecomponent.Code pncCode, HotspotKey
        FROM cataloguecomponent
        INNER JOIN lexicon attributeLex ON (cataloguecomponent.DescriptionId = attributeLex.DescriptionId and attributeLex.LanguageId IN ('15') AND attributeLex.SourceId = '4')
        WHERE cataloguecomponent.AssemblyLevel = 5 and cataloguecomponent.CatalogueId = :modificationCode and cataloguecomponent.Code LIKE :schemaCode
        ";

    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('schemaCode', '%'.$schemaCode.'%');
        $query->execute();

        $aPncs = $query->fetchAll();



        foreach ($aPncs as &$aPnc)
        {
            $aPnc['coords'] = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
        }


        $pncs = array();


        foreach ($aPncs as $item) {

            $pncs[$item['HotspotKey']][Constants::OPTIONS][Constants::COORDS][$item['HotspotKey']] = array(
                Constants::X1 => 0,
                Constants::Y1 => 0,
                Constants::X2 => 0,
                Constants::Y2 => 0);

            $pncs[$item['HotspotKey']][Constants::NAME] = mb_strtoupper(iconv('cp1251', 'utf8', $item['famDesc']), 'utf8');



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
";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('complectationCode', substr($complectationCode,1,strlen($complectationCode)-1));
        $query->bindValue('subGroupId', $options['GrId']);
        $query->bindValue('pncCode', $pncCode);
        $query->bindValue('role',  substr($complectationCode, 0, 1));
        $query->bindValue('korobka',  substr($complectationCode, 1, 1));
        $query->bindValue('dataCar',  substr($complectationCode, 2, 8));

        $query->execute();

        $aArticuls = $query->fetchAll();



        $nach = array();




        foreach ($aArticuls as $index => $value)
        {

            if (($value['Bildnummer'] != '--') && ((iconv('cp1251', 'utf8',trim($value['Komm_Benennung'])) ==='только в комбинации с') ||
                    (iconv('cp1251', 'utf8',trim($value['Komm_Benennung'])) ==='подходит только при') || $value['KommNach']!='0'))
            {
                $nach[]=$value['KommNach'];
            }

        }


        foreach ($aArticuls as $index => $value)
        {

            if (($value['Bildnummer'] == '--') && (count($nach) == 0))
            {
                unset ($aArticuls[$index]);
            }

        }

        $aPred = array();
        $aCurrent = array();
        $aNext = array();
        $nachIndex = 0;


        foreach ($aArticuls as $index => $value)
        {

            if (($value['Bildnummer'] != '--') && ((iconv('cp1251', 'utf8',trim($value['Komm_Benennung'])) ==='только в комбинации с') ||
            (iconv('cp1251', 'utf8',trim($value['Komm_Benennung'])) ==='подходит только при') || $value['KommNach']!='0'))
            {

                $nachIndex = $index;
                $nachPos = $value['Pos'];

            }

        }

       $min = 10;
        foreach ($aArticuls as $index => $value)
        {
                if (($value['Bildnummer'] == '--') && ($value['Pos'] > $aArticuls[$nachIndex]['Pos'])
                    && (($value['Pos'] - $aArticuls[$nachIndex]['Pos']) < $min)) {
                    $min =  $value['Pos'] - $aArticuls[$nachIndex]['Pos'];
                    $minIndex = $index;
                    $minPos = $value['Pos'];

                }
        }

        $ba = array();
        foreach ($aArticuls as $index => $value)
        {
            if (($value['Bildnummer'] == '--') && (($index==$minIndex)))
            {

                $pos = $value['Pos'];

            }
        }


     foreach ($aArticuls as $index => $value)
        {

            if (($value['Bildnummer'] == '--') && ((($value['Pos']-$pos) > 2) || ($value['Pos']-$pos) < 0)) {

                unset ($aArticuls[$index]);
            }
        }




        $articuls = array();
        $kommnach = array();
        $kommanach = array();

        $kommvor = array();
        $kommavor = array();

        foreach ($aArticuls as $item)
        {
            if ($item ['KomVz']=='+') {$string[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']] = ' применяется';}
            else if ($item ['KomVz']=='-') {$string[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']] = ' не применяется';}
            else {$string[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']] = NULL;}
            $a = $string[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']];

            $kommnach[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']][$item['Komnach']] = iconv('cp1251', 'utf8', $item ['Komm_Benennung']);
            (ksort($kommnach[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']]));
            $kommanach[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']] = implode (' ', $kommnach[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']]);

            $kommvor[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']][$item['Komvor']] = iconv('cp1251', 'utf8', $item ['Komm_Vor']).' '.$item ['KomCode'];
            (ksort($kommvor[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']]));
            $kommavor[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']] = implode (' ', $kommvor[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']]);

        }

      
        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']] = array(
                Constants::NAME => mb_strtoupper(iconv('cp1251', 'utf8', $item ['Teil_Benennung']), 'utf8'),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['Menge'],
                    Constants::START_DATE => ($item['Einsatz'] != '(null)')?$item['Einsatz']:99999999,
                    Constants::END_DATE => ($item['Auslauf'] != '(null)')?$item['Auslauf']:99999999,
                    'dopinf' => ($item['Teil_Zusatz'] != '(null)')?$item['Teil_Zusatz']:'',
                    'kommanach' => $kommanach[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']],
                    'kommavor' => ($item['Bildnummer'] != '--')?($kommavor[$item['Teil_HG'].$item['Teil_UG'].$item['Teil_Sachnummer']].($a?$a:NULL)):' ',




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

    public function getCodeByDescription($Description)
    {
        $Code = array();

        foreach ($Description as $item)
        {
            $sqlCode = "
        SELECT DescriptionId
        FROM lexicon
        where lexicon.Description = :item
        ";

            $query = $this->conn->prepare($sqlCode);
            $query->bindValue('item', $item);
            $query->execute();

            $Code[] = $query->fetchColumn(0);

        }


        return $Code;

    }

    public function getConditions($Description)
    {
        $Code = array();

        foreach ($Description as $item)
        {
            $sqlCode = "
        SELECT AttributeId
        FROM attribute
        where DescriptionId = :item
        ";

            $query = $this->conn->prepare($sqlCode);
            $query->bindValue('item', $item);
            $query->execute();

            $Code[] = $query->fetchColumn(0);

        }


        return $Code;

    }

    
} 