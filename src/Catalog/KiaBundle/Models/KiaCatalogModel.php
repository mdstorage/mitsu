<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\KiaBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\KiaBundle\Components\KiaConstants;

class KiaCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT data_regions
        FROM catalog
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
        FROM catalog
        WHERE data_regions LIKE :regionCode
        ORDER BY family
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item){
            $models[urlencode($item['family'])] = array(Constants::NAME=>strtoupper($item['family']),
            Constants::OPTIONS=>array());
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);
        $sql = "
        SELECT *
        FROM catalog
        WHERE data_regions LIKE :regionCode
        AND family = :modelCode
        ORDER BY cat_number
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', '%'.$regionCode.'%');
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['catalogue_code'].'_'.$item['cat_folder']] = array(
                Constants::NAME     => $item['cat_name'],
                Constants::OPTIONS  => array('option1'=>strtoupper($item['cat_folder'])));

        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $modelCode = rawurldecode($modelCode);

        $sql = "
        SELECT
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 1), '|', -1) as f1,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 2), '|', -1) as f2,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 3), '|', -1) as f3,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 4), '|', -1) as f4,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 5), '|', -1) as f5,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 6), '|', -1) as f6,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 7), '|', -1) as f7,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 8), '|', -1) as f8,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 9), '|', -1) as f9,
        SUBSTRING_INDEX(vm.ucc, '|', -1) as f10,
        date_from, date_to, model,

        kig1.lex_desc ken1,
        kig2.lex_desc ken2,
        kig3.lex_desc ken3,
        kig4.lex_desc ken4,
        kig5.lex_desc ken5,
        kig6.lex_desc ken6,
        kig7.lex_desc ken7,
        kig8.lex_desc ken8,
        kig9.lex_desc ken9,
        kig10.lex_desc ken10,

        tkm1.lex_desc ten1,
        tkm2.lex_desc ten2,
        tkm3.lex_desc ten3,
        tkm4.lex_desc ten4,
        tkm5.lex_desc ten5,
        tkm6.lex_desc ten6,
        tkm7.lex_desc ten7,
        tkm8.lex_desc ten8,
        tkm9.lex_desc ten9,
        tkm10.lex_desc ten10


        FROM vin_model vm
        LEFT JOIN cats0_ucc cuc1 ON (cuc1.catalogue_code = vm.catalogue_code AND cuc1.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 1), '|', -1) AND cuc1.ucc_type = '01')
        LEFT JOIN lex_lex kig1 ON (kig1.lex_code = cuc1.lex_code1 AND kig1.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc2 ON (cuc2.catalogue_code = vm.catalogue_code AND cuc2.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 2), '|', -1) AND cuc2.ucc_type = '02')
        LEFT JOIN lex_lex kig2 ON (kig2.lex_code = cuc2.lex_code1 AND kig2.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc3 ON (cuc3.catalogue_code = vm.catalogue_code AND cuc3.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 3), '|', -1) AND cuc3.ucc_type = '03')
        LEFT JOIN lex_lex kig3 ON (kig3.lex_code = cuc3.lex_code1 AND kig3.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc4 ON (cuc4.catalogue_code = vm.catalogue_code AND cuc4.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 4), '|', -1) AND cuc4.ucc_type = '04')
        LEFT JOIN lex_lex kig4 ON (kig4.lex_code = cuc4.lex_code1 AND kig4.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc5 ON (cuc5.catalogue_code = vm.catalogue_code AND cuc5.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 5), '|', -1) AND cuc5.ucc_type = '05')
        LEFT JOIN lex_lex kig5 ON (kig5.lex_code = cuc5.lex_code1 AND kig5.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc6 ON (cuc6.catalogue_code = vm.catalogue_code AND cuc6.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 6), '|', -1) AND cuc6.ucc_type = '06')
        LEFT JOIN lex_lex kig6 ON (kig6.lex_code = cuc6.lex_code1 AND kig6.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc7 ON (cuc7.catalogue_code = vm.catalogue_code AND cuc7.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 7), '|', -1) AND cuc7.ucc_type = '07')
        LEFT JOIN lex_lex kig7 ON (kig7.lex_code = cuc7.lex_code1 AND kig7.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc8 ON (cuc8.catalogue_code = vm.catalogue_code AND cuc8.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 8), '|', -1) AND cuc8.ucc_type = '08')
        LEFT JOIN lex_lex kig8 ON (kig8.lex_code = cuc8.lex_code1 AND kig8.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc9 ON (cuc9.catalogue_code = vm.catalogue_code AND cuc9.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 9), '|', -1) AND cuc9.ucc_type = '09')
        LEFT JOIN lex_lex kig9 ON (kig9.lex_code = cuc9.lex_code1 AND kig9.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc10 ON (cuc10.catalogue_code = vm.catalogue_code AND cuc10.ucc = SUBSTRING_INDEX(vm.ucc, '|', -1) AND cuc10.ucc_type = '10')
        LEFT JOIN lex_lex kig10 ON (kig10.lex_code = cuc10.lex_code1 AND kig10.lang_code = 'EN')


        LEFT JOIN cats0_ucctype cut1 ON (cut1.catalogue_code = vm.catalogue_code AND cut1.ucc_type = '01')
        LEFT JOIN lex_lex tkm1 ON (tkm1.lex_code = cut1.lex_code AND tkm1.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut2 ON (cut2.catalogue_code = vm.catalogue_code AND cut2.ucc_type = '02')
        LEFT JOIN lex_lex tkm2 ON (tkm2.lex_code = cut2.lex_code AND tkm2.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut3 ON (cut3.catalogue_code = vm.catalogue_code AND cut3.ucc_type = '03')
        LEFT JOIN lex_lex tkm3 ON (tkm3.lex_code = cut3.lex_code AND tkm3.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut4 ON (cut4.catalogue_code = vm.catalogue_code AND cut4.ucc_type = '04')
        LEFT JOIN lex_lex tkm4 ON (tkm4.lex_code = cut4.lex_code AND tkm4.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut5 ON (cut5.catalogue_code = vm.catalogue_code AND cut5.ucc_type = '05')
        LEFT JOIN lex_lex tkm5 ON (tkm5.lex_code = cut5.lex_code AND tkm5.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut6 ON (cut6.catalogue_code = vm.catalogue_code AND cut6.ucc_type = '06')
        LEFT JOIN lex_lex tkm6 ON (tkm6.lex_code = cut6.lex_code AND tkm6.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut7 ON (cut7.catalogue_code = vm.catalogue_code AND cut7.ucc_type = '07')
        LEFT JOIN lex_lex tkm7 ON (tkm7.lex_code = cut7.lex_code AND tkm7.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut8 ON (cut8.catalogue_code = vm.catalogue_code AND cut8.ucc_type = '08')
        LEFT JOIN lex_lex tkm8 ON (tkm8.lex_code = cut8.lex_code AND tkm8.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut9 ON (cut9.catalogue_code = vm.catalogue_code AND cut9.ucc_type = '09')
        LEFT JOIN lex_lex tkm9 ON (tkm9.lex_code = cut9.lex_code AND tkm9.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut10 ON (cut10.catalogue_code = vm.catalogue_code AND cut10.ucc_type = '10')
        LEFT JOIN lex_lex tkm10 ON (tkm10.lex_code = cut10.lex_code AND tkm10.lang_code = :locale)

        WHERE vm.catalogue_code = :modificationCode
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('locale',  $locale);
        $query->execute();

        $aData = $query->fetchAll();

        $com = array();


        $result = array();
        $psevd = array();

        $af = array();

        foreach($aData as $item) {
            for ($i = 1; $i < 11; $i++) {
                if ($item['f' . $i]) {
                    $af[$i][$item['f' . $i]] = '(' . $item['f' . $i] . ') ' . $item['ken' . $i];
                    $result['f'.$i] = $af[$i];
                    $psevd['f'.$i] = iconv('Windows-1251', 'UTF-8', $item['ten' . $i]);

                }
            }


            $com[$item['model']] = array(
                Constants::NAME => $result,
                Constants::OPTIONS => array('option1'=>$psevd)
            );
        }

        return $com;
     
    }

    public function getComplectationsForForm($complectations)
    {
        $result = array();


        foreach ($complectations as $index => $value) {

            for ($i = 1; $i < 11; $i++) {

                if (!empty($value['name']['f' . $i])) {
                    foreach ($value['name']['f' . $i] as $ind => $val) {
                        $result['f' . $i]['name'][urlencode($ind)] = $val;
                    }
                }


                if (!empty($value['options']['option1']['f' . $i])) {
                    foreach ($value['options']['option1'] as $ind => $val) {
                        $result['f' . $i]['options']['option1'][urlencode($ind)] = $val;
                    }
                }


            }

        }

        return ($result);

    }

    public function getComplectationsFormValidValue($regionCode, $modelCode, $modificationCode, $selectorD, $priznakAllSelect)
    {
        empty($selectorD)?$selectorD = array():$selectorD = $selectorD;
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $pole = array();
        $pole1 = '';

        foreach ($selectorD as $index => $value)
        {
            $pole[$index] = "SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|',".str_replace('f', '', $index)."), '|', -1) = '".$value."'";
            $pole1 .= (($pole1 == '')?'':' AND ').$pole[$index];
        }


        $sql = "
        SELECT
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 1), '|', -1) as f1,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 2), '|', -1) as f2,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 3), '|', -1) as f3,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 4), '|', -1) as f4,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 5), '|', -1) as f5,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 6), '|', -1) as f6,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 7), '|', -1) as f7,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 8), '|', -1) as f8,
        SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 9), '|', -1) as f9,
        SUBSTRING_INDEX(vm.ucc, '|', -1) as f10,
        date_from, date_to, model,

        kig1.lex_desc ken1,
        kig2.lex_desc ken2,
        kig3.lex_desc ken3,
        kig4.lex_desc ken4,
        kig5.lex_desc ken5,
        kig6.lex_desc ken6,
        kig7.lex_desc ken7,
        kig8.lex_desc ken8,
        kig9.lex_desc ken9,
        kig10.lex_desc ken10,

        tkm1.lex_desc ten1,
        tkm2.lex_desc ten2,
        tkm3.lex_desc ten3,
        tkm4.lex_desc ten4,
        tkm5.lex_desc ten5,
        tkm6.lex_desc ten6,
        tkm7.lex_desc ten7,
        tkm8.lex_desc ten8,
        tkm9.lex_desc ten9,
        tkm10.lex_desc ten10


        FROM vin_model vm
        LEFT JOIN cats0_ucc cuc1 ON (cuc1.catalogue_code = vm.catalogue_code AND cuc1.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 1), '|', -1) AND cuc1.ucc_type = '01')
        LEFT JOIN lex_lex kig1 ON (kig1.lex_code = cuc1.lex_code1 AND kig1.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc2 ON (cuc2.catalogue_code = vm.catalogue_code AND cuc2.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 2), '|', -1) AND cuc2.ucc_type = '02')
        LEFT JOIN lex_lex kig2 ON (kig2.lex_code = cuc2.lex_code1 AND kig2.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc3 ON (cuc3.catalogue_code = vm.catalogue_code AND cuc3.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 3), '|', -1) AND cuc3.ucc_type = '03')
        LEFT JOIN lex_lex kig3 ON (kig3.lex_code = cuc3.lex_code1 AND kig3.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc4 ON (cuc4.catalogue_code = vm.catalogue_code AND cuc4.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 4), '|', -1) AND cuc4.ucc_type = '04')
        LEFT JOIN lex_lex kig4 ON (kig4.lex_code = cuc4.lex_code1 AND kig4.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc5 ON (cuc5.catalogue_code = vm.catalogue_code AND cuc5.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 5), '|', -1) AND cuc5.ucc_type = '05')
        LEFT JOIN lex_lex kig5 ON (kig5.lex_code = cuc5.lex_code1 AND kig5.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc6 ON (cuc6.catalogue_code = vm.catalogue_code AND cuc6.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 6), '|', -1) AND cuc6.ucc_type = '06')
        LEFT JOIN lex_lex kig6 ON (kig6.lex_code = cuc6.lex_code1 AND kig6.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc7 ON (cuc7.catalogue_code = vm.catalogue_code AND cuc7.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 7), '|', -1) AND cuc7.ucc_type = '07')
        LEFT JOIN lex_lex kig7 ON (kig7.lex_code = cuc7.lex_code1 AND kig7.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc8 ON (cuc8.catalogue_code = vm.catalogue_code AND cuc8.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 8), '|', -1) AND cuc8.ucc_type = '08')
        LEFT JOIN lex_lex kig8 ON (kig8.lex_code = cuc8.lex_code1 AND kig8.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc9 ON (cuc9.catalogue_code = vm.catalogue_code AND cuc9.ucc = SUBSTRING_INDEX(SUBSTRING_INDEX(vm.ucc, '|', 9), '|', -1) AND cuc9.ucc_type = '09')
        LEFT JOIN lex_lex kig9 ON (kig9.lex_code = cuc9.lex_code1 AND kig9.lang_code = 'EN')
        LEFT JOIN cats0_ucc cuc10 ON (cuc10.catalogue_code = vm.catalogue_code AND cuc10.ucc = SUBSTRING_INDEX(vm.ucc, '|', -1) AND cuc10.ucc_type = '10')
        LEFT JOIN lex_lex kig10 ON (kig10.lex_code = cuc10.lex_code1 AND kig10.lang_code = 'EN')


        LEFT JOIN cats0_ucctype cut1 ON (cut1.catalogue_code = vm.catalogue_code AND cut1.ucc_type = '01')
        LEFT JOIN lex_lex tkm1 ON (tkm1.lex_code = cut1.lex_code AND tkm1.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut2 ON (cut2.catalogue_code = vm.catalogue_code AND cut2.ucc_type = '02')
        LEFT JOIN lex_lex tkm2 ON (tkm2.lex_code = cut2.lex_code AND tkm2.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut3 ON (cut3.catalogue_code = vm.catalogue_code AND cut3.ucc_type = '03')
        LEFT JOIN lex_lex tkm3 ON (tkm3.lex_code = cut3.lex_code AND tkm3.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut4 ON (cut4.catalogue_code = vm.catalogue_code AND cut4.ucc_type = '04')
        LEFT JOIN lex_lex tkm4 ON (tkm4.lex_code = cut4.lex_code AND tkm4.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut5 ON (cut5.catalogue_code = vm.catalogue_code AND cut5.ucc_type = '05')
        LEFT JOIN lex_lex tkm5 ON (tkm5.lex_code = cut5.lex_code AND tkm5.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut6 ON (cut6.catalogue_code = vm.catalogue_code AND cut6.ucc_type = '06')
        LEFT JOIN lex_lex tkm6 ON (tkm6.lex_code = cut6.lex_code AND tkm6.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut7 ON (cut7.catalogue_code = vm.catalogue_code AND cut7.ucc_type = '07')
        LEFT JOIN lex_lex tkm7 ON (tkm7.lex_code = cut7.lex_code AND tkm7.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut8 ON (cut8.catalogue_code = vm.catalogue_code AND cut8.ucc_type = '08')
        LEFT JOIN lex_lex tkm8 ON (tkm8.lex_code = cut8.lex_code AND tkm8.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut9 ON (cut9.catalogue_code = vm.catalogue_code AND cut9.ucc_type = '09')
        LEFT JOIN lex_lex tkm9 ON (tkm9.lex_code = cut9.lex_code AND tkm9.lang_code = :locale)
        LEFT JOIN cats0_ucctype cut10 ON (cut10.catalogue_code = vm.catalogue_code AND cut10.ucc_type = '10')
        LEFT JOIN lex_lex tkm10 ON (tkm10.lex_code = cut10.lex_code AND tkm10.lang_code = :locale)

        WHERE vm.catalogue_code = :modificationCode
        AND $pole1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('locale',  $locale);
        $query->execute();
        $aData = $query->fetchAll();

        $complectations = array();
        $result = array();
        $af = array();
        foreach($aData as $item) {
            for ($i = 1; $i < 11; $i++) {
                if ($item['f' . $i]) {
                    $af[$i][$item['f' . $i]] = '(' . $item['f' . $i] . ') ' . $item['ken' . $i];
                    $result['f'.$i] = $af[$i];
                }
            }
        }
        foreach ($result as $index => $value) {
            $complectations[($index)] = $value;
        }
        if ($priznakAllSelect == 1){
            return ($aData[0]['model']);
        }
        else {
            return $complectations;
        }
    }
    /*public function getComplectations($regionCode, $modelCode, $modificationCode)
    {

        $sql = "
        SELECT model
        FROM vin_model
        WHERE vin_model.catalogue_code = :modificationCode
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();

        foreach($aData as $item){


            $complectations[$item['model']] = array(
                Constants::NAME => $item['model'],
                Constants::OPTIONS => array());

        }

        return $complectations;

    }*/

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $sql = "
        SELECT cm.major_sect, cm.major_sect_lex_code, UPPER(ll.lex_desc) TEXT
        FROM cats_map cm
        INNER JOIN lex_lex ll ON (ll.lex_code = cm.major_sect_lex_code AND ll.lang_code = :locale)
        WHERE cm.cat_folder = :catCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('locale',  $locale);
        $query->execute();
        $aData = $query->fetchAll();

        $groups = array();

        foreach($aData as $item){
            $groups[$item['major_sect']] = array(
                Constants::NAME     => iconv('Windows-1251', 'UTF-8', $item['TEXT']),
                Constants::OPTIONS  => array()
            );
        }

        return $groups;
    }

    public function getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $sqlNumPrigroup = "
        SELECT cdr.img_name
        FROM cats_dat_ref cdr
        INNER JOIN cats_map cm ON (cm.cat_folder = cdr.cat_folder AND cm.major_sect = :groupCode AND cm.sector = cdr.ref)
        WHERE cdr.cat_folder = :catCode
        AND cdr.ref_type = '3'
        ";
        $query = $this->conn->prepare($sqlNumPrigroup);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();
        $aData = $query->fetchAll();

        $groupSchemas = array();
        foreach ($aData as $item) {
            $groupSchemas[$item['img_name']] = array(Constants::NAME => $item['img_name'], Constants::OPTIONS => array());
        }
        return $groupSchemas;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $modificationCode = substr($modificationCode, 0, strpos($modificationCode, '_'));

        $sql = "
        SELECT cm.minor_sect, cm.sector_format, cm.compatibility, cm.sector_part, UPPER(IFNULL(ll_locale.lex_desc, ll_en.lex_desc)) TEXT, ll_note.lex_desc note,
        cdr.x1, cdr.x2, cdr.y1, cdr.y2, cdr.img_name
        FROM cats_map cm
        LEFT JOIN lex_lex ll_locale ON (ll_locale.lex_code = cm.minor_sect_lex_code AND ll_locale.lang_code = :locale)
        LEFT JOIN lex_lex ll_en ON (ll_en.lex_code = cm.minor_sect_lex_code AND ll_en.lang_code = 'EN')
        LEFT JOIN lex_lex ll_note ON (ll_note.lex_code = cm.note_lex_code AND ll_note.lang_code = 'EN')
        INNER JOIN cats_dat_ref cdr ON (cdr.cat_folder = :catCode AND cdr.ref_type = '3' AND cdr.ref = cm.sector)
        WHERE cm.cat_folder = :catCode
        AND cm.major_sect = :groupCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('locale',  $locale);
        $query->execute();
        $aData = $query->fetchAll();


        /*Фильтр совместимости с выбранной комплектацией*/
        $sqlComlectation = "
        SELECT DISTINCT ucc
        FROM vin_model vm
        WHERE vm.catalogue_code = :modificationCode
        AND vm.model = :complectationCode
        ";
        $query = $this->conn->prepare($sqlComlectation);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('complectationCode',  $complectationCode);
        $query->execute();
        $aComplectation = $query->fetch();

        $aExplodeComplectation = explode('|', $aComplectation['ucc']);
        foreach($aData as $index => $value){
            if ($value['compatibility'])
            {
                $aNull = array('', ';', ';;');
                $aExplodeCompatibility = explode('|', str_replace(';', '', $value['compatibility']));
                $aExplodeCompatibility = array_slice($aExplodeCompatibility, 0 , count($aExplodeComplectation)-1);
                $aExplodeCompatibilityWithoutNull = array_diff($aExplodeCompatibility, $aNull);
                $aIntersect = array_intersect_assoc($aExplodeComplectation, $aExplodeCompatibilityWithoutNull);
                if (count($aIntersect) != count($aExplodeCompatibilityWithoutNull))
                {
                    unset ($aData[$index]);
                }
                unset ($value);
            }
        }
        /*конец фильтра*/

        /*для того, чтобы координаты подгрупп не дублировались и не мешали правильно отображать картинку (области не накладывались друг на друга)*/
        $aSectorFormat = array();
        $aCountValuesSectorFormat = array();
        foreach($aData as $index => $value)
        {
            $aSectorFormat[] = $value['sector_format'];
        }

        $aCountValuesSectorFormat = array_count_values($aSectorFormat);var_dump($aCountValuesSectorFormat); die;

        $subgroups = array();
            foreach($aData as $item){
                    $subgroups[$item['minor_sect']] = array(
                        Constants::NAME     => iconv('Windows-1251', 'UTF-8', $item['TEXT']),
                        Constants::OPTIONS  => array(
                            'picture' => $item['img_name'],
                            'sector_format' => $item['sector_format'],
                            'sector_part' => $item['sector_part'],
                            'note' => $item['note'],
                            Constants::X1 => $aCountValuesSectorFormat[$item['sector_format']]<= 1?floor($item['x1']):null,
                            Constants::X2 => $aCountValuesSectorFormat[$item['sector_format']]<= 1?floor($item['x2']):null,
                            Constants::Y1 => $aCountValuesSectorFormat[$item['sector_format']]<= 1?floor($item['y1']):null,
                            Constants::Y2 => $aCountValuesSectorFormat[$item['sector_format']]<= 1?floor($item['y2']):null
                        )
                    );
            }
        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));
        $schemas = array();
		            $schemas[$subGroupCode] = array(
                    Constants::NAME => $catCode,
                    Constants::OPTIONS => array()
                );
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
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $sqlPnc = "
        SELECT cdp.*, UPPER(IFNULL(ll_locale.lex_desc, ll_en.lex_desc)) TEXT, ll_desc.lex_desc descript
        FROM cats_dat_parts cdp
        LEFT JOIN lex_lex ll_locale ON (ll_locale.lex_code = cdp.name_lex_code AND ll_locale.lang_code = :locale)
        LEFT JOIN lex_lex ll_en ON (ll_en.lex_code = cdp.name_lex_code AND ll_en.lang_code = 'EN')
        LEFT JOIN lex_lex ll_desc ON (ll_desc.lex_code = cdp.desc_lex_code AND ll_desc.lang_code = 'EN')
        WHERE cdp.cat_folder = :catCode
        AND cdp.minor_sect LIKE :schemaCode
        ";
    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('locale',  $locale);
        $query->execute();
        $aPncs = $query->fetchAll();
    	
    	foreach ($aPncs as &$aPnc){
            $sqlSchemaLabels = "
            SELECT x1, y1, x2, y2
            FROM cats_dat_ref
            WHERE cat_folder = :catCode
            AND img_name = :schemaCode
            AND ref = :ref
            ";
            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('catCode', $catCode);
            $query->bindValue('schemaCode', $schemaCode);
            $query->bindValue('ref', $aPnc['ref']);
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
                    $pncs[$value['ref']][Constants::OPTIONS][Constants::COORDS][$item1['x1']] = array(
                    Constants::X1 => floor(($item1['x1'])),
                    Constants::Y1 => $item1['y1'],
                    Constants::X2 => $item1['x2'],
                    Constants::Y2 => $item1['y2']);
            	}
            }
        }
        foreach ($aPncs as $item) {
            $pncs[$item['ref']][Constants::NAME] = iconv('Windows-1251', 'UTF-8', $item['TEXT']);
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
        /*$catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));


        $sqlSchemaLabels = "
        SELECT name, x1, y1, x2, y2
        FROM cats_coord
        WHERE catalog_code = :catCode
          AND compl_name LIKE :schemaCode
          AND quantity = 5
          ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('schemaCode', '%'.$schemaCode.'%');
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

        $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));

        $ghg = $this->getComplectations($regionCode, $modelCode, $modificationCode);/*print_r($ghg[$complectationCode]['options']['option2']); die;*/
        $complectationOptions = $ghg[$complectationCode]['options']['option2'];

        $sqlPnc = "
        SELECT *
        FROM cats_table
        WHERE catalog_code =:catCode
            AND scheme_num = :pncCode
        	AND compl_name LIKE :schemaCode
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('catCode', $catCode);
        $query->bindValue('pncCode', $pncCode);
        $query->bindValue('schemaCode', '%'.$options['cd'].'%');
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
                    FROM kialex
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
                    FROM kialex
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
        $query->bindValue('subGroupCode', (substr_count($subGroupCode, '-') > 1) ? substr($subGroupCode, 0, strripos($subGroupCode, '-')+1): $subGroupCode);
        $query->bindValue('catCode', $catCode);
        $query->execute();

        $groupCode = $query->fetchColumn(0);

        return $groupCode;

    }

    
} 