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
        SELECT auto_name
        FROM feuc
        ORDER BY auto_name
        ";


        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item) {

            $mod = $item['auto_name'];
            if (stripos($item['auto_name'], ' ') !== false && $item['auto_name'] != 'Fluids and Maintenance Products')
            {
                $mod = strtoupper(substr($item['auto_name'], 0 ,stripos($item['auto_name'], ' ')));
            }

            $models[urlencode($mod)] = array(Constants::NAME => $mod,
                Constants::OPTIONS => array());

        }


        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = rawurldecode($modelCode);
        $dir = 'bundles/catalogford/Images';

        /*Считывание имен картинок всех моделей в искомой папке, их обработка*/
        $aFiles = scandir($dir, 1);

        foreach($aFiles as $index => $aFile){
            $aPicture = explode('-', $aFile);
            if ((stripos($aFile, '.png') == false)||((strlen($aPicture[0])>2)))
            {
                unset($aFiles[$index]);
            }
        }

        $aOnlyName = array();

        foreach($aFiles as $index => $aFile){
            $aPicture = explode('.', $aFile);
            $aOnlyName[$index] = $aPicture[0];
        }

        $sql  = "
        SELECT f.*, coordinates_names.num_index
        FROM feuc f
        INNER JOIN coordinates_names ON (coordinates_names.model_id = f.model_id AND group_detail_sign LIKE '1'
        AND coordinates_names.num_model_group LIKE '0')
        WHERE SUBSTRING_INDEX(f.auto_name, ' ', 1) = :modelCode
        ORDER BY auto_name
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();

        foreach($aOnlyName as $index => $aFile) {
            foreach($aData as &$item){
                if ((stripos($aFile, $item['engine_type']) !== false) || (substr($aFile,-2) == substr($item['engine_type'],-2)))
                {
                    $item['picture'] = $aFiles[$index];
                    $item['folder'] = explode('-', $aFile)[1];

                }
                unset($item);
            }
        }

        foreach($aData as $item){
            {
                $modifications[$item['model_id'].'_'.$item['auto_code'].'_'.$item['engine_type']] = array(
                    Constants::NAME     => $item['auto_name'],
                    Constants::OPTIONS  => array(
                        'grafik' => $item['picture'],
                        'group_picture' => $item['num_index'],
                        'folder' => $item['folder']
                        ));
            }
        }

        return $modifications;
    }

    /**
     Функция getComplectations() учитывает все положения руля и даты производства модификаций. Используется только в каталог_контроллере коммонБандла.
     */

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $modificationCode = explode('_', $modificationCode);

        $sql = "
        SELECT DISTINCT CONCAT(
              CASE
              WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(f.params, '!', 43) , '!', -1) = '1'
              THEN 'GC'
              ELSE 'CT'
              END , SUBSTRING(f.rigidity, -2, 2)) model_auto_f
        FROM feu.feuc f
        WHERE CONCAT(',', f.auto_code, ',') LIKE CONCAT('%,', :auto_code, ',%')
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('auto_code', $modificationCode[1]);
        $query->execute();
        $aData = $query->fetch();



        $sqlEmptyData = "
        SELECT am.*, l_gr.lex_name group_name, l.lex_name param_value
        FROM feu.feuc f
        INNER JOIN feu.avsmodel am ON (am.model_auto = :model_auto_f)
        LEFT JOIN feu.lex l_gr ON l_gr.lang = 'EN' AND l_gr.lex_code = am.main_part
        LEFT JOIN feu.lex l ON l.lang = 'EN' AND l.lex_code = am.param_code
        WHERE CONCAT(',', f.auto_code, ',') LIKE CONCAT('%,', :auto_code, ',%')
        AND engine_type = :engine_type
        ";
        $query = $this->conn->prepare($sqlEmptyData);
        $query->bindValue('auto_code', $modificationCode[1]);
        $query->bindValue('engine_type', $modificationCode[2]);
        $query->bindValue('model_auto_f', $aData['model_auto_f']);
        $query->execute();
        $aDataEmptyData = $query->fetchAll();

        if (empty($aDataEmptyData))
        {
            $sql = "
            SELECT DISTINCT
                CONCAT(
                CASE
                WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(f.params, '!', 43) , '!', -1) = ''
                THEN 'GC'
                ELSE 'CT'
                END , SUBSTRING(f.rigidity, -2, 2)) model_auto_fu
            FROM feu.feuc f
            WHERE CONCAT(',', f.auto_code, ',') LIKE CONCAT('%,', :auto_code, ',%')
            ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('auto_code', $modificationCode[1]);
            $query->execute();
            $aData = $query->fetch();

            $sqlEmptyData = "
            SELECT am.*, l_gr.lex_name group_name, l.lex_name param_value
            FROM feu.feuc f
            INNER JOIN feu.avsmodel am ON (am.model_auto = :model_auto_fu)
            LEFT JOIN feu.lex l_gr ON l_gr.lang = 'EN' AND l_gr.lex_code = am.main_part
            LEFT JOIN feu.lex l ON l.lang = 'EN' AND l.lex_code = am.param_code
            WHERE CONCAT(',', f.auto_code, ',') LIKE CONCAT('%,', :auto_code, ',%')
            AND engine_type = :engine_type
            ";
            $query = $this->conn->prepare($sqlEmptyData);
            $query->bindValue('auto_code', $modificationCode[1]);
            $query->bindValue('engine_type', $modificationCode[2]);
            $query->bindValue('model_auto_fu', $aData['model_auto_fu']);
            $query->execute();
            $aDataEmptyData = $query->fetchAll();
        }

        $complectations = array();
        $result = array();

        foreach ($aDataEmptyData as &$item)
        {
            $item['group_name'] = str_replace(' ', '_', $item['group_name']);
            unset($item);
        }



        foreach ($aDataEmptyData as $item){
            $result[($item['group_name'])][$item['param_value']] = $item['param_value'];
        }

        foreach ($result as $index => $value) {
            $complectations[($index)] = array(
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
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $modificationCode = explode('_', $modificationCode);


        $sql = "
        SELECT z.main_group, z.name_main, coordinates_names.num_index, x1, x2, y1, y2
        FROM (

        SELECT q.main_group, q.name_main,
        CASE WHEN main_group = @main_group
        THEN @n := @n +1
        ELSE @n :=1
        END AS num, @main_group := main_group AS main_group_doubl
        FROM (

        SELECT DISTINCT
        CASE
        WHEN chi.pnc_code <> ''
        THEN SUBSTR( chi.pnc_code, 1, 1 )
        ELSE SUBSTR( chi.name_group, 1, LENGTH( chi.name_group ) -2)
        END main_group, l_main.lex_name name_main
        FROM feu.coord_header_info chi
        LEFT JOIN lex l_main ON l_main.lang = :locale
        AND l_main.index_lex = chi.id_main
        WHERE chi.model_id = :model_id
        )q
        )z
        LEFT JOIN coordinates_names ON (coordinates_names.model_id = :model_id AND group_detail_sign LIKE '1'
        AND coordinates_names.num_model_group LIKE '0')
        LEFT JOIN coordinates ON (coordinates.model_id = coordinates_names.model_id AND coordinates.num_index = coordinates_names.num_index
        AND SUBSTRING_INDEX(SUBSTRING_INDEX(coordinates.label_name,'|',2),'|',-1) = z.main_group)
        WHERE z.num = 1
        ORDER BY z.main_group
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->bindValue('locale', $locale);
        $query->execute();
        $aData = $query->fetchAll();


        $groups = array();


        foreach($aData as $item){
            $groups[$item['main_group']] = array(
                Constants::NAME     => iconv('cp1251', 'utf8', $item['name_main']),
                Constants::OPTIONS  => array('picture' => $item ['num_index'],
                    Constants::X1 => floor($item['x1']),
                    Constants::X2 => floor($item['x2']),
                    Constants::Y1 => floor($item['y1']),
                    Constants::Y2 => floor($item['y2']),
                )
            );
        }

        return $groups;
    }

    public function getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $modificationCode = explode('_', $modificationCode);

        $sqlNumPrigroup = "
        SELECT num_index
        FROM `coordinates`
        WHERE `model_id` LIKE :model_id
        AND `label_name` LIKE CONCAT('1|', :groupCode, '%')
        ";
    	$query = $this->conn->prepare($sqlNumPrigroup);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groupSchemas = array();
        foreach ($aData as $item) {
            $groupSchemas[$item['num_index']] = array(Constants::NAME => $item['num_index'], Constants::OPTIONS => array());
        }

        return $groupSchemas;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $modificationCode = explode('_', $modificationCode);

        $sql = "
        SELECT q.main_group, q.name_main, q.sub_group, q.name_subgroup, q.x1, q.x2, q.y1, q.y2, q.num_index
        FROM (
        SELECT DISTINCT
              CASE
                   WHEN chi.pnc_code <> '' THEN SUBSTR(chi.pnc_code, 1, 1)
                   ELSE SUBSTR(chi.name_group, 1, LENGTH(chi.name_group) - 2)
              END main_group,
              CASE
                  WHEN chi.pnc_code <> '' THEN CONCAT(SUBSTR(chi.pnc_code, 1, 3), '-',  SUBSTR(chi.pnc_code, 4, 2))
                  ELSE SUBSTR(chi.name_group, LENGTH(chi.name_group) - 2, 2)
              END sub_group,
              l_main.lex_name name_main,
              l_sector.lex_name name_subgroup,
              co.x1 x1, co.x2 x2, co.y1 y1, co.y2 y2, co.num_index num_index
        FROM feu.coord_header_info chi
        LEFT JOIN coordinates co ON (co.model_id = :model_id AND SUBSTRING_INDEX(SUBSTRING_INDEX(co.label_name,'|',2),'|',-1) = REPLACE(CASE
                  WHEN chi.pnc_code <> '' THEN CONCAT(SUBSTR(chi.pnc_code, 1, 3), '-',  SUBSTR(chi.pnc_code, 4, 2))
                  ELSE SUBSTR(chi.name_group, LENGTH(chi.name_group) - 2, 2)
              END, '-', ''))
        LEFT JOIN lex l_main ON l_main.lang = :locale AND l_main.index_lex = chi.id_main
        LEFT JOIN lex l_sector ON l_sector.lang = :locale AND l_sector.index_lex = chi.id_sector
        WHERE chi.model_id = :model_id AND CASE
                   WHEN chi.pnc_code <> '' THEN SUBSTR(chi.pnc_code, 1, 1)
                   ELSE SUBSTR(chi.name_group, 1, LENGTH(chi.name_group) - 2)
              END = :groupCode
        ) q
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('locale', $locale);
        $query->execute();
        $aData = $query->fetchAll();


        $subgroups = array();


        foreach($aData as $item){
            $subgroups[$item['sub_group']] = array(
                Constants::NAME     => mb_strtoupper(iconv('cp1251', 'utf8', $item ['name_subgroup']),'utf8'),
                Constants::OPTIONS => array(
                    'picture' => $item['num_index'],
                    Constants::X1 => floor($item['x1']),
                    Constants::X2 => floor($item['x2']),
                    Constants::Y1 => floor($item['y1']),
                    Constants::Y2 => floor($item['y2']),
                )
            );
        }
        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $modificationCode = explode('_', $modificationCode);
        $complectationCode = explode('|', base64_decode($complectationCode));
        $subGroupCode = str_replace('-', '', $subGroupCode);

        $sql = "
        SELECT cdi.*, cn.num_index
        FROM feu.coord_detail_info cdi
        LEFT JOIN coordinates_names cn ON (cn.model_id = :model_id AND cn.group_detail_sign = 2 AND cn.num_model_group = cdi.num_model_group)
        WHERE cdi.model_id = :model_id
        AND cdi.model_3gr LIKE CONCAT('%', :subGroupCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('model_id', $modificationCode[0]);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();
        $aData = $query->fetchAll();

        foreach($aData as $index => &$item){
            $alexs = explode(' ', $item['lex_filter']);
            $aNames = explode(' ', $item['id_detail']);

            $aDataLex = array();
            $aDataName = array();

            foreach ($alexs as $alex){

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

            foreach ($aNames as $indexName => $sName){

                $sqlname = "
                SELECT lex_name
                FROM lex
                WHERE lex.index_lex = :sName
                AND lex.lang = :locale
                ";
                $query = $this->conn->prepare($sqlname);
                $query->bindValue('sName', $sName);
                $query->bindValue('locale', $locale);
                $query->execute();
                $aDataName[$indexName] = $query->fetchColumn(0);

                if ($aDataName[$indexName] == null) {
                    $sqlname = "
                    SELECT lex_name
                    FROM lex
                    WHERE lex.index_lex = :sName
                    AND lex.lang = 'EN'
                ";
                    $query = $this->conn->prepare($sqlname);
                    $query->bindValue('sName', $sName);
                    $query->execute();
                    $aDataName[$indexName] = $query->fetchColumn(0);
                }
            }

            $item['id_detail'] = implode(', ', $aDataName);
            $item['lex_filter'] = $aDataLex;

            if (count(array_intersect($complectationCode, $aDataLex)) != count($aDataLex)){
                unset ($aData[$index]);
            }
        }

        $schemas = array();
        foreach($aData as $item){
            $schemas[$item['num_index']] = array(
                Constants::NAME     => iconv('cp1251', 'utf8', $item['id_detail']),
                Constants::OPTIONS  => array('cd' => $item['num_index'])
            );
        }

        return $schemas;
    }

    public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {
        $schema = array();
        $schema[urldecode($schemaCode)] = array(
                    Constants::NAME => $schemaCode,
                        Constants::OPTIONS => array()
                );
        return $schema;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $modificationCode = explode('_', $modificationCode);

        $sqlPnc = "
        SELECT mp.*, SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 1), ',', -1) label,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) number,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 3), ',', -1) val_un,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 6), ',', -1) desc_lex_codes,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 8), ',', -1) start_date,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 9), ',', -1) end_date,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 10), ',', -1) quantity,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 16), ',', -1) number2,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 17), ',', -1) dates,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 29), ',', -1) new_lex_codes,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 30), ',', -1) kol,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 32), ',', -1) pr1,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 34), ',', -1) pr2,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 35), ',', -1) orig_dates,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 1), '!', -1) name_group,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 2), '!', -1) main_lex,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 3), '!', -1) group_lex,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 8), '!', -1) code_detail_full,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 9), '!', -1) id_detail,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 11), '!', -1) code_filter,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 14), '!', -1) name_detail,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 15), '!', -1) path_code_detail,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 17), '!', -1) model,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 18), '!', -1) model_1gr,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 19), '!', -1) model_2gr,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 20), '!', -1) model_3gr
        FROM feu.mcpart1 mp
        WHERE mp.pict_index = :schemaCode
        ";

    	$query = $this->conn->prepare($sqlPnc);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aPncs = $query->fetchAll();

        foreach ($aPncs as &$aPnc)
        {
            $sqlSchemaLabels = "
            SELECT x1, x2, y1, y2
            FROM coordinates
            WHERE model_id = :model_id
            AND num_index = :schemaCode
            AND label_name = :pnc
           ";

            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('model_id', $modificationCode[0]);
            $query->bindValue('schemaCode', $schemaCode);
            $query->bindValue('pnc',  $aPnc['label']);


            $query->execute();

            $aPnc['clangjap'] = $query->fetchAll();
            unset($aPnc);
        }

        $aPncs = $this->getLexNames($aPncs, 1);


        $pncs = array();

        foreach ($aPncs as $index=>$value) {

            if (!$value['clangjap'])
            {
                unset ($aPncs[$index]);

            }

            foreach ($value['clangjap'] as $item1)
            {
                $pncs[$value['label']][Constants::OPTIONS][Constants::COORDS][$item1['x1']] = array(
                    Constants::X1 => floor($item1['x1']),
                    Constants::Y1 => ($item1['y1']),
                    Constants::X2 => ($item1['x2']),
                    Constants::Y2 => ($item1['y2']));
            }
        }
        foreach ($aPncs as $item)
        {
            $pncs[$item['label']][Constants::NAME] = mb_strtoupper(iconv('cp1251', 'utf8', $item['desc_lex_codes']), 'utf8');
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

        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $modificationCode = explode('_', $modificationCode);

        $sqlPnc = "
        SELECT mp.*, SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 1), ',', -1) label,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 2), ',', -1) number,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 3), ',', -1) val_un,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 6), ',', -1) desc_lex_codes,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 8), ',', -1) start_date,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 9), ',', -1) end_date,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 10), ',', -1) quantity,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 16), ',', -1) number2,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 17), ',', -1) dates,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 29), ',', -1) new_lex_codes,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 30), ',', -1) kol,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 32), ',', -1) pr1,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 34), ',', -1) pr2,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 35), ',', -1) orig_dates,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 1), '!', -1) name_group,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 2), '!', -1) main_lex,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 3), '!', -1) group_lex,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 8), '!', -1) code_detail_full,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 9), '!', -1) id_detail,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 11), '!', -1) code_filter,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 14), '!', -1) name_detail,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 15), '!', -1) path_code_detail,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 17), '!', -1) model,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 18), '!', -1) model_1gr,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 19), '!', -1) model_2gr,
        SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param2, '!', 20), '!', -1) model_3gr
        FROM feu.mcpart1 mp
        WHERE mp.pict_index = :schemaCode
        AND SUBSTRING_INDEX(SUBSTRING_INDEX(mp.param1, ',', 1), ',', -1) = :pnc
        ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('schemaCode', $options['cd']);
        $query->bindValue('pnc', $pncCode);
        $query->execute();

        $aArticuls = $query->fetchAll();

        $aArticuls = $this->getLexNames($aArticuls, 0);

        $articuls = array();

        foreach ($aArticuls as $item) {
				$articuls[$item['number']] = array(
                Constants::NAME =>  mb_strtoupper(iconv('cp1251', 'utf8', $item['desc_lex_codes']), 'utf8'),
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['quantity'],
                    Constants::START_DATE => $item['start_date'],
                    Constants::END_DATE => $item['end_date'],
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

    public function getCodeByDescription($Description)
    {
        $Code = array();

        foreach ($Description as $item)
        {
            $sqlCode = "
        SELECT DescriptionId
        FROM lexicon
        where lexicon.Description = :item
        ";

            $query = $this->conn->prepare($sqlCode);
            $query->bindValue('item', $item);
            $query->execute();

            $Code[] = $query->fetchColumn(0);

        }


        return $Code;

    }

    public function getConditions($Description)
    {
        $Code = array();

        foreach ($Description as $item)
        {
            $sqlCode = "
        SELECT AttributeId
        FROM attribute
        where DescriptionId = :item
        ";

            $query = $this->conn->prepare($sqlCode);
            $query->bindValue('item', $item);
            $query->execute();

            $Code[] = $query->fetchColumn(0);

        }


        return $Code;

    }

    private function getLexNames($aPncs, $removeHandDrive)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        foreach ($aPncs as $index => &$aPnc){

            $aNames = explode(' ', $aPnc['desc_lex_codes']);
            $aDataName = array();

            foreach ($aNames as $indexName => $sName){

                $sqlname = "
                SELECT lex_name
                FROM lex
                WHERE lex.index_lex = :sName
                AND lex.lang = :locale
                ";
                $query = $this->conn->prepare($sqlname);
                $query->bindValue('sName', $sName);
                $query->bindValue('locale', $locale);
                $query->execute();
                $aDataName[$indexName] = $query->fetchColumn(0);

                if ($aDataName[$indexName] == null) {
                    $sqlname = "
                    SELECT lex_name
                    FROM lex
                    WHERE lex.index_lex = :sName
                    AND lex.lang = 'EN'
                ";
                    $query = $this->conn->prepare($sqlname);
                    $query->bindValue('sName', $sName);
                    $query->execute();
                    $aDataName[$indexName] = $query->fetchColumn(0);
                }

            }
            foreach ($aDataName as $indexaDataName =>$valueaDataName){
                if (($valueaDataName == 'RH' or $valueaDataName == 'LH') & $removeHandDrive)
                {
                    unset($aDataName[$indexaDataName]);
                }
            }
            $aPnc['desc_lex_codes'] = strtoupper(implode(', ', $aDataName));

            unset($aPnc);
        }

        return ($aPncs);

    }

    
} 