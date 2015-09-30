<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\HuyndaiBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\HuyndaiBundle\Components\HuyndaiConstants;

class HuyndaiCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT data_regions
        FROM hywc
        GROUP BY data_regions
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        foreach($aData as $item)
        {

            $pieces = explode("|", $item['data_regions']);
            foreach($pieces as $item1)
            {
                if($item1 != ''){$reg[] = $item1;}
            }

        }

        $regions = array();
        foreach(array_unique($reg) as $item)
        {
            $regions[trim($item)] = array(Constants::NAME=>$item, Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT *
        FROM hywc
        WHERE data_regions LIKE :regionCode
        ORDER BY family
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->execute();

        $aData = $query->fetchAll();
              

        $models = array();
        foreach($aData as $item){ 
        	 
            $models[($item['family'])] = array(Constants::NAME=>strtoupper($item['family']),
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sql = "
        SELECT *
        FROM hywc
        WHERE data_regions LIKE :regionCode
        AND family = :modelCode
        ORDER BY catalog_name
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['catalog_code'].'_'.$item['catalog_folder']] = array(
                Constants::NAME     => $item['catalog_name'],
                Constants::OPTIONS  => array('option1'=>strtolower($item['catalog_code'])));

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
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
         $sql = "
        SELECT *
        FROM cats_maj
        WHERE CATALOG_NAME = :catCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catCode', $catCode);
        $query->execute();
        $aData = $query->fetchAll();

        $groups = array();

        foreach ($aData as &$item2)
        {
                    $item2['part_index'] = $this->getDesc($item2['part_index'], 'EN');
        }

        foreach($aData as $item){
            $groups[$item['part']] = array(
                Constants::NAME     => $item ['part_index'],
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
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));


    	$sql = "
        SELECT sector_name, sector_lex_code, sector_id, sector_id_code
        FROM cats_map
        WHERE catalog_name =:catCode
        AND part = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catCode',  $catCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subgroups = array();
        foreach($aData as $item){
            $subgroups[($item['sector_name'])] = array(
                Constants::NAME => '('.$item['sector_name'].') '.$this->getDesc($item['sector_lex_code'], 'RU'),
                Constants::OPTIONS => array()
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $sql = "
        SELECT sector_id
        FROM cats_map
        WHERE catalog_name =:catCode
        AND sector_name = :subGroupCode
        AND part = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catCode',  $catCode);
        $query->bindValue('subGroupCode',  $subGroupCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        foreach($aData as $item)
        {

		            $schemas[$item['sector_id']] = array(
                    Constants::NAME => $catCode,
                    Constants::OPTIONS => array(Constants::CD => $item['sector_id'])
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

        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $sqlPnc = "
        SELECT *
        FROM cats_table
        WHERE catalog_code =:catCode
        	AND compl_name = :schemaCode
        ";

    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aPncs = $query->fetchAll();
    	
    	foreach ($aPncs as &$aPnc)
    	{
    		
    	$sqlSchemaLabels = "
        SELECT x1, y1, x2, y2
        FROM cats_coord
        WHERE catalog_code =:catCode
          AND compl_name =:schemaCode
          AND name =:pnc_code
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('catCode', $catCode);
            $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('pnc_code', $aPnc['detail_pnc']);
        $query->execute();
        
        $aPnc['clangjap'] = $query->fetchAll();


            $sqlPncName = "
        SELECT lex_code
        FROM pnclex
        WHERE pnc_code =:pnc_code
        ";

            $query = $this->conn->prepare($sqlPncName);
            $query->bindValue('pnc_code', $aPnc['detail_pnc']);
            $query->execute();
            $aData = $query->fetch();

            $aPnc['name'] = $aData['lex_code'];


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
            	$pncs[$value['detail_pnc']][Constants::OPTIONS][Constants::COORDS][$item1['x1']] = array(
                    Constants::X1 => floor(($item1['x1'])),
                    Constants::Y1 => $item1['y1'],
                    Constants::X2 => $item1['x2'],
                    Constants::Y2 => $item1['y2']);
            	
            	}
            
            
                
            }
        }

        foreach ($aPncs as $item) {
         	
         	
				$pncs[$item['detail_pnc']][Constants::NAME] = $this->getDesc($item['name'], 'RU');
			
			
           
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
       
        $groups = array();
        return $groups;
    }

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode, $options)
    {

        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $ghg = $this->getComplectations($regionCode, $modelCode, $modificationCode);/*print_r($ghg[$complectationCode]['options']['option2']); die;*/
        $complectationOptions = $ghg[$complectationCode]['options']['option2'];

        $sqlPnc = "
        SELECT *
        FROM cats_table
        WHERE catalog_code =:catCode
            AND detail_pnc = :pncCode
        	AND compl_name = :schemaCode
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('pncCode', $pncCode);
        $query->bindValue('schemaCode', $options['cd']);
        $query->execute();

        $aArticuls = $query->fetchAll();

        foreach ($aArticuls as $index => $value) {

            $articulOptions = explode('|', str_replace(';', '', $value['model_options']));

            foreach ($articulOptions as $index1 => $value1) {
                if (($value1 == '') || ($index1 > (count($complectationOptions)-1))) {
                    unset ($articulOptions[$index1]);
                }
            }

            if (count($articulOptions) != count(array_intersect_assoc($articulOptions, $complectationOptions)))
            {
                unset ($aArticuls[$index]);
            }
        }



        $articuls = array();
      
        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['detail_code']] = array(
                Constants::NAME => $this->getDesc($item['detail_lex_code'], 'RU'),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['quantity_details'],
                    'option1' => $item['start_data'],
                    'option2' => $item['end_data'],
                    'option3' => $item['replace_code'],
                )
            );
            
        }

        return $articuls;
    }

    private function getDesc($itemCode, $language)
    {

                $sql = "
                    SELECT lex_name
                    FROM hywlex
                    WHERE lex_code = :itemCode
                    AND lang = :language
                    ";

                $query = $this->conn->prepare($sql);
                $query->bindValue('itemCode', $itemCode);
                $query->bindValue('language', $language);
                $query->execute();
                $sData = $query->fetch();

        $sDesc = $itemCode;
                if ($sData)
                {
                    $sDesc = $sData['lex_name'];
                }
        if ($sDesc == $itemCode){ $sDesc = $this->getDesc($sDesc, 'EN');}

        return mb_strtoupper($sDesc, 'UTF-8');
    }

    
} 