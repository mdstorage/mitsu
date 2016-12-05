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
        FROM feuc f
        WHERE SUBSTRING_INDEX(f.auto_name, ' ', 1) = :modelCode
        ORDER BY auto_name
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', urldecode($modelCode));
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
        $sql = "
        select fztyp_lenkung, fztyp_getriebe, fgstnr_prod
        from w_btzeilen_verbauung
        inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos)
        left join w_fztyp on (fztyp_mospid = btzeilenv_mospid)
        left join w_fgstnr on (fgstnr_mospid = fztyp_mospid)
        where btzeilenv_sachnr = :articulCode and btzeilenv_mospid = :modificationCode and (fgstnr_prod <= btzeilen_auslf
        or fgstnr_prod >= btzeilen_eins)
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();
        $complectations = array();

        foreach($aData as $item)
        {
            $complectations[] = $item['fztyp_lenkung'].$item['fztyp_getriebe'].$item['fgstnr_prod'];
        }


        return array_unique($complectations);
    }

    public function getArticulRole($articul, $regionCode, $modelCode, $modificationCode)
    {
        $sql = "
        select fztyp_lenkung, fztyp_getriebe, fgstnr_prod, grafik_blob Id
        from w_btzeilen_verbauung
        inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos)
        left join w_fztyp on (fztyp_mospid = btzeilenv_mospid)
        left join w_baureihe on (fztyp_baureihe = baureihe_baureihe)
        left join w_grafik on (grafik_grafikid = baureihe_grafikid)
        left join w_fgstnr on (fgstnr_mospid = fztyp_mospid)
        where btzeilenv_sachnr = :articulCode and btzeilenv_mospid = :modificationCode and (fgstnr_prod <= btzeilen_auslf
        or fgstnr_prod >= btzeilen_eins)
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $role = array();
        $aData = $query->fetchAll();


        foreach ($aData as &$item) {


            $role[$item['fztyp_lenkung']] = array(
                Constants::NAME => ($item['fztyp_lenkung'] == 'L')?'Левый руль':'Правый руль',
                Constants::OPTIONS => array('grafik' => $item['Id'])
            );
        }


        return ($role);
    }

    public function getArticulComplectationsKorobka($articul, $role, $modificationCode)
    {

        $sql = "
        select fztyp_getriebe
        from w_btzeilen_verbauung
        inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos)
        left join w_fztyp on (fztyp_mospid = btzeilenv_mospid)
        left join w_fgstnr on (fgstnr_mospid = fztyp_mospid)
        where btzeilenv_sachnr = :articulCode and btzeilenv_mospid = :modificationCode and (fgstnr_prod <= btzeilen_auslf
        or fgstnr_prod >= btzeilen_eins) and fztyp_lenkung = :role
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('role', $role);
        $query->execute();

        $complectations = array();
        $aData = $query->fetchAll();

        foreach ($aData as $item) {


            $complectations[$item['fztyp_getriebe']] = array(
                Constants::NAME => $item['fztyp_getriebe'],
                Constants::OPTIONS => array()
            );
        }


        return $complectations;

    }
    public function getArticulComplectationsYear($articul, $role, $modificationCode, $korobka)
    {

        $sql = "
        select fgstnr_prod,  fgstnr_typschl, fgstnr_mospid
        from w_btzeilen_verbauung
        inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos)
        left join w_fztyp on (fztyp_mospid = btzeilenv_mospid)
        left join w_fgstnr on (fgstnr_mospid = fztyp_mospid)
        where btzeilenv_sachnr = :articulCode and btzeilenv_mospid = :modificationCode and (fgstnr_prod <= btzeilen_auslf
        or fgstnr_prod >= btzeilen_eins) and fztyp_lenkung = :role and fztyp_getriebe = :korobka
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('role', $role);
        $query->bindValue('korobka', $korobka);
        $query->execute();


        $complectations = array();
        $aData = $query->fetchAll();


        foreach ($aData as &$item) {


            $complectations[substr($item['fgstnr_prod'], 0, 4)] = array(
                Constants::NAME => substr($item['fgstnr_prod'], 0, 4),
                Constants::OPTIONS => array('modificationCode' => $item['fgstnr_mospid'],
                    'roleCode' => $item['fgstnr_typschl'])
            );
        }

        return $complectations;
    }

    public function getArticulComplectationsMonth($articul, $role, $modificationCode, $year, $korobka)
    {
        $sql = "
        select fgstnr_prod,  fztyp_lenkung, fztyp_getriebe
        from w_btzeilen_verbauung
        inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos)
        left join w_fztyp on (fztyp_mospid = btzeilenv_mospid)
        left join w_fgstnr on (fgstnr_mospid = fztyp_mospid)
        where btzeilenv_sachnr = :articulCode and btzeilenv_mospid = :modificationCode and (fgstnr_prod <= btzeilen_auslf
        or fgstnr_prod >= btzeilen_eins) and fztyp_lenkung = :role and fztyp_getriebe = :korobka AND fgstnr_prod LIKE :years
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', substr($articul, 4, strlen($articul)));
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('role', $role);
        $query->bindValue('korobka', $korobka);
        $query->bindValue('years', '%'.$year.'%');
        $query->execute();


        $complectations = array();
        $aData = $query->fetchAll();


        foreach ($aData as $item) {


            $complectations[$item['fztyp_lenkung'].$item['fztyp_getriebe'].$item['fgstnr_prod']] = array(
                Constants::NAME => substr($item['fgstnr_prod'],4,2),
                Constants::OPTIONS => array()
            );
        }

        return $complectations;

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

        $articulCode = substr($articul, 4, strlen($articul));
        $sql = "
        SELECT btzeilenv_mospid, btzeilenv_btnr, btzeilenv_pos Pos, btzeilen_bildposnr Bildnummer, btzeilenv_sachnr, bildtafs_hg, bildtafs_fg, btzeilen_automatik, btzeilen_lenkg,
        btzeilen_eins, btzeilen_auslf
        FROM w_btzeilen_verbauung
        left JOIN w_bildtaf_suche ON (bildtafs_mospid = :modificationCode and bildtafs_fg = :subGroupCode and bildtafs_hg = :groupCode)
        INNER JOIN w_btzeilen ON (btzeilenv_btnr = btzeilen_btnr AND btzeilenv_pos = btzeilen_pos)
        WHERE btzeilenv_mospid = :modificationCode
        AND btzeilenv_btnr = bildtafs_btnr and (btzeilen_lenkg ='' OR btzeilen_lenkg = :role) and (btzeilen_automatik ='' OR btzeilen_automatik = :korobka) and
(btzeilen_eins ='0' OR btzeilen_eins <= :dataCar) and (btzeilen_auslf ='0' OR :dataCar <= btzeilen_auslf)
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('role',  substr($complectationCode, 0, 1));
        $query->bindValue('korobka',  substr($complectationCode, 1, 1));
        $query->bindValue('dataCar',  substr($complectationCode, 2, 8));
        $query->execute();

        $aData = $query->fetchAll();
        $pncs = array();

        foreach ($aData as $index => $value) {
            if (($value['Bildnummer'] != '--') && ($value['btzeilenv_sachnr'] == $articulCode)) {
                $pncs[] = $value['Bildnummer'];
            }
        }


        $Pos = NULL;
        foreach ($aData as $index => $value)
        {
            if (($value['Bildnummer'] == '--') && ($value['btzeilenv_sachnr'] == $articulCode))
            {
              $Pos = $value ['Pos'];
            }
        }

        if ($Pos) {
            $minPos = array();
            $min = 10;

            foreach ($aData as $index => $value) {
                if (($value['Bildnummer'] != '--') && ($value['Pos'] < $Pos)
                    && (($Pos - $value['Pos']) < $min)
                ) {
                    $min = $Pos - $value['Pos'];
                    $minIndex = $index;
                    $minPos[] = $value['Pos'];
                    $pncs[] = $value['Bildnummer'];

                }
            }
        }

        return array_unique($pncs);
    }
} 