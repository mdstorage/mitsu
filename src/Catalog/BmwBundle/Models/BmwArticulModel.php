<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\BmwBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\BmwBundle\Components\BmwConstants;

class BmwArticulModel extends BmwCatalogModel{

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
        select fztyp_karosserie Kuzov, fztyp_baureihe Baureihe
        from w_fztyp, w_btzeilen_verbauung
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
            $models[] = $item['Baureihe'].'_'.$item['Kuzov'];

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $sql = "
        select btzeilenv_mospid
        from w_btzeilen_verbauung
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

        $role = array();
        $aData = $query->fetchAll();


        foreach ($aData as &$item) {


            $role[$item['fztyp_lenkung']] = array(
                Constants::NAME => ($item['fztyp_lenkung'] == 'L')?'Левый руль':'Правый руль',
                Constants::OPTIONS => array()
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