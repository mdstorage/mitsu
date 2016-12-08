<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\FordBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\FordBundle\Components\FordConstants;
use Symfony\Component\HttpFoundation\Request;

class FordArticulModel extends FordCatalogModel{

    public function getArticulRegions($articulCode)
    {
        $aData = array('region' => 'EU');

        $regions = array();
                  
        foreach($aData as $item)
        {
            $regions[] = $item;

		}
        return array_unique($regions);

    }

    public function getArticulModels($articul, $regionCode)
    {
        $sql = "
        SELECT feuc.auto_name
        FROM mcpart1 mp
        INNER JOIN coordinates_names cn ON (cn.num_index = mp.pict_index)
        INNER JOIN feuc ON (feuc.model_id = cn.model_id)
        WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        $models = array();

        foreach($aData as $item) {

            $mod = $item['auto_name'];
            if (stripos($item['auto_name'], ' ') !== false && $item['auto_name'] != 'Fluids and Maintenance Products')
            {
                $mod = strtoupper(substr($item['auto_name'], 0 ,stripos($item['auto_name'], ' ')));
            }

            $models[] = urlencode($mod);

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $sql = "
        SELECT f.*
        FROM mcpart1 mp
        INNER JOIN coordinates_names cn ON (cn.num_index = mp.pict_index)
        INNER JOIN feuc f ON (f.model_id = cn.model_id AND SUBSTRING_INDEX(f.auto_name, ' ', 1) = :modelCode)
        WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', urldecode($modelCode));
        $query->bindValue('articulCode', $articul);
        $query->execute();

        $aData = $query->fetchAll();
        $modifications = array();


        foreach($aData as $item)
        {
            $modifications[] = $item['model_id'].'_'.$item['auto_code'].'_'.$item['engine_type'];

        }

        return array_unique($modifications);

    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
        $complectations = $this->getComplectations($regionCode, $modelCode, $modificationCode);

        $modificationCode = explode('_', $modificationCode);

        $sql = "
        SELECT cdi.*
        FROM mcpart1 mp
        INNER JOIN coordinates_names cn ON (cn.num_index = mp.pict_index AND cn.model_id = :model_id AND cn.group_detail_sign = 2)
        INNER JOIN feu.coord_detail_info cdi ON (cn.num_model_group = cdi.num_model_group AND cdi.model_id = :model_id)
        WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('model_id', $modificationCode[0]);

        $query->execute();
        $aData = $query->fetchAll();
        $aDataLex = array();

        foreach($aData as $index => &$item) {
            $alexs = explode(' ', $item['lex_filter']);

            foreach ($alexs as $alex) {

                $sqllex = "
                SELECT lex_name
                FROM lex
                WHERE lex.index_lex = :alex
                AND lex.lang = 'EN'
                ";
                $query = $this->conn->prepare($sqllex);
                $query->bindValue('alex', $alex);
                $query->execute();
                $aDataLex[] = $query->fetchColumn(0);
            }
        }

        foreach ($complectations as $indexCo => &$complectation)
        {
            if (count(array_intersect($complectation['name'], array_unique($aDataLex))) > 0 & count(array_intersect(array_unique($aDataLex), array(''))) == null)
            {
                $complectation['name'] = array_intersect($complectation['name'], $aDataLex);
                $complectation['options']['option1'] = array_intersect($complectation['name'], $aDataLex);
            }
        }

        return $complectations;
    }

    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $modificationCode = explode('_', $modificationCode);

        $sql = "
        SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) number,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 20), '!', -1) model_3gr
        FROM feu.mcpart1 mp
        LEFT JOIN coordinates_names cn ON (cn.num_index = mp.pict_index AND cn.model_id = :model_id AND cn.group_detail_sign = 2)
        LEFT JOIN feu.coord_detail_info cdi ON (cn.num_model_group = cdi.num_model_group AND cdi.model_id = :model_id)
        HAVING
        number = :articul
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{
			$groups[] = substr($item['model_3gr'], 0, 1);
		}

        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {

        $modificationCode = explode('_', $modificationCode);

        $sql = "
        SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) number,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 20), '!', -1) model_3gr
        FROM feu.mcpart1 mp
        LEFT JOIN coordinates_names cn ON (cn.num_index = mp.pict_index AND cn.model_id = :model_id AND cn.group_detail_sign = 2)
        LEFT JOIN feu.coord_detail_info cdi ON (cn.num_model_group = cdi.num_model_group AND cdi.model_id = :model_id)
        HAVING
        number = :articul
        AND SUBSTRING(model_3gr, 1, 1) = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->execute();

        $aData = $query->fetchAll();

        $subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[] = substr($item['model_3gr'], 0, 3).'-'.substr($item['model_3gr'], -2);
        }
        return array_unique($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $modificationCode = explode('_', $modificationCode);
        $subGroupCode = str_replace('-', '', $subGroupCode);

        $sql = "
        SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) number,
               SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 20), '!', -1) model_3gr,
               pict_index
        FROM feu.mcpart1 mp
        INNER JOIN coordinates_names cn ON (cn.num_index = mp.pict_index AND cn.model_id = :model_id AND cn.group_detail_sign = 2)
        INNER JOIN feu.coord_detail_info cdi ON (cn.num_model_group = cdi.num_model_group AND cdi.model_id = :model_id)
        HAVING
        number = :articul
        AND model_3gr = :subGroupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();

        foreach($aData as $item)
        {
                $schemas[] = $item['pict_index'];
        }

        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {

        $modificationCode = explode('_', $modificationCode);
        $subGroupCode = str_replace('-', '', $subGroupCode);

        $sql = "
        SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) number,
               SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 1), ',', -1) label,
               SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 20), '!', -1) model_3gr,
               pict_index
        FROM feu.mcpart1 mp
        LEFT JOIN coordinates_names cn ON (cn.num_index = mp.pict_index AND cn.model_id = :model_id AND cn.group_detail_sign = 2)
        LEFT JOIN feu.coord_detail_info cdi ON (cn.num_model_group = cdi.num_model_group AND cdi.model_id = :model_id)
        HAVING
        number = :articul
        AND pict_index = :schemaCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', $articul);
        $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->execute();

        $aData = $query->fetchAll();
        $pncs = array();

        foreach ($aData as $index => $value)
        {
            $pncs[] = $value['label'];
        }

        return array_unique($pncs);
    }
} 