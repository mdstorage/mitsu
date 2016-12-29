<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\SubaruBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\SubaruBundle\Components\SubaruConstants;

class SubaruArticulModel extends SubaruCatalogModel{

    public function getArticulRegions($articulCode){

        $sql = "
        SELECT catalog, sub_wheel
        FROM part_catalog
        WHERE part_number = :articulCode
        GROUP BY catalog";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[] = $item['catalog'].'_'.$item['sub_wheel'];
        }

        return $regions;

    }

    public function getArticulModels($articul, $regionCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

    	$sql = "
        SELECT model_code
        FROM part_catalog
        WHERE part_number =:articul
        AND catalog = :regionCode
        AND sub_wheel = :wheel
        GROUP BY model_code
        ";
       		
        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('wheel', $wheel);
        $query->execute();


        $aData = $query->fetchAll();

        $models = $this->array_column($aData, 'model_code'); 

        return $models;
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $table = 'model_changes_jp';
            $lang = 'jp';
        }
        else
        {
            $table = 'model_changes';
            $lang = 'en';
        }

        $sql = "
        SELECT desc_$lang, change_abb, sdate, edate
        FROM $table
        WHERE model_code = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();
        

        $modifications = array();
        foreach($aData as $item){
            $modifications[] = $item['change_abb'].$item['desc_'.$lang];
        }

        return $modifications;
    }
    
    public function getArticulComplectations($articulCode, $regionCode, $modelCode, $modificationCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        $sqlArticul = "
        SELECT DISTINCT body_desc.f1
        FROM part_catalog
        INNER JOIN body_desc ON (body_desc.catalog = part_catalog.catalog
        AND body_desc.model_code = part_catalog.model_code)
        WHERE part_catalog.part_number = :articulCode
        AND part_catalog.catalog = :regionCode
        AND part_catalog.sub_wheel = :wheel
        ";



        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articulCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('wheel', $wheel);

        $query->execute();

        $aComplectations = $query->fetchAll();


        $complectations = array();

         foreach  ($aComplectations as $item)
         {
             $complectations[] = $item['f1'];
		 }

        return $complectations;



    }

    public function getArticulModelRestrictions($articulCode, $regionCode, $modelCode, $modificationCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        $sqlArticul = "
        SELECT part_catalog.model_restrictions
        FROM part_catalog
        WHERE part_catalog.part_number = :articulCode
        AND part_catalog.model_code = :modelCode
        AND part_catalog.catalog = :regionCode
        AND part_catalog.sub_wheel = :wheel
        ";



        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articulCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('wheel', $wheel);

        $query->execute();

        $aRestrictions = $query->fetchAll();


        $restrictions = array();

        foreach  ($aRestrictions as $item)
        {
            $restrictions[] = $item['model_restrictions'];
        }

        return implode(' +', $restrictions);

    }

    public function getarticulComplectationsRestrictions($modelRestrictions, $complectationsWithoutRestrictions)
    {

        foreach ($complectationsWithoutRestrictions as $index => &$value) {
            $ct = 0;
            $schemaOptions = $this->multiexplode(array(' +'), $modelRestrictions);

            $schemaOptions = (array_reverse(array_unique($schemaOptions)));

            $plus = array();


            foreach ($schemaOptions as $schemaOptionsOpt) {


                $item = (strpos($schemaOptionsOpt, '<') !== false) ? substr_replace($schemaOptionsOpt,'', strpos($schemaOptionsOpt, '<'), strripos($schemaOptionsOpt, '>')+1) : $schemaOptionsOpt;


                if (strpos($item, ".")) {
                    $plus = array_merge($plus, explode('.', $item));
                    $countOfPluses = 0;
                    $pluses = array();



                    foreach ($plus as $index1 => $plusOne){

                        if (strpos($plusOne, '+')){

                            unset($plus[$index1]);
                            $plusOne = trim($plusOne, ('*()'));
                            $pluses = explode('+', $plusOne);

                            $countOfPluses = count($pluses) - 1;



                            $plus = array_merge($plus, $pluses);

                        }
                    }


                } else {

                    $plus = array_merge($plus, array($item));

                }


            }
            $array_flip = array_flip(array_unique($plus));

            $value['name'] = (count(array_intersect_key($value['name'], $array_flip)) != 0)?array_intersect_key($value['name'], $array_flip):$value['name'];


        }

        return $complectationsWithoutRestrictions;
    }
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlArticul = "

        SELECT part_catalog.pri_group
        FROM part_catalog
        INNER JOIN body_desc ON body_desc.catalog = part_catalog.catalog
        AND body_desc.model_code = part_catalog.model_code AND body_desc.f1 = :complectationCode
        WHERE part_catalog.part_number = :articulCode
        AND part_catalog.catalog = :regionCode
        AND part_catalog.sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('wheel', $wheel);
        $query->bindValue('complectationCode', $complectationCode);

        $query->execute();
        
        $aArticulDesc = $query->fetchAll();

        $groups = array();

        $groups = $this->array_column($aArticulDesc, 'pri_group');

        return $groups;
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlArticul = "
        SELECT part_catalog.sec_group
        FROM part_catalog
        INNER JOIN body_desc ON (body_desc.catalog = part_catalog.catalog
        AND body_desc.model_code = part_catalog.model_code AND body_desc.f1 = :complectationCode)
        WHERE part_catalog.part_number = :articulCode
        AND part_catalog.catalog = :regionCode
        AND part_catalog.model_code = :modelCode
        AND part_catalog.pri_group = :groupCode
        AND part_catalog.sub_wheel = :wheel
        GROUP BY sec_group
        ";

        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('wheel', $wheel);
        $query->bindValue('complectationCode', $complectationCode);
        $query->execute();
        
        $aArticulDesc = $query->fetchAll();
    	$subgroups = array();

        $subgroups = $this->array_column($aArticulDesc, 'sec_group');

        return $subgroups;
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $part_images = 'part_images_jp';
            $labels = 'labels_jp';
            $lang = 'jp';
        }
        else
        {
            $part_images = 'part_images';
            $labels = 'labels';
            $lang = 'en';
        }



        $sqlArticul = "
        SELECT pi.image_file, pi.desc_$lang
        FROM part_catalog
        INNER JOIN labels la ON (la.part_code = part_catalog.part_code AND la.sec_group = part_catalog.sec_group
        AND la.model_code = part_catalog.model_code AND la.sub_dir = part_catalog.sub_dir AND la.sub_wheel = part_catalog.sub_wheel AND la.catalog = part_catalog.catalog)
        INNER JOIN part_images pi ON (pi.sec_group = part_catalog.sec_group AND pi.catalog = part_catalog.catalog
        AND pi.model_code = part_catalog.model_code AND pi.page = la.page AND pi.sub_dir = part_catalog.sub_dir AND pi.sub_wheel = part_catalog.sub_wheel)
        WHERE part_catalog.part_number = :articulCode
        AND part_catalog.catalog = :regionCode
        AND part_catalog.model_code = :modelCode
        AND part_catalog.pri_group = :groupCode
        AND part_catalog.sec_group = :subGroupCode
        AND part_catalog.sub_wheel = :wheel
        ";

        
        $query = $this->conn->prepare($sqlArticul);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('wheel', $wheel);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
       
        foreach  ($aData as $item)
        {
        /*	if ((substr_count($item['desc_en'],'MY') > 0) && (substr_count($item['desc_en'], substr($modificationCode, 1, 5))!=0) || (substr_count($item['desc_en'],'MY') == false))*/
			$schemas[] = $item['image_file'];
		}

               

        return $schemas;
    }

	 
    
     public function getArticulPncs($articul, $regionCode, $modelCode, $groupCode, $subGroupCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));
        
        $sqlPnc = "
        SELECT part_code
        FROM part_catalog
        WHERE catalog = :regionCode
        AND part_number = :articul 
        AND model_code = :modelCode
        AND pri_group = :groupCode
        AND sec_group = :subGroupCode
        GROUP BY part_code
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('articul', $articul);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $pncs = array();
        $pncs = $this->array_column($query->fetchAll(), 'part_code');

        return $pncs; 
    }
} 