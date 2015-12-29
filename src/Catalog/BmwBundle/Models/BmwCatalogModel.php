<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\BmwBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\BmwBundle\Components\BmwConstants;

class BmwCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT fztyp_ktlgausf
        FROM w_fztyp
        WHERE fztyp_karosserie NOT LIKE 'ohne'
        AND (fztyp_ktlgausf = 'ECE'
        OR fztyp_ktlgausf = 'USA'
        OR fztyp_ktlgausf = 'RUS')
        UNION
        SELECT fztyp_ktlgausf
        FROM w_fztyp
        WHERE fztyp_karosserie NOT LIKE 'ohne'
        AND (fztyp_ktlgausf NOT LIKE 'ECE'
        OR fztyp_ktlgausf NOT LIKE 'USA'
        OR fztyp_ktlgausf NOT LIKE 'RUS')

        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item)
        {
            $regions[$item['fztyp_ktlgausf']] = array(Constants::NAME=>$item['fztyp_ktlgausf'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql  = "
        SELECT DISTINCT
        fztyp_karosserie Kuzov,
        fztyp_baureihe Baureihe,
        grafik_blob Id,
        b.ben_text ExtBaureihe
        FROM w_fztyp, w_baureihe, w_grafik, w_ben_gk b
        WHERE (baureihe_textcode = b.ben_textcode AND b.ben_iso = 'ru' AND b.ben_regiso = '') AND grafik_grafikid = baureihe_grafikid AND fztyp_baureihe = baureihe_baureihe
        AND fztyp_ktlgausf = :regionCode AND baureihe_marke_tps = 'BMW' AND fztyp_karosserie NOT LIKE 'ohne'
        ORDER BY ExtBaureihe
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item) {

            $models[$item['Baureihe'].'_'.$item['Kuzov']] = array(Constants::NAME => strtoupper($item['ExtBaureihe']) . ' ' . $item['Kuzov'],
                Constants::OPTIONS => array('grafik' => $item['Id']));

        }


        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {

        $sql = "
        SELECT fztyp_mospid, fztyp_erwvbez, fztyp_getriebe
        FROM w_fztyp
        WHERE fztyp_ktlgausf = :regionCode
        AND fztyp_baureihe = :modelCode
        and fztyp_karosserie = :submodelCode
        ORDER BY fztyp_erwvbez
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', substr($modelCode, 0, strpos($modelCode, '_')));
        $query->bindValue('submodelCode', substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode)));
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['fztyp_mospid']] = array(
                Constants::NAME     => $item['fztyp_erwvbez'].' '.($item['fztyp_getriebe']==='A'?'АКПП':'').($item['fztyp_getriebe']==='M'?'MКПП':''),
                Constants::OPTIONS  => array());

        }

        return $modifications;
    }

    /**
     Функция getComplectations() учитывает все положения руля и даты производства модификаций. Используется только в каталог_контроллере коммонБандла.
     */

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {
        $sql = "
        SELECT fztyp_lenkung, fgstnr_prod
        FROM w_fztyp, w_fgstnr
        WHERE fztyp_mospid = :modificationCode AND fztyp_mospid = fgstnr_mospid AND fgstnr_typschl = fztyp_typschl
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', substr($modificationCode, 0, strpos($modificationCode, '_')));
        $query->execute();

        $complectations = array();
        $aData = $query->fetchAll();

        foreach ($aData as &$item) {


            $complectations[$item['fztyp_lenkung'].$item['fgstnr_prod']] = array(
                Constants::NAME => $item['fztyp_lenkung'].$item['fgstnr_prod'],
                Constants::OPTIONS => array()
            );
        }

        return ($complectations);

    }

    public function getComplectationsKorobka($role, $modificationCode)
    {

        $sql = "
        SELECT fztyp_getriebe
        FROM w_fztyp
        WHERE fztyp_mospid = :modificationCode AND fztyp_lenkung = :role
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('role', $role);
        $query->execute();

        $complectations = array();
        $aData = $query->fetchAll();

        foreach ($aData as &$item) {


            $complectations[$item['fztyp_getriebe']] = array(
                Constants::NAME => $item['fztyp_getriebe'],
                Constants::OPTIONS => array()
            );
        }


        return $complectations;

    }
    /**
    Функция getRole() возвращает только положения руля. Используется только в каталог_контроллере БМВ_Бандла.
     */


    public function getRole($regionCode, $modelCode, $modificationCode)
   
    {
        $sql = "
        SELECT fztyp_lenkung
        FROM w_fztyp
        WHERE fztyp_mospid = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
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

    /**
    Функция getComplectationsData() возвращает год производства при изветном положении руля. Используется только в каталог_контроллере БМВ_Бандла.
     */

    public function getComplectationsYear($role, $modificationCode, $korobka)

    {
        $sql = "
        SELECT fgstnr_prod, fgstnr_typschl, fgstnr_mospid
        FROM w_fgstnr
        INNER JOIN w_fztyp ON (fztyp_lenkung = :role AND fztyp_getriebe = :korobka AND fgstnr_mospid = fztyp_mospid AND fgstnr_typschl = fztyp_typschl)
        WHERE fgstnr_mospid = :modificationCode
        ORDER BY fgstnr_prod
        ";

        $query = $this->conn->prepare($sql);
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
    /**
    Функция getComplectationsCatalogData() возвращает месяц производства при изветных годе производства и положении руля. Используется только в каталог_контроллере БМВ_Бандла.
     */


    public function getComplectationsMonth($role, $modificationCode, $year, $korobka)
    {

        $sql = "
        SELECT fgstnr_prod, fztyp_lenkung, fztyp_getriebe
        FROM w_fgstnr, w_fztyp
        WHERE fgstnr_mospid = :modificationCode
        AND fztyp_mospid = fgstnr_mospid AND fgstnr_typschl = fztyp_typschl
        AND fgstnr_typschl = :role
        AND  fgstnr_prod LIKE :years
        AND fztyp_getriebe = :korobka
        ORDER BY fgstnr_prod
        ";

        $query = $this->conn->prepare($sql);
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



    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {

         $sql = "
        select
        hgfg_hg Hauptgruppe,
        hgfg_grafikid Id,
        grafik_blob BlobMod,
        ben_text Benennung
        from w_hgfg_mosp, w_hgfg, w_ben_gk, w_grafik
        where hgfgm_mospid = :modificationCode and hgfgm_hg = hgfg_hg and hgfg_fg = '00' and hgfgm_produktart = hgfg_produktart and hgfg_grafikid = grafik_grafikid
        and hgfgm_bereich = hgfg_bereich and hgfg_textcode = ben_textcode and ben_iso = 'ru' and ben_regiso = '  '
        ORDER BY Hauptgruppe
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();
        $aData = $query->fetchAll();

        $groups = array();


        foreach($aData as $item){
            $groups[$item['Hauptgruppe']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['Benennung']),'utf8'),
                Constants::OPTIONS  => array('Id' => $item ['BlobMod'])
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
        select
hgfg_hg Hauptgruppe,
hgfg_fg Funktionsgruppe,
ben_text Benennung
from w_hgfg_mosp, w_hgfg, w_ben_gk
where hgfgm_mospid = :modificationCode and hgfg_hg = :groupCode and hgfgm_hg = hgfg_hg and hgfgm_fg = hgfg_fg and hgfgm_produktart = hgfg_produktart
and hgfgm_bereich = hgfg_bereich and hgfg_textcode = ben_textcode and ben_iso = 'ru' and ben_regiso = '  '
ORDER BY Funktionsgruppe
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();


        $subgroups = array();
        foreach($aData as $item){

            $subgroups[$item['Funktionsgruppe']] = array(

                Constants::NAME => mb_strtoupper(iconv('cp1251', 'utf8', $item ['Benennung']),'utf8'),
                Constants::OPTIONS => array()
            );

        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {


        $sql = "
select distinct
bildtaf_btnr BildtafelNr,
bildtaf_bteart BildtafelArt,
ben_text Benennung,
bildtaf_kommbt Kommentar,
bildtaf_vorh_cp CPVorhanden,
bildtaf_bedkez BedingungKZ,
bildtaf_pos Pos,
bildtaf_grafikid Id,
grafik_blob BlobMod
from w_bildtaf_suche, w_ben_gk, w_bildtaf, w_grafik
where bildtafs_hg = :groupCode and bildtafs_mospid = :modificationCode and bildtafs_btnr = bildtaf_btnr and bildtaf_hg = :groupCode and bildtaf_fg = :subGroupCode
and bildtaf_sicher = 'N' and bildtaf_textc = ben_textcode and ben_iso = 'ru' and ben_regiso = '  ' and bildtaf_grafikid = grafik_grafikid
order by Pos
";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('subGroupCode',  $subGroupCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        foreach($aData as &$item)
        {
            if (strpos($item['BlobMod'], '_z')) {
                $item['BlobMod'] = str_replace('tif', 'png', $item['BlobMod']);
                $schemas[$item['BlobMod']] = array(
                    Constants::NAME => '(' . $item['BildtafelNr'] . ') ' . mb_strtoupper(iconv('cp1251', 'utf8', $item ['Benennung']), 'utf8'),
                    Constants::OPTIONS => array('GrId' => $item['BildtafelNr'])
                );
            }
        }

        return $schemas;
    }

    public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {




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
select distinct
btzeilen_bildposnr Bildnummer,
teil_hauptgr Teil_HG,
teil_untergrup Teil_UG,
teil_sachnr Teil_Sachnummer,
tben.ben_text Teil_Benennung,
teil_benennzus Teil_Zusatz,
teil_entfall_kez Teil_Entfall,
teil_textcode_kom Teil_Kommentar_Id,
tkben.ben_text Teil_Kommentar,
teil_kom_pi Teil_Komm_PI,
teil_vorhanden_si Teil_SI,
teil_ist_reach Teil_Reach,
teil_ist_aspg Teil_Aspg,
teil_ist_stecker Teil_Stecker,
teil_ist_diebstahlrelevant Teil_Diebstahlrelevant,
si_dokart SI_DokArt,
grpinfo_leitaw_pa GRP_PA,
grpinfo_leitaw_hg GRP_HG,
grpinfo_leitaw_ug GRP_UG,
grpinfo_leitaw_nr GRP_lfdNr,
btzeilenv_vmenge Menge,
btzeilen_kat Kat_KZ,
btzeilen_automatik Getriebe_KZ,
btzeilen_lenkg Lenkung_KZ,
btzeilen_eins Einsatz,
btzeilen_auslf Auslauf,
btzeilen_kommbt KommBT,
btzeilen_kommvor KommVor,
btzeilen_kommnach KommNach,
ks_sachnr_satz Satz_Sachnummer,
btzeilen_gruppeid GruppeId,
btzeilen_blocknr BlockNr,
bnbben.ben_text BnbBenText,
btzeilen_pos Pos,
btzeilenv_alter_kz BtZAlter,
btzeilen_bedkez_pg Teil_BedkezPG,
btzeilenv_bed_art BedingungArt,
btzeilenv_bed_alter BedingungAlter
from w_btzeilen_verbauung
inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos)
inner join w_teil on (btzeilen_sachnr = teil_sachnr)
inner join w_ben_gk tben on (teil_textcode = tben.ben_textcode and tben.ben_iso = 'ru' and tben.ben_regiso = '')
left join w_kompl_satz on (btzeilen_sachnr = ks_sachnr_satz and ks_marke_tps = 'BMW')
left join w_tc_performance on (tcp_mospid = :modificationCode and tcp_sachnr = btzeilen_sachnr)
left join w_grp_information on (btzeilenv_mospid = grpinfo_mospid and grpinfo_sachnr = btzeilen_sachnr and grpinfo_typ = 'FE81')
left join w_ben_gk tkben on (teil_textcode_kom = tkben.ben_textcode and tkben.ben_iso = 'ru' and tkben.ben_regiso = '')
left join w_si on (si_sachnr = teil_sachnr)
left join w_bildtaf_bnbben on (bildtafb_btnr = btzeilenv_btnr and bildtafb_bildposnr = btzeilen_bildposnr)
left join w_ben_gk bnbben on (bildtafb_textcode = bnbben.ben_textcode and bnbben.ben_iso = 'ru' and bnbben.ben_regiso = '')
where btzeilenv_mospid = :modificationCode and btzeilenv_btnr = :subGroupId order by Bildnummer, Pos, GRP_PA, GRP_HG, GRP_UG, GRP_lfdNr, SI_DokArt
";

    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupId', $options['GrId']);
        $query->execute();

        $aPncs = $query->fetchAll();


    	
    	foreach ($aPncs as &$aPnc) {
            if ($aPnc['Bildnummer'] != '--' || $aPnc['Bildnummer'] != null) {

                $sqlSchemaLabels = "
        SELECT grafikhs_topleft_x, grafikhs_topleft_y, grafikhs_bottomright_x, grafikhs_bottomright_y
        FROM w_grafik_hs
        WHERE grafikhs_grafikid = :schemaCode
        AND grafikhs_bildposnr = :pos
        ";

                $query = $this->conn->prepare($sqlSchemaLabels);
                $query->bindValue('schemaCode', $schemaCode);
                $query->bindValue('pos', $aPnc['Bildnummer']);
                $query->execute();

                $aPnc['coords'] = $query->fetchAll();
            }
        }


        $pncs = array();
      foreach ($aPncs as $index=>$value) {
            {
                if ($value['Bildnummer'] == '--' || $value['Bildnummer'] == null)
                {
                    unset ($aPncs[$index]);
                }
            	foreach ($value['coords'] as $item1)
            	{
            	$pncs[$value['Bildnummer']][Constants::OPTIONS][Constants::COORDS][$item1['grafikhs_topleft_x']] = array(
                    Constants::X1 => floor($item1['grafikhs_topleft_x']),
                    Constants::Y1 => $item1['grafikhs_topleft_y'],
                    Constants::X2 => $item1['grafikhs_bottomright_x'],
                    Constants::Y2 => $item1['grafikhs_bottomright_y']);
            	
            	}
            
            
                
            }
        }

        foreach ($aPncs as $item) {
         	
         	
				$pncs[$item['Bildnummer']][Constants::NAME] = mb_strtoupper(iconv('cp1251', 'utf8', $item['Teil_Benennung']), 'utf8');
			
			
           
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


        $sqlPnc = "select distinct
btzeilen_bildposnr Bildnummer,
teil_hauptgr Teil_HG,
teil_untergrup Teil_UG,
teil_sachnr Teil_Sachnummer,
tben.ben_text Teil_Benennung,
nuch.komm_pos Komnach,
kommnuch.ben_text Komm_Benennung,
vor.komm_pos Komvor,
vor.komm_code KomCode,
vor.komm_vz KomVz,
kommvor.ben_text Komm_Vor,
teil_benennzus Teil_Zusatz,
teil_entfall_kez Teil_Entfall,
teil_textcode_kom Teil_Kommentar_Id,
tkben.ben_text Teil_Kommentar,
teil_kom_pi Teil_Komm_PI,
teil_vorhanden_si Teil_SI,
teil_ist_reach Teil_Reach,
teil_ist_aspg Teil_Aspg,
teil_ist_stecker Teil_Stecker,
teil_ist_diebstahlrelevant Teil_Diebstahlrelevant,
si_dokart SI_DokArt,
grpinfo_leitaw_pa GRP_PA,
grpinfo_leitaw_hg GRP_HG,
grpinfo_leitaw_ug GRP_UG,
grpinfo_leitaw_nr GRP_lfdNr,
btzeilenv_vmenge Menge,
btzeilen_kat Kat_KZ,
btzeilen_automatik Getriebe_KZ,
btzeilen_lenkg Lenkung_KZ,
btzeilen_eins Einsatz,
btzeilen_auslf Auslauf,
btzeilen_kommbt KommBT,
btzeilen_kommvor KommVor,
btzeilen_kommnach KommNach,
ks_sachnr_satz Satz_Sachnummer,
btzeilen_gruppeid GruppeId,
btzeilen_blocknr BlockNr,
bnbben.ben_text BnbBenText,
btzeilen_pos Pos,
btzeilenv_alter_kz BtZAlter,
btzeilen_bedkez_pg Teil_BedkezPG,
btzeilenv_bed_art BedingungArt,
btzeilenv_bed_alter BedingungAlter
from w_btzeilen_verbauung
inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos)
inner join w_teil on (btzeilen_sachnr = teil_sachnr)
inner join w_ben_gk tben on (teil_textcode = tben.ben_textcode and tben.ben_iso = 'ru' and tben.ben_regiso = '  ')
left join w_komm nuch on (btzeilen_kommnach = nuch.komm_id)
left join w_ben_gk kommnuch on (nuch.komm_textcode = kommnuch.ben_textcode and kommnuch.ben_iso = 'ru' and kommnuch.ben_regiso = '  ')
left join w_komm vor on (btzeilen_kommvor = vor.komm_id)
left join w_ben_gk kommvor on (vor.komm_textcode = kommvor.ben_textcode and kommvor.ben_iso = 'ru' and kommvor.ben_regiso = '  ')
left join w_kompl_satz on (btzeilen_sachnr = ks_sachnr_satz and ks_marke_tps = 'BMW')
left join w_tc_performance on (tcp_mospid = :modificationCode and tcp_sachnr = btzeilen_sachnr  and tcp_datum_von <= 20150816 and (tcp_datum_bis is null or tcp_datum_bis >= 20150816))
left join w_grp_information on (btzeilenv_mospid = grpinfo_mospid and grpinfo_sachnr = btzeilen_sachnr and grpinfo_typ = 'FE81')
left join w_ben_gk tkben on (teil_textcode_kom = tkben.ben_textcode and tkben.ben_iso = 'ru' and tkben.ben_regiso = '  ')
left join w_si on (si_sachnr = teil_sachnr)
left join w_bildtaf_bnbben on (bildtafb_btnr = btzeilenv_btnr and bildtafb_bildposnr = btzeilen_bildposnr)
left join w_ben_gk bnbben on (bildtafb_textcode = bnbben.ben_textcode and bnbben.ben_iso = 'ru' and bnbben.ben_regiso = '  ')
where btzeilenv_mospid = :modificationCode and btzeilenv_btnr = :subGroupId and (btzeilen_bildposnr = :pncCode or btzeilen_bildposnr = '--')
AND (CASE WHEN btzeilen_eins NOT LIKE '0' THEN :complectationCode >= btzeilen_eins ELSE btzeilen_eins <> '1' END)
AND (CASE WHEN btzeilen_auslf NOT LIKE '0' THEN btzeilen_auslf >= :complectationCode  ELSE btzeilen_auslf <> '1' END)
AND (CASE WHEN btzeilen_lenkg NOT LIKE '' THEN btzeilen_lenkg = :role  ELSE btzeilen_lenkg <> '1' END)

 order by Pos, GRP_PA, GRP_HG, GRP_UG, GRP_lfdNr, SI_DokArt
";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('complectationCode', substr($complectationCode,1,strlen($complectationCode)-1));
        $query->bindValue('subGroupId', $options['GrId']);
        $query->bindValue('pncCode', $pncCode);
        $query->bindValue('role', substr($complectationCode,0,1));

        $query->execute();

        $aArticuls = $query->fetchAll();


        $nach = array();
        $nachIndex = array();


        foreach ($aArticuls as $index => $value)
        {

            if (($value['Bildnummer'] != '--') && (iconv('cp1251', 'utf8',trim($value['Komm_Benennung'])) ==='только в комбинации с'))
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

        foreach ($aArticuls as $index => $value)
        {

            if (($value['Bildnummer'] != '--') &&  (iconv('cp1251', 'utf8',trim($value['Komm_Benennung'])) ==='только в комбинации с'))
            {
                $nachIndex[] = $index;
                $nachPos[] = $value['Pos'];


            }

        }


       $min = 10;
        foreach ($aArticuls as $index => $value)
        {
                if (($value['Bildnummer'] == '--') && ($value['Pos'] > $aArticuls[$nachIndex[0]]['Pos'])
                    && (($value['Pos'] - $aArticuls[$nachIndex[0]]['Pos']) < $min)) {
                    $min =  $value['Pos'] - $aArticuls[$nachIndex[0]]['Pos'];
                    $minIndex = $index;
                    $minPos = $value['Pos'];

                }
        }

        $bArticuls = array();
     foreach ($aArticuls as $index => $value)
        {
            if (($value['Bildnummer'] == '--') && (($index!=$minIndex) && ($index!=($minIndex+1)))) {
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

    
} 