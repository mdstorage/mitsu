<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\KiaBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\KiaBundle\Components\KiaConstants;

class KiaArticulModel extends KiaCatalogModel{

    public function getArticulRegions($articulCode)
    {
        $sql = "
        SELECT ctlg.data_regions
        FROM catalog ctlg
        INNER JOIN cats_dat_parts cdp ON (cdp.cat_folder = ctlg.cat_folder AND cdp.number = :articulCode)
        GROUP BY data_regions
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articulCode);
        $query->execute();
        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $pieces = explode("|", $item['data_regions']);
            foreach($pieces as $value){
                if($value != ''){
                    $regions[] = trim($value);
                }
            }
        }
        return array_unique($regions);
    }

    public function getArticulModels($articul, $regionCode)
    {
        $sql = "
        SELECT ctlg.family
        FROM catalog ctlg
        INNER JOIN cats_dat_parts cdp ON (cdp.cat_folder = ctlg.cat_folder AND cdp.number = :articulCode)
        WHERE ctlg.data_regions LIKE :regionCode
        GROUP BY data_regions
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();

        if ($aData){
            foreach($aData as $item) {
                $models[] = urlencode($item['family']);
            }
        }
        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);
        $sql = "
        SELECT ctlg.cat_folder, ctlg.catalogue_code, ctlg.cat_number
        FROM catalog ctlg
        INNER JOIN cats_dat_parts cdp ON (cdp.cat_folder = ctlg.cat_folder AND cdp.number = :articulCode)
        WHERE ctlg.data_regions LIKE :regionCode AND ctlg.family = :modelCode
        GROUP BY ctlg.cat_number
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->bindValue('modelCode', $modelCode);
        $query->execute();
        $aData = $query->fetchAll();
        $modifications = array();
        if ($aData) {
            foreach ($aData as $item) {
                    $modifications[] = $item['catalogue_code'].'_'.$item['cat_folder'];
            }
        }
        return array_unique($modifications);
    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        SELECT vm.model, cdp.compatibility
        FROM vin_model vm
        INNER JOIN cats_dat_parts cdp ON (cdp.cat_folder = :catCode AND cdp.number = :articulCode)
        WHERE vm.catalogue_code LIKE :modificationCode
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('catCode', $catCode);
        $query->execute();
        $aData = $query->fetchAll();

        $aModel = array();
        $aCompatibility = array();
        $complectations = array();

        /*Отфильтруем коды комплектаций по колонке cats_dat_parts.compatibility в фильтре filterComplectations() из KiaCatalogModel*/
        foreach ($aData as $item){
            $aModel[$item['model']] = $item['model'];
            $aCompatibility[$item['compatibility']]['compatibility'] = $item['compatibility'];
        }
        foreach ($aModel as $item){
            $k[$item] = $this->filterComplectations($modificationCode, $item, $aCompatibility);
            if (count($k[$item]) > 0)
            $complectations[$item] = $k[$item];
        }
        /*---------------*/

        return $complectations;
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $sql = "
        SELECT cdp.major_sect
        FROM cats_dat_parts cdp
        WHERE cdp.cat_folder = :catCode AND cdp.number = :articulCode
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('catCode', $catCode);
        $query->execute();
        $aData = $query->fetchAll();
    	$groups = array();

        foreach($aData as $item){
			$groups[] = $item['major_sect'];
			}
        return array_unique($groups);
    }
    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        SELECT cdp.minor_sect, cdp.compatibility, cdp.ref
        FROM cats_dat_parts cdp
        WHERE cdp.cat_folder = :catCode AND cdp.number = :articulCode AND cdp.major_sect = :groupCode
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();
        $aData = $query->fetchAll();

        /*Убираем pnc, которых нет на картинке. Если нет координат - значит нет на картинке*/
        foreach ($aData as &$aPnc){
            $sqlSchemaLabels = "
            SELECT x1, y1, x2, y2
            FROM cats_dat_ref
            WHERE cat_folder = :catCode
            AND img_name = :schemaCode
            AND ref = :ref
            ";
            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('catCode', $catCode);
            $query->bindValue('schemaCode', $aPnc['minor_sect']);
            $query->bindValue('ref', $aPnc['ref']);
            $query->execute();
            $aPnc['clangjap'] = $query->fetchAll();
        }
        foreach ($aData as $index=>$value){
            if (count($value['clangjap']) == 0){
                unset($aData[$index]);
            }
        }
        /*применяем фильтр совместимости с выбранной комплектацией*/
        $aData = $this->filterComplectations($modificationCode, $complectationCode, $aData);
        /*-------------*/
        $subgroups = array();
        foreach($aData as $item){
            $subgroups[] = $item['minor_sect'];
        }
        return array_unique($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $schemas = array();
                $schemas[] = $subGroupCode;
        return $schemas;
    }
         
    public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        SELECT cdp.ref
        FROM cats_dat_parts cdp
        WHERE cdp.cat_folder = :catCode AND cdp.number = :articulCode AND cdp.major_sect = :groupCode AND cdp.minor_sect = :subGroupCode
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();
        $aData = $query->fetchAll();
        $pncs = array();
        foreach($aData as $item) {
            $pncs[] = $item['ref'];
        }
        return array_unique($pncs);
    }
} 