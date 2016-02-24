<?php
namespace Catalog\SmartBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;

class SmartCatalogModel extends CatalogModel{

    public function getRegions()
    {
        $regions = array();

        $sqlRegions = "
        SELECT
            group_concat(APPINF SEPARATOR '') string
        FROM
            (SELECT DISTINCT
                trim(' ' FROM APPINF) APPINF
            FROM
                alltext_models_v) t;
        ";

        $query = $this->conn->query($sqlRegions);

        $sData = $query->fetchColumn(0);

        $aData = array_unique(str_split($sData));

        foreach ($aData as $item) {
            if (($item == "M")) {
                $regions[$item] = array(
                    Constants::NAME => 'region_' . $item,
                    Constants::OPTIONS => array()
                );
            }
        }

        return $regions;
    }

    public function getModels($regionCode)
    {
        $sqlModels = "
        SELECT DISTINCT
            amv.CLASS
        FROM
            alltext_models_v amv
        WHERE amv.APPINF LIKE :regionCode;
        ";

        $query = $this->conn->prepare($sqlModels);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->execute();

        $aData = $query->fetchAll();
        $models = array();

        foreach ($aData as $item) {
            $models[$item['CLASS']] = array(
                Constants::NAME => 'model_' . $item['CLASS'],
                Constants::OPTIONS => array()
            );
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sqlModifications = "
        SELECT DISTINCT
            AGGTYPE,
            descr.AGGTYPE_DESC
        FROM
            mercedesbenz.alltext_models_v models
        LEFT JOIN alltext_aggtype_code_map_v descr ON TRIM(models.AGGTYPE) = TRIM(descr.DAG_AGGTYPE_CODE)
        WHERE models.APPINF LIKE :regionCode AND models.CLASS = :modelCode;
        ";

        $query = $this->conn->prepare($sqlModifications);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();

        foreach ($aData as $item) {
            $modifications[$item['AGGTYPE']] = array(
                Constants::NAME => $item['AGGTYPE_DESC'],
                Constants::OPTIONS => array()
            );
        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {
        $sqlComplectations = "
        SELECT DISTINCT
            IF (models.SUBBM2 != '', concat(models.TYPE, '.', models.SUBBM1, '-', models.SUBBM2), concat(models.TYPE, '.', models.SUBBM1)) COMPLECTATION,
            models.SALESDES TRADEMARK,
            models.CATNUM,
            models.REMARKS
        FROM
            alltext_models_v as models
        WHERE
            models.APPINF LIKE :regionCode
            AND models.CLASS = :modelCode
            AND models.AGGTYPE = :modificationCode
        ";

        $query = $this->conn->prepare($sqlComplectations);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();

        foreach ($aData as $item) {
            $complectations[$item['CATNUM'] . "." . $item['COMPLECTATION']][Constants::NAME] = $item['TRADEMARK'];
            $complectations[$item['CATNUM'] . "." . $item['COMPLECTATION']][Constants::OPTIONS]['REMARKS'] = $item['REMARKS'];
        }
        return $complectations;
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $complectationCode = substr($complectationCode, 0, 3);
        $sqlGroups = "
            SELECT
                GROUPS.GROUPNUM, IFNULL(DIC_RU.TEXT, DIC_EN.TEXT) DESCR
            FROM
                alltext_bm_group_v GROUPS
                LEFT OUTER JOIN alltext_bm_dictionary_v DIC_RU
                    ON DIC_RU.DESCIDX = GROUPS.DESCIDX
                    AND DIC_RU.LANG = 'R'
                LEFT OUTER JOIN alltext_bm_dictionary_v DIC_EN
                    ON DIC_EN.DESCIDX = GROUPS.DESCIDX
                    AND DIC_EN.LANG = 'E'
            WHERE
                GROUPS.CATNUM = :complectationCode
            ORDER BY
                GROUPS.GROUPNUM
        ";

        $query = $this->conn->prepare($sqlGroups);
        $query->bindValue('complectationCode', $complectationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();
        foreach ($aData as $item) {
            $groups[$item['GROUPNUM']] = array(
                Constants::NAME => iconv('Windows-1251', 'UTF-8', $item['DESCR'])
            );
        }

        return $groups;
    }

    /*
     * Классификация такова, что весь автомобиль разбит на агрегаты, агрегаты имеют группы, группы имеют подгруппы.
     * Все агрегаты, кроме "шасси", имеют только группы. Шасси может иметь группы и другие агрегаты. Т.е. на уровне групп
     * для агрегата "шасси" может быть агрегат "двигатель".
     * Метод предназначен для поиска "вложенных" агрегатов для агрегата "шасси".
     */
    public function getComplectationAgregats($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $catnum = substr($complectationCode, 0, 3);
        $model = substr($complectationCode, 4);

        $sqlAggregates = "
        SELECT
            FRONTAX `VA`,
            STEER `LG`,
            REARAX `HA`,
            A_BODY `FH`,
            ENGINE `M`,
            PLATFRM `P`,
            AUTO `GA`,
            MANUAL `GM`,
            EXHAUSTSYS `AS`,
            EMOTOR `E`,
            HVBATTERY `B`,
            FUELCELL `N`
        FROM
            alltext_bm_spm_v details
        WHERE
            CATNUM = :complectationCode AND MODEL = :model;
        ";

        $query = $this->conn->prepare($sqlAggregates);
        $query->bindValue('complectationCode', $catnum);
        $query->bindValue('model', $model);
        $query->execute();

        $aData = $query->fetchAll();

        $slqAggTypes = "
        SELECT DAG_AGGTYPE_CODE, AGGTYPE_DESC
        FROM alltext_aggtype_code_map_v
        ";

        $aAggData = $this->conn->executeQuery($slqAggTypes)->fetchAll();
        $aAggs = array_combine($this->array_column($aAggData, 'DAG_AGGTYPE_CODE'), $this->array_column($aAggData, 'AGGTYPE_DESC'));

        $aggregates = array();
        foreach ($aData as $item) {
            foreach ($item as $key => $value) {
                if ($value) {
                    $aggregates[$key] = array(
                        Constants::NAME => $aAggs[$key],
                    );
                    $string = $value;
                    while ($string) {
                        $type = substr($string, 0, 3);
                        $string = substr($string, 4);
                        $subtypes = substr_count($string, ".") ? substr($string, 0, strpos($string, ".") - 3) : $string;
                        $string = substr($string, strlen($subtypes));
                        foreach (str_split(str_replace(array(',', ' ', '-'), '', $subtypes), 3) as $subtype) {
                            $aggregates[$key][Constants::OPTIONS]['COMPLECTATIONS'][] = $type . "." . $subtype;
                        }
                    }

                    foreach ($aggregates[$key][Constants::OPTIONS]['COMPLECTATIONS'] as $complKey=>$complVal) {
                        $aCatnum = $this->getCatnum($regionCode, $modelCode, $key, $complVal);
                        $code = $complVal;
                        unset($aggregates[$key][Constants::OPTIONS]['COMPLECTATIONS'][$complKey]);
                        foreach ($aCatnum as $catnum) {
                            if ($catnum['CATNUM']) {
                                $aggregates[$key][Constants::OPTIONS]['COMPLECTATIONS'][$catnum['CATNUM'] . "." . $code] =  $catnum['SALESDES'];
                            }
                        }
                        /*
                         * Удаляем те агрегаты, для комплектации которых не найдены (например, type, submm есть, а catnum пуст),
                         * поскольку они все равно отобразятся с ошибкой
                         */
                        if (!$aggregates[$key][Constants::OPTIONS]['COMPLECTATIONS']) {
                            unset($aggregates[$key]);
                        }
                    }
                }
            }
        }

        return $aggregates;
    }

    public function getCatnum($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $type = substr($complectationCode, 0, 3);
        $sub1 = substr($complectationCode, 4, 3);
        $sub2 = substr($complectationCode, 8, 3) ?: '';

        $sqlCatnum = "
        SELECT DISTINCT CATNUM, SALESDES
        FROM alltext_models_v
        WHERE APPINF LIKE :regionCode
          AND AGGTYPE = :modelCode
          AND TYPE = :mtype
          AND SUBBM1 = :sub1
          AND SUBBM2 = :sub2
        ";

        $query = $this->conn->prepare($sqlCatnum);
        $query->bindValue('regionCode', '%' . $regionCode . '%');
        $query->bindValue('modelCode', $modificationCode);
        $query->bindValue('mtype', $type);
        $query->bindValue('sub1', $sub1);
        $query->bindValue('sub2', $sub2);
        $query->execute();

        $catnum = $query->fetchAll();

        return $catnum;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $sqlBmSubGroups = "
        select BM_SG_E.GROUPNUM, BM_SG_E.SUBGRP, IFNULL(BM_SG_R.TEXT, BM_SG_E.TEXT) TEXT
        from alltext_bm_subgrp_v BM_SG_E
            LEFT OUTER JOIN alltext_bm_subgrp_v BM_SG_R
                ON BM_SG_R.CATNUM = BM_SG_E.CATNUM
                AND BM_SG_R.GROUPNUM = BM_SG_E.GROUPNUM
                AND BM_SG_R.SUBGRP = BM_SG_E.SUBGRP
                AND BM_SG_R.LANG = 'R'
        where BM_SG_E.CATNUM = :complectationCode /* Выбранный каталог */
                AND BM_SG_E.GROUPNUM = :groupCode /* выбранная группа */
                AND BM_SG_E.LANG = 'E'
        order by BM_SG_E.SUBGRP
        ";

        $query = $this->conn->prepare($sqlBmSubGroups);
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aDataBM = $query->fetchAll();

        $subgroupsBM = array();
        foreach ($aDataBM as $item) {
            $subgroupsBM[$item['SUBGRP']] = array(
                Constants::NAME => iconv('Windows-1251', 'UTF-8', $item['TEXT']),
                Constants::OPTIONS => array()
            );
        }

        $subgroups = $subgroupsBM;

        return $subgroups;
    }

    public function getSaSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $sqlSaSubGroups = "
        select sa.SANUM,  ifnull(desc_r.TEXT, desc_e.TEXT) TEXT, sa.CODEONE,  sa.CODETWO
        from alltext_bm_saidx_v sa
         left outer join alltext_sa_dictionary_v desc_e
            ON desc_e.DESCIDX = sa.DESCIDX
            AND desc_e.LANG = 'E'
            left outer join alltext_sa_dictionary_v desc_r
            ON desc_r.DESCIDX = sa.DESCIDX
            AND desc_r.LANG = 'R'
        where sa.CATNUM = :complectationCode
          AND sa.GROUPNUM = :groupCode
        order by SANUM
        ";

        $query = $this->conn->prepare($sqlSaSubGroups);
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aDataSA = $query->fetchAll();

        $subgroupsSA = array();
        foreach ($aDataSA as $item) {
            $subgroupsSA[trim($item['SANUM'])] = array(
                Constants::NAME => iconv('Windows-1251', 'UTF-8', $item['TEXT']),
                Constants::OPTIONS => array(
                    'CODEONE' => $item['CODEONE'],
                    'CODETWO' => $item['CODETWO'],
                )
            );
        }

        return $subgroupsSA;
    }

    public function getSaFirstLevelSubgroups($complectationCode, $sanum)
    {
        $sqlSaSubGroups = "
        select sa.STRKVER,  ifnull(desc_r.TEXT, desc_e.TEXT) TEXT
        from alltext_sa_strokes_v sa
         left outer join alltext_sa_dictionary_v desc_e
            ON desc_e.DESCIDX = sa.DESCIDX
            AND desc_e.LANG = 'E'
            left outer join alltext_sa_dictionary_v desc_r
            ON desc_r.DESCIDX = sa.DESCIDX
            AND desc_r.LANG = 'R'
        where
        sa.SANUM = :sanum
        order by sa.SANUM
        ";

        $query = $this->conn->prepare($sqlSaSubGroups);
        $query->bindValue('sanum', str_pad($sanum, 6, " ", STR_PAD_LEFT));
        $query->execute();

        $aData = $query->fetchAll();

        $saFirstLevelSubgroups = array();

        foreach ($aData as $item) {
            $saFirstLevelSubgroups[$item['STRKVER']] = array(
                Constants::NAME => $item['TEXT'] == '' ? '---' : iconv('Windows-1251', 'UTF-8', $item['TEXT'])
            );
        }

        return $saFirstLevelSubgroups;
    }

    public function getSaSchemas($sanum)
    {
        $sqlSaSchemas = "
            select CONCAT(IMGTYPE,SANUM,SEQNO,RESTIMG) IMG,
              CONTREC,
              CALLOUT,
              SUBGRP,
              SEQNUM
            from alltext_sa_npg_v
            where SANO = :sanum
            ORDER BY SEQNUM
        ";

        $query = $this->conn->prepare($sqlSaSchemas);
        $query->bindValue('sanum', str_pad($sanum, 6, " ", STR_PAD_LEFT));
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();

        foreach ($aData as $item) {
            $schemas[$item['IMG']] = array(
                Constants::NAME => $item['IMG']
            );
        }

        return $schemas;
    }

    public function getSaPncs($sanum, $schemaCode)
    {
        /**
         * Выбираем метки, которые принадлежат группе sanum (пока без привязки к рисункам)
         */
        $sqlSaPncs = "
        SELECT
          CAST(ITEMNO AS UNSIGNED) ITEMNO
        FROM `alltext_sa_parts_v` parts
        WHERE SANUM = :sanum AND ITEMNO != ''
        GROUP BY ITEMNO
        ";

        $query = $this->conn->prepare($sqlSaPncs);
        $query->bindValue('sanum', str_pad($sanum, 6, " ", STR_PAD_LEFT));
        $query->execute();

        $aSanumPncs = $this->array_column($query->fetchAll(), 'ITEMNO');

        /**
         * Выбираем метки на рисунке, которые входят в список принадлежащих группе sanum
         */
        $sqlImageSaPncs = "
           (select image_name, x, y, `desc` as pnc from sa_images_arc_image_v where image_name = ? AND `desc` IN (?))
            UNION
            (select image_name, x, y, `desc` as pnc from sa_images_image_v where image_name = ? AND `desc` IN (?))
            order by CAST(pnc AS UNSIGNED)
        ";

        $query = $this->conn->executeQuery($sqlImageSaPncs, array(
            $schemaCode,
            $aSanumPncs,
            $schemaCode,
            $aSanumPncs
        ), array(
            \PDO::PARAM_STR,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
            \PDO::PARAM_STR,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aPncs = $query->fetchAll();

        $pncs = array();
        foreach ($aPncs as $item) {
            if ($item) {
                $pncCode = str_pad($item['pnc'], 3, '0', STR_PAD_LEFT);
//                if (in_array($pncCode, explode(" ", $aData['CALLOUT']))) {
                    $pncs[$pncCode][Constants::NAME] = iconv('Windows-1251', 'UTF-8', $this->getSaPncName($sanum, (string) $pncCode)) ;
                    $pncs[$pncCode][Constants::OPTIONS][Constants::COORDS][] = array(
                        Constants::X1 => $item['x'] - 8,
                        Constants::Y1 => $item['y'] - 12,
                        Constants::X2 => $item['x'] + 15,
                        Constants::Y2 => $item['y'] + 8);
//                }
            }
        }
        return $pncs;
    }

    public function getSaCommonArticuls($sanum)
    {
        $sqlSaCommonArticuls = "
        SELECT `PARTTYP`, `PARTNUM`, parts.`QUANTSA`, IFNULL(nouns_ru.NOUN, nouns_en.NOUN) TEXT
        FROM `alltext_sa_parts_v` parts
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_ru
        ON nouns_ru.NOUNIDX = parts.NOUNIDX AND nouns_ru.LANG = 'R'
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_en
        ON nouns_en.NOUNIDX = parts.NOUNIDX AND nouns_en.LANG = 'E'
        WHERE `SANUM` = :sanum
        AND `ITEMNO` = ''
        ";

        $query = $this->conn->prepare($sqlSaCommonArticuls);
        $query->bindValue('sanum', str_pad($sanum, 6, " ", STR_PAD_LEFT));
        $query->execute();

        $saCommonArticuls = $query->fetchAll();
        $articuls = array();
        foreach ($saCommonArticuls as $item) {
            if ($item) {
                $articuls[$item['PARTTYP'] . $item['PARTNUM']][Constants::OPTIONS][Constants::COORDS][] = array(
                    Constants::X1 => 0,
                    Constants::Y1 => 0,
                    Constants::X2 => 0,
                    Constants::Y2 => 0
                );
            }
        }

        return $articuls;
    }

    public function getSaPncName($sanum, $pncCode)
    {
        $sqlPncName = "
        SELECT IFNULL(nouns_ru.NOUN, nouns_en.NOUN) TEXT
        FROM `alltext_sa_parts_v` parts
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_ru
        ON nouns_ru.NOUNIDX = parts.NOUNIDX AND nouns_ru.LANG = 'R'
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_en
        ON nouns_en.NOUNIDX = parts.NOUNIDX AND nouns_en.LANG = 'E' OR nouns_en.LANG = 'N'
        WHERE `SANUM` = :sanum
        AND `ITEMNO` = :pnc
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlPncName);
        $query->bindValue('sanum', str_pad($sanum, 6, " ", STR_PAD_LEFT));
        $query->bindValue('pnc', str_pad($pncCode, 3, "0", STR_PAD_LEFT));
        $query->execute();

        $sData = $query->fetchColumn();

        return $sData;
    }

    public function getSaArticuls($sanum, $pncCode)
    {
        $sqlSaCommonArticuls = "
        SELECT `PARTTYP`, `PARTNUM`, parts.`QUANTSA`, IFNULL(nouns_ru.NOUN, nouns_en.NOUN) TEXT
        FROM `alltext_sa_parts_v` parts
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_ru
        ON nouns_ru.NOUNIDX = parts.NOUNIDX AND nouns_ru.LANG = 'R'
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_en
        ON nouns_en.NOUNIDX = parts.NOUNIDX AND nouns_en.LANG = 'E'
        WHERE `SANUM` = :sanum
        AND `ITEMNO` = :pncCode
        ";

        $query = $this->conn->prepare($sqlSaCommonArticuls);
        $query->bindValue('sanum', str_pad($sanum, 6, " ", STR_PAD_LEFT));
        $query->bindValue('pncCode', str_pad($pncCode, 3, "0", STR_PAD_LEFT));
        $query->execute();

        $saCommonArticuls = $query->fetchAll();
        $articuls = array();
        foreach ($saCommonArticuls as $item) {
            if ($item) {
                $articuls[$item['PARTTYP'] . $item['PARTNUM']] = array(
                    Constants::NAME => iconv('Windows-1251', 'UTF-8', $item['TEXT']),
                    Constants::OPTIONS => array(
                        Constants::QUANTITY => substr($item['QUANTSA'], 0, 3),
                        Constants::START_DATE => '00000000',
                        Constants::END_DATE => '99999999'
                    )
                );
            }
        }

        return $articuls;
    }

    /**
     * Метод-заглушка
     * @return array
     */
    public function getGroupSchemas()
    {
        return array();
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $sqlSchemas = "
            select
                CONTREC,
                CALLOUT,
                seqnum,
                CONCAT(IMGTYPE,
                        GROUPNUM,
                        SUBGRP,
                        SEQNO,
                        RESTIMG) IMAGE_CODE
            from
                alltext_bm_npg_v
            where
                CATNUM = :complectationCode
                AND GROUPNUM = :groupCode
                AND SUBGRP = :subGroupCode
            ORDER BY SEQNUM
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        foreach ($aData as $item) {
            $schemas[$item['IMAGE_CODE']] = array(
                Constants::NAME => $item['IMAGE_CODE'],
                Constants::OPTIONS => array(
                    'seqnum' => $item['seqnum']
                )
            );
        }
        return $schemas;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {
        $sqlLabels = "
            select
                CALLOUT,
                CONCAT(IMGTYPE,
                        GROUPNUM,
                        SUBGRP,
                        SEQNO,
                        RESTIMG) IMAGE_CODE
            from
                alltext_bm_npg_v
            where
                CATNUM = :complectationCode
                AND GROUPNUM = :groupCode
                AND SUBGRP = :subGroupCode
                HAVING IMAGE_CODE =:schemaCode
        ";

        $query = $this->conn->prepare($sqlLabels);
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aData = $query->fetch();
//        var_dump($aData);die;
//        $schemaCode = $aData['IMAGE_CODE'];
//        $aLabels = explode(" ", $aData['CALLOUT']);

        $sqlPncs = "
        (select image_name, x, y, `desc` as pnc from bm_images_arc_image_v where image_name = :schemaCode)
        UNION
        (select image_name, x, y, `desc` as pnc from bm_images_image_v where image_name = :schemaCode)
        order by CAST(pnc AS UNSIGNED)
        ";

        $query = $this->conn->prepare($sqlPncs);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aPncs = $query->fetchAll();
        $pncs = array();
        foreach ($aPncs as $item) {
            if ($item) {
                $pncCode = str_pad($item['pnc'], 3, '0', STR_PAD_LEFT);
                if (in_array($pncCode, explode(" ", $aData['CALLOUT']))) {
                    $pncs[$pncCode][Constants::NAME] = iconv('Windows-1251', 'UTF-8', $this->getPncName($complectationCode, $groupCode, $subGroupCode, (string) $pncCode)) ;
                    $pncs[$pncCode][Constants::OPTIONS][Constants::COORDS][] = array(
                        Constants::X1 => $item['x'] - 8,
                        Constants::Y1 => $item['y'] - 12,
                        Constants::X2 => $item['x'] + 15,
                        Constants::Y2 => $item['y'] + 8);
                }
            }
        }

        return $pncs;
    }

    private function getPncName($complectationCode, $groupCode, $subGroupCode, $pncCode)
    {
        $sqlPncName = "
        SELECT IFNULL(nouns_ru.NOUN, nouns_en.NOUN) TEXT
        FROM `alltext_bm_parts2_v` parts
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_ru
        ON nouns_ru.NOUNIDX = parts.NOUNIDX AND nouns_ru.LANG = 'R'
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_en
        ON nouns_en.NOUNIDX = parts.NOUNIDX AND nouns_en.LANG = 'E'
        WHERE `CATNUM` = :complectationCode
        AND `GROUPNUM` = :groupCode
        AND `SUBGRP` = :subGroupCode
        AND `CALLOUT` = :pnc
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlPncName);
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pnc', $pncCode);
        $query->execute();

        $sData = $query->fetchColumn();

        return $sData;
    }

    public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {
        $sqlArticuls = "
        SELECT `PARTTYPE`, `PARTNUM`, parts.`QUANTBM`, IFNULL(nouns_ru.NOUN, nouns_en.NOUN) TEXT
        FROM `alltext_bm_parts2_v` parts
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_ru
        ON nouns_ru.NOUNIDX = parts.NOUNIDX AND nouns_ru.LANG = 'R'
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_en
        ON nouns_en.NOUNIDX = parts.NOUNIDX AND nouns_en.LANG = 'E'
        WHERE `CATNUM` = :complectationCode
        AND `GROUPNUM` = :groupCode
        AND `SUBGRP` = :subGroupCode
        AND `CALLOUT` = ''
        ";

        $query = $this->conn->prepare($sqlArticuls);
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();


        $articuls = array();
        foreach ($aData as $item) {
            if ($item) {
                $articuls[$item['PARTTYPE'] . $item['PARTNUM']][Constants::OPTIONS][Constants::COORDS][] = array(
                    Constants::X1 => 0,
                    Constants::Y1 => 0,
                    Constants::X2 => 0,
                    Constants::Y2 => 0
                );
            }
        }

        return $articuls;
    }

    public function getReferGroups()
    {
        $referGroups = array();
        return $referGroups;
    }

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode, $options)
    {
        $sqlArticuls = "
        SELECT parts.`PARTTYPE`, parts.`PARTNUM`, parts.`CODEB`, parts.`QUANTBM`, IFNULL(nouns_ru.NOUN, nouns_en.NOUN) TEXT
        FROM `alltext_bm_parts2_v` parts
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_ru
        ON nouns_ru.NOUNIDX = parts.NOUNIDX AND nouns_ru.LANG = 'R'
        LEFT OUTER JOIN `alltext_part_nouns_v` nouns_en
        ON nouns_en.NOUNIDX = parts.NOUNIDX AND nouns_en.LANG = 'E'
        WHERE `CATNUM` = :complectationCode
        AND `GROUPNUM` = :groupCode
        AND `SUBGRP` = :subGroupCode
        AND `CALLOUT` = :pncCode
        ";

        $query = $this->conn->prepare($sqlArticuls);
        $query->bindValue('complectationCode', substr($complectationCode, 0, 3));
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pncCode', str_pad($pncCode, 3, "0", STR_PAD_LEFT));
        $query->execute();

        $aData = $query->fetchAll();

        $articuls = array();
        foreach ($aData as $item) {
            $articuls[$item['PARTTYPE'] . $item['PARTNUM']] = array(
                Constants::NAME => iconv('Windows-1251', 'UTF-8', $item['TEXT']),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => substr($item['QUANTBM'], 0, 3),
                    Constants::START_DATE => '00000000',
                    Constants::END_DATE => '99999999',
                    'CODEB' => $item['CODEB']
                )
            );
        }

        return $articuls;
    }
} 