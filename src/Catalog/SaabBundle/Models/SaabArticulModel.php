<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\SaabBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\SaabBundle\Components\SaabConstants;

class SaabArticulModel extends SaabCatalogModel{

    public function getArticulRegions($articulCode){


        $sql = "
        select fztyp_ktlgausf from w_fztyp, w_btzeilen_verbauung
        where btzeilenv_sachnr = :articulCode and fztyp_mospid = btzeilenv_mospid and fztyp_karosserie NOT LIKE 'ohne'
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articulCode, 4, strlen($articulCode)));
        $query->execute();

        $aData = $query->fetchAll();
        $regions = array();
                  
        foreach($aData as $item)
        {
            $regions[] = $item['fztyp_ktlgausf'];

		}
        return array_unique($regions);

    }

    public function getArticulModels($articul, $regionCode)
    {

        $sql = "
        select fztyp_baureihe from w_fztyp, w_btzeilen_verbauung
        where btzeilenv_sachnr = :articulCode and fztyp_mospid = btzeilenv_mospid and fztyp_karosserie NOT LIKE 'ohne'
        and fztyp_ktlgausf = :regionCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articul, 4, strlen($articul)));
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();
        $models = array();


        foreach($aData as $item)
        {
            $models[] = $item['fztyp_baureihe'];

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $sql = "
        select btzeilenv_mospid from w_btzeilen_verbauung
        where btzeilenv_sachnr = :articulCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articul, 4, strlen($articul)));
        $query->execute();

        $aData = $query->fetchAll();
        $modifications = array();


        foreach($aData as $item)
        {
            $modifications[] = $item['btzeilenv_mospid'];

        }

        return array_unique($modifications);
    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $ghg = $this->getComplectations($regionCode, $modelCode, $modificationCode);
        $test = array();


        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        select * from cats_table
        where detail_code = :articulCode
        AND catalog_code = :catCode

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('catCode', $catCode);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataCatalog = array();

        if ($aData)
        {

            foreach ($aData as $item) {
                $sqlCatalog = "
        SELECT model_index
        FROM vin_model
        WHERE model =:modificationCode
        ";

                $query = $this->conn->prepare($sqlCatalog);
                $query->bindValue('modificationCode', $modificationCode);
                $query->execute();

                $aDataCatalog[] = $query->fetchAll();
            }


            $complectations = array();

            foreach ($aDataCatalog as $item2) {
                foreach ($item2 as $item1) {
                    $complectations[] = $item1['model_index'];
                }

            }
        }

        foreach ($ghg as $indexCompl => $valueCompl)
        {
            foreach ($aData as $index => $value)
            {
                $value2 = str_replace(substr($value['model_options'], 0, strpos($value['model_options'], '|')), '', $value['model_options']);
                $articulOptions = explode('|', str_replace(';', '', $value2));
            /*  $complectationOptions = $ghg['37454']['options']['option2'];*/
                $complectationOptions = $valueCompl['options']['option2'];

                foreach ($articulOptions as $index1 => $value1) {
                    if (($value1 == '') || ($index1 > (count($complectationOptions)-1))) {
                        unset ($articulOptions[$index1]);
                    }
                }
                $cd = count($articulOptions);
                $cdc = count(array_intersect_assoc($articulOptions, $complectationOptions));

                if ($cd == $cdc)
                {
                  $test[] = $indexCompl;
                }

            }

        }

        return (array_intersect(array_unique($complectations), array_unique($test)));
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $sql = "
        SELECT bildtafs_hg
        FROM w_bildtaf_suche, w_btzeilen_verbauung
        WHERE btzeilenv_sachnr = :articul
        and bildtafs_btnr = btzeilenv_btnr
        and btzeilenv_mospid = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

    	$groups = array();

        foreach($aData as $item)
		{


			$groups[]=$item['bildtafs_hg'];

			
		}

        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $sql = "
        SELECT bildtafs_fg
        FROM w_bildtaf_suche, w_btzeilen_verbauung
        WHERE btzeilenv_sachnr = :articul
        and bildtafs_btnr = btzeilenv_btnr
        and btzeilenv_mospid = :modificationCode
        and bildtafs_hg = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]=$item['bildtafs_fg'];
        }
        return array_unique($subgroups);
    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $sql = "
        SELECT grafik_blob
        FROM w_bildtaf, w_btzeilen_verbauung, w_grafik
        WHERE btzeilenv_sachnr = :articul
        and bildtaf_grafikid = grafik_grafikid
        and bildtaf_btnr = btzeilenv_btnr
        and btzeilenv_mospid = :modificationCode
        and bildtaf_hg = :groupCode
        and bildtaf_fg = :subGroupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();

        foreach($aData as $item)
        {
            if (strpos($item['grafik_blob'], '_z'))
            {
                $item['grafik_blob'] = str_replace('tif', 'png', $item['grafik_blob']);
                $schemas[]=$item['grafik_blob'];

            }

        }

        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {

        $sql = "
        SELECT btzeilen_bildposnr
        FROM w_bildtaf, w_btzeilen
        WHERE btzeilen_sachnr = :articul
        and bildtaf_btnr = btzeilen_btnr
        and bildtaf_hg = :groupCode
        and bildtaf_fg = :subGroupCode
        and bildtaf_grafikid = :schemaCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articul', substr($articul, 4, strlen($articul)));
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();

        foreach($aData as $item)
        {
            $pncs[]=$item['btzeilen_bildposnr'];
        }
        return array_unique($pncs);
    }
} 