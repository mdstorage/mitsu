<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\BmvBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\BmvBundle\Components\BmvConstants;

class BmvCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT fztyp_ktlgausf
        FROM w_fztyp
        WHERE fztyp_karosserie NOT LIKE 'ohne'
        GROUP BY fztyp_ktlgausf
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
        b.ben_text ExtBaureihe
        FROM w_fztyp
        INNER JOIN w_baureihe ON (fztyp_baureihe = baureihe_baureihe)
        INNER JOIN w_ben_gk b ON (baureihe_textcode = b.ben_textcode AND b.ben_iso = 'ru' AND b.ben_regiso = '')
        WHERE fztyp_ktlgausf = :regionCode AND baureihe_marke_tps = 'BMW' AND fztyp_karosserie NOT LIKE 'ohne'
        ORDER BY ExtBaureihe
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item){
        	 
            $models[$item['Baureihe']] = array(Constants::NAME=>strtoupper($item['ExtBaureihe']).' '.$item['Kuzov'],
            Constants::OPTIONS=>array());
      
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sql = "
        SELECT fztyp_mospid, fztyp_erwvbez
        FROM w_fztyp
        WHERE fztyp_ktlgausf = :regionCode
        AND fztyp_baureihe = :modelCode
        ORDER BY fztyp_erwvbez
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['fztyp_mospid']] = array(
                Constants::NAME     => $item['fztyp_erwvbez'],
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
                    FROM bmvlex
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
        select
        hgfg_hg Hauptgruppe,
        hgfg_grafikid Id,
        ben_text Benennung
        from w_hgfg_mosp, w_hgfg, w_ben_gk
        where hgfgm_mospid = :modificationCode and hgfgm_hg = hgfg_hg and hgfg_fg = '00' and hgfgm_produktart = hgfg_produktart
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
                Constants::OPTIONS  => array('Id' => $item ['Id'])
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
bildtaf_grafikid Id
from w_bildtaf_suche, w_ben_gk, w_bildtaf
where bildtafs_hg = :groupCode and bildtafs_mospid = :modificationCode and bildtafs_btnr = bildtaf_btnr and bildtaf_hg = :groupCode and bildtaf_fg = :subGroupCode
and bildtaf_sicher = 'N' and bildtaf_textc = ben_textcode and ben_iso = 'ru' and ben_regiso = '  '
order by Pos
";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('subGroupCode',  $subGroupCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        foreach($aData as $item)
        {

		            $schemas[$item['Id']] = array(
                    Constants::NAME => '('.$item['BildtafelNr'].') '.mb_strtoupper(iconv('cp1251', 'utf8', $item ['Benennung']),'utf8'),
                    Constants::OPTIONS => array('GrId' => $item['BildtafelNr'])
                );
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


        $sqlPnc = "select distinct
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
inner join w_ben_gk tben on (teil_textcode = tben.ben_textcode and tben.ben_iso = 'ru' and tben.ben_regiso = '  ')
left join w_kompl_satz on (btzeilen_sachnr = ks_sachnr_satz and ks_marke_tps = 'BMW')
left join w_tc_performance on (tcp_mospid = :modificationCode and tcp_sachnr = btzeilen_sachnr  and tcp_datum_von <= 20150816 and (tcp_datum_bis is null or tcp_datum_bis >= 20150816))
left join w_grp_information on (btzeilenv_mospid = grpinfo_mospid and grpinfo_sachnr = btzeilen_sachnr and grpinfo_typ = 'FE81')
left join w_ben_gk tkben on (teil_textcode_kom = tkben.ben_textcode and tkben.ben_iso = 'ru' and tkben.ben_regiso = '  ')
left join w_si on (si_sachnr = teil_sachnr)
left join w_bildtaf_bnbben on (bildtafb_btnr = btzeilenv_btnr and bildtafb_bildposnr = btzeilen_bildposnr)
left join w_ben_gk bnbben on (bildtafb_textcode = bnbben.ben_textcode and bnbben.ben_iso = 'ru' and bnbben.ben_regiso = '  ')
where btzeilenv_mospid = :modificationCode and btzeilenv_btnr = :subGroupId order by Bildnummer, Pos, GRP_PA, GRP_HG, GRP_UG, GRP_lfdNr, SI_DokArt
";

    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupId', $options['GrId']);
        $query->execute();

        $aPncs = $query->fetchAll();
    	
    	foreach ($aPncs as &$aPnc)
    	{
    		
    	$sqlSchemaLabels = "
        SELECT grafikhs_topleft_x, grafikhs_topleft_y, grafikhs_bottomright_x, grafikhs_bottomright_y
        FROM w_grafik_hs
        WHERE grafikhs_grafikid = :schemaCode
        AND grafikhs_bildposnr = :position
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('position', $aPnc['Bildnummer']);
        $query->execute();
        
        $aPnc['clangjap'] = $query->fetchAll();
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
            	$pncs[$value['Bildnummer']][Constants::OPTIONS][Constants::COORDS][$item1['grafikhs_topleft_x']] = array(
                    Constants::X1 => $item1['grafikhs_topleft_x']/2,
                    Constants::Y1 => $item1['grafikhs_topleft_y']/2,
                    Constants::X2 => $item1['grafikhs_bottomright_x']/2,
                    Constants::Y2 => $item1['grafikhs_bottomright_y']/2);
            	
            	}
            
            
                
            }
        }

        foreach ($aPncs as $item) {
         	
         	
				$pncs[$item['Bildnummer']][Constants::NAME] = mb_strtoupper(iconv('cp1251', 'utf8', $item ['Teil_Benennung']), 'utf8');
			
			
           
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
        SELECT *
        FROM cats_table
        WHERE catalog_code =:catCode
            AND scheme_num = :pncCode
        	AND compl_name = :schemaCode
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('pncCode', $pncCode);
        $query->bindValue('schemaCode', $options['cd']);
        $query->execute();

        $aArticuls = $query->fetchAll();

        foreach ($aArticuls as $index => $value) {

            $value2 = str_replace(substr($value['model_options'], 0, strpos($value['model_options'], '|')), '', $value['model_options']);
            $articulOptions = explode('|', str_replace(';', '', $value2));

            foreach ($articulOptions as $index1 => $value1) {
                if (($value1 == '') || ($index1 > (count($complectationOptions)-1))) {
                    unset ($articulOptions[$index1]);
                }
            }


            if (count($articulOptions) !== count(array_intersect_assoc($articulOptions, $complectationOptions)))
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
                    Constants::START_DATE => $item['start_data'],
                    Constants::END_DATE => ($item['end_data'])?$item['end_data']:99999999,
                    'option3' => $item['replace_code'],
                )
            );
            
        }

        return $articuls;
    }

    public function getDesc($itemCode, $language)
    {

                $sqlRu = "
                    SELECT lex_name
                    FROM bmvlex
                    WHERE lex_code = :itemCode
                    AND lang = :language
                    ";

                $query = $this->conn->prepare($sqlRu);
                $query->bindValue('itemCode', $itemCode);
                $query->bindValue('language', $language);
                $query->execute();
                $sData = $query->fetch();

        $sDesc = $itemCode;
                if ($sData)
                {
                    $sDesc = $sData['lex_name'];
                }
        else
        {
            $sqlEn = "
                    SELECT lex_name
                    FROM bmvlex
                    WHERE lex_code = :itemCode
                    AND lang = :language
                    ";

            $query = $this->conn->prepare($sqlEn);
            $query->bindValue('itemCode', $sDesc);
            $query->bindValue('language', 'EN');
            $query->execute();
            $sData1 = $query->fetch();

            if ($sData1)
            {
                $sDesc = $sData1['lex_name'];
            }

        }


        return mb_strtoupper(iconv('cp1251', 'utf8', $sDesc), 'utf8');

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