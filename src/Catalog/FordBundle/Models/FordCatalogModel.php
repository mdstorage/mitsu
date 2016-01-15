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
        SELECT
        vincodes_models.Name modelName
        FROM vincodes_models
        ORDER BY modelName
        ";


        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item) {

            $models[rawurlencode($item['modelName'])] = array(Constants::NAME => strtoupper($item['modelName']),
                Constants::OPTIONS => array());

        }


        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = rawurldecode($modelCode);

        $sql  = "
        SELECT
        URL, MIME, Description, AttributeId, vincodes_models.CatalogueCode, catalogue.CatalogueId Id, avs
        FROM vincodes_models, attachmentdata, componentattachment, cataloguecomponent, catalogue
        WHERE vincodes_models.CatalogueCode = catalogue.CatalogueCode and cataloguecomponent.CatalogueId = catalogue.CatalogueId and
        cataloguecomponent.ComponentId = componentattachment.ComponentId AND
        attachmentdata.AttachmentId = componentattachment.AttachmentId
        and Name = :modelCode
        and componentattachment.AttachmentTypeId = 3
        and cataloguecomponent.AssemblyLevel = 1
        ORDER BY Description
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['Id'].'_'.$item['avs']] = array(
                Constants::NAME     => $item['Description']. '('.$item['CatalogueCode'].')',
                Constants::OPTIONS  => array('grafik' =>
                    substr($item['URL'], strpos($item['URL'], 'png')+4, strlen($item['URL'])).'.'.$item['MIME']));

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

                    $result[base64_encode($item['famDesc'])][$item['attrDesc']] = $item['attrDesc'];

                }
            }

        }



        foreach ($result as $index => $value) {



            $complectations[base64_decode($index)] = array(
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
inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos
and (btzeilen_lenkg ='' OR btzeilen_lenkg = :role) and (btzeilen_automatik ='' OR btzeilen_automatik = :korobka) and
(btzeilen_eins ='0' OR btzeilen_eins <= :dataCar) and (btzeilen_auslf ='0' OR :dataCar <= btzeilen_auslf)
)
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
        $query->bindValue('role',  substr($complectationCode, 0, 1));
        $query->bindValue('korobka',  substr($complectationCode, 1, 1));
        $query->bindValue('dataCar',  substr($complectationCode, 2, 8));
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
inner join w_btzeilen on (btzeilenv_btnr = btzeilen_btnr and btzeilenv_pos = btzeilen_pos
and (btzeilen_lenkg ='' OR btzeilen_lenkg = :role) and (btzeilen_automatik ='' OR btzeilen_automatik = :korobka) and
(btzeilen_eins ='0' OR btzeilen_eins <= :dataCar) and (btzeilen_auslf ='0' OR :dataCar <= btzeilen_auslf)
)
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
order by Pos, GRP_PA, GRP_HG, GRP_UG, GRP_lfdNr, SI_DokArt
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

    
} 