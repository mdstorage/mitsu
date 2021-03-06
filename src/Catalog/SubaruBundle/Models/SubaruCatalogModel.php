<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\SubaruBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\SubaruBundle\Components\SubaruConstants;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class SubaruCatalogModel extends CatalogModel{

    public function getRegions(){

        $sql = "
        SELECT models.catalog, sub_wheel
        FROM models
        UNION
        SELECT models_jp.catalog, sub_wheel
        FROM models_jp
        GROUP BY 1
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();

        foreach($aData as $item){
            $val = $this->translat->trans($item['catalog'].'_'.$item['sub_wheel'], array(), 'subaru');
            $regions[$item['catalog'].'_'.$item['sub_wheel']] =
                array(
                    Constants::NAME => $val,
                    Constants::OPTIONS=>array('locale_name'=>$item['catalog'].'_'.$item['sub_wheel'])
                );
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        $sql = "
        SELECT model_code, desc_en
        FROM models
        WHERE models.catalog = :regionCode
        AND models.sub_wheel = :wheel
        UNION
        SELECT model_code, desc_en
        FROM models_jp_translate
        WHERE models_jp_translate.catalog = :regionCode
        AND models_jp_translate.sub_wheel = :wheel
        ORDER BY (1)

        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('wheel', $wheel);
        $query->execute();

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item){
            $models[urldecode($item['model_code'])] = array(Constants::NAME=>'('.$item['model_code'].') '.$item['desc_en'], Constants::OPTIONS=>array());
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $table = 'model_changes_jp';
            $lang = 'jp';
        }
        else
        {
            $table = 'model_changes';
            $lang = 'en';
        }



        $sql = "
        SELECT desc_$lang, change_abb, sdate, edate
        FROM $table
        WHERE model_code = :modelCode
        ORDER BY change_abb
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['change_abb'].$item['desc_'.$lang]] = array(
                Constants::NAME     => $item['change_abb'].' '.$item['desc_'.$lang],
                Constants::OPTIONS  => array(
                    Constants::START_DATE   => $item['sdate'],
                    Constants::END_DATE   => $item['edate'],
                   
            ));
            
        }

        return $modifications;
    }

    public function getComplectations1($regionCode, $modelCode, $modificationCode)
    {
        $modelCode = urldecode($modelCode);
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        if ($regionCode == 'JP'){
            $table = 'model_desc_jp';
            $models = 'models_jp';
            $lang = 'jp';
        }
        else
        {
            $table = 'model_desc';
            $models = 'models';
            $lang = 'en';
        }


        $sql = "
        SELECT body_desc.f1 as compl_code,
        body_desc.body p1,
        body_desc.engine1 p2,
        body_desc.train p3,
        body_desc.trans p4,
        body_desc.grade p5,
        body_desc.sus p6,
        body_desc.f2 p7,
        body_desc.f3 p8,

        kig1.param_name ken1,
        kig2.param_name ken2,
        kig3.param_name ken3,
        kig4.param_name ken4,
        kig5.param_name ken5,
        kig6.param_name ken6,
        kig7.param_name ken7,
        kig8.param_name ken8


        FROM body_desc
        left JOIN body ON (body.catalog = :regionCode AND body.model_code = :model_code AND SUBSTR(body.body, 4, 1) = :modificationCode AND body.body_desc_id = body_desc.id)

        LEFT JOIN $table kig1 ON (kig1.catalog = body_desc.catalog AND kig1.model_code = body_desc.model_code AND kig1.param_abb = body_desc.body)
        LEFT JOIN $table kig2 ON (kig2.catalog = body_desc.catalog AND kig2.model_code = body_desc.model_code AND kig2.param_abb = body_desc.engine1)
        LEFT JOIN $table kig3 ON (kig3.catalog = body_desc.catalog AND kig3.model_code = body_desc.model_code AND kig3.param_abb = body_desc.train)
        LEFT JOIN $table kig4 ON (kig4.catalog = body_desc.catalog AND kig4.model_code = body_desc.model_code AND kig4.param_abb = body_desc.trans)
        LEFT JOIN $table kig5 ON (kig5.catalog = body_desc.catalog AND kig5.model_code = body_desc.model_code AND kig5.param_abb = body_desc.grade)
        LEFT JOIN $table kig6 ON (kig6.catalog = body_desc.catalog AND kig6.model_code = body_desc.model_code AND kig6.param_abb = body_desc.sus)
        LEFT JOIN $table kig7 ON (kig7.catalog = body_desc.catalog AND kig7.model_code = body_desc.model_code AND kig7.param_abb = body_desc.f2)
        LEFT JOIN $table kig8 ON (kig8.catalog = body_desc.catalog AND kig8.model_code = body_desc.model_code AND kig8.param_abb = body_desc.f3)



        WHERE body_desc.catalog = :regionCode
        AND body_desc.model_code = :model_code
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('modificationCode', substr($modificationCode, 0, 1));
        $query->execute();

        $aData = $query->fetchAll();


        $sql = "
        SELECT dif_code, dif_fields
        FROM $models
        WHERE $models.catalog = :regionCode
        AND $models.model_code = :model_code
        AND $models.sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('wheel', $wheel);
        $query->execute();

        $aAgregateNames = $query->fetchAll();

        $aAgregateData = array();

        $aVozmozhnyeZna4s = array('STEERING', 'DESTINAT', 'TRAIN', 'ENGINE', 'GRADE', 'ROOF', 'VEHICLE', 'SPECIFIC', 'MISSION', 'DOOR', 'SUS', 'BODY');




        $aAgregate = array();

        if ($regionCode != 'JP')
        {
            foreach ($aAgregateNames as $aAgregateName){
                $sdif_fields = $aAgregateName['dif_fields'];
                foreach(str_split($aAgregateName['dif_code']) as $index => $value){
                    $adif_code[$index] = $value;
                }
            }

            foreach ($aVozmozhnyeZna4s as $aVozmozhnyeZna4)
            {
                foreach($adif_code as $index => $value)
                {
                    if (strpos(trim($sdif_fields), $aVozmozhnyeZna4) !== false)
                    {
                        $aAgregate[strpos(trim($sdif_fields), $aVozmozhnyeZna4)] =  $aVozmozhnyeZna4;

                    }


                }

            }
            ksort($aAgregate);
            reset($aAgregate);

        }

        else
        {
            foreach ($aAgregateNames as $aAgregateName)
            {

                    $aAgregate = explode(' ',  $aAgregateName['dif_fields']);

            }
        }



        foreach ($aAgregateNames as $aAgregateName)
        {
            $aAgregateData = array_combine(str_split($aAgregateName['dif_code']) , ($regionCode == 'JP')?array_values(array_diff($aAgregate, array(''))) : array_values(array_diff(array_unique($aAgregate), array(''))));
        }


        $com = array();


        $result = array();
        $psevd = array();

        $af = array();

        foreach($aData as $item) {
            for ($i = 1; $i < 9; $i++) {
                if ($item['p' . $i]) {
                    $af[$i][$item['p' . $i]] = '(' . $item['p' . $i] . ') ' . $item['ken' . $i];
                    $result['p'.$i] = $af[$i];
                    $psevd['p'.$i] = str_replace(array('ENGINE 1'), array('ENGINE'), $aAgregateData[$i]);

                }
            }


            $com[$item['compl_code']] = array(
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

            for ($i = 1; $i < 9; $i++) {

                if (!empty($value['name']['p' . $i])) {
                    foreach ($value['name']['p' . $i] as $ind => $val) {
                        $result['p' . $i]['name'][$ind] = $val;
                    }
                }


                if (!empty($value['options']['option1']['p' . $i])) {
                    foreach ($value['options']['option1'] as $ind => $val) {
                        $result['p' . $i]['options']['option1'][$ind] = $val;
                    }
                }


            }


            unset($value);
        }

        return ($result);


    }

    public function getComplectationsKorobka($regionCode, $modelCode, $modificationCode, $priznak, $engine)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        if ($regionCode == 'JP'){
            $table = 'model_desc_jp';
            $models = 'models_jp';
            $lang = 'jp';
        }
        else
        {
            $table = 'model_desc';
            $models = 'models';
            $lang = 'en';
        }

        switch($priznak)

        {
            case ('p1'): $pole = 'body_desc.body';break;
            case ('p2'): $pole = 'body_desc.engine1';break;
            case ('p3'): $pole = 'body_desc.train';break;
            case ('p4'): $pole = 'body_desc.trans';break;
            case ('p5'): $pole = 'body_desc.grade';break;
            case ('p6'): $pole = 'body_desc.sus';break;
            case ('p7'): $pole = 'body_desc.f2';break;
            case ('p8'): $pole = 'body_desc.f3';break;

        }

        $sql = "
        SELECT body_desc.f1 as compl_code,
        body_desc.body p1,
        body_desc.engine1 p2,
        body_desc.train p3,
        body_desc.trans p4,
        body_desc.grade p5,
        body_desc.sus p6,
        body_desc.f2 p7,
        body_desc.f3 p8,

        kig1.param_name ken1,
        kig2.param_name ken2,
        kig3.param_name ken3,
        kig4.param_name ken4,
        kig5.param_name ken5,
        kig6.param_name ken6,
        kig7.param_name ken7,
        kig8.param_name ken8


        FROM body_desc
        left JOIN body ON (body.catalog = :regionCode AND body.model_code = :model_code AND SUBSTR(body.body, 4, 1) = :modificationCode AND body.body_desc_id = body_desc.id)

        LEFT JOIN $table kig1 ON (kig1.catalog = body_desc.catalog AND kig1.model_code = body_desc.model_code AND kig1.param_abb = body_desc.body)
        LEFT JOIN $table kig2 ON (kig2.catalog = body_desc.catalog AND kig2.model_code = body_desc.model_code AND kig2.param_abb = body_desc.engine1)
        LEFT JOIN $table kig3 ON (kig3.catalog = body_desc.catalog AND kig3.model_code = body_desc.model_code AND kig3.param_abb = body_desc.train)
        LEFT JOIN $table kig4 ON (kig4.catalog = body_desc.catalog AND kig4.model_code = body_desc.model_code AND kig4.param_abb = body_desc.trans)
        LEFT JOIN $table kig5 ON (kig5.catalog = body_desc.catalog AND kig5.model_code = body_desc.model_code AND kig5.param_abb = body_desc.grade)
        LEFT JOIN $table kig6 ON (kig6.catalog = body_desc.catalog AND kig6.model_code = body_desc.model_code AND kig6.param_abb = body_desc.sus)
        LEFT JOIN $table kig7 ON (kig7.catalog = body_desc.catalog AND kig7.model_code = body_desc.model_code AND kig7.param_abb = body_desc.f2)
        LEFT JOIN $table kig8 ON (kig8.catalog = body_desc.catalog AND kig8.model_code = body_desc.model_code AND kig8.param_abb = body_desc.f3)



        WHERE body_desc.catalog = :regionCode
        AND body_desc.model_code = :model_code
        AND $pole = :engine
        ";

        $query = $this->conn->prepare($sql);

        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('modificationCode', substr($modificationCode, 0, 1));
        $query->bindValue('engine', $engine);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();
        $result = array();

        $af = array();

        foreach($aData as $item) {
            for ($i = 1; $i < 9; $i++) {
                if ($item['p' . $i]) {
                    $af[$i][$item['p' . $i]] = '(' . $item['p' . $i] . ') ' . $item['ken' . $i];
                    $result['p'.$i] = $af[$i];
                }
            }
        }


        foreach ($result as $index => $value) {

            $complectations[($index)] = $value;
        }

        return $complectations;
    }

    public function getComplectationCodeFromFormaData($aDataFromForm, $regionCode, $modelCode)

    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        if ($regionCode == 'JP'){
            $table = 'model_desc_jp';
            $models = 'models_jp';
            $lang = 'jp';
        }
        else
        {
            $table = 'model_desc';
            $models = 'models';
            $lang = 'en';
        }

        $pole = array();
        $pole['body'] = '';
        $pole['engine1'] = '';
        $pole['train'] = '';
        $pole['trans'] = '';
        $pole['grade'] = '';
        $pole['sus'] = '';
        $pole['f2'] = '';
        $pole['f3'] = '';

        foreach ($aDataFromForm as $index => $value)
        {
            switch($index)

            {
                case ('p1'): $pole['body'] = $value;break;
                case ('p2'): $pole['engine1'] = $value;break;
                case ('p3'): $pole['train'] = $value;break;
                case ('p4'): $pole['trans'] = $value;break;
                case ('p5'): $pole['grade'] = $value;break;
                case ('p6'): $pole['sus'] = $value;break;
                case ('p7'): $pole['f2'] = $value;break;
                case ('p8'): $pole['f3'] = $value;break;

            }
        }



        $sql = "
        SELECT body_desc.f1
        FROM body_desc
        WHERE body_desc.catalog = :regionCode
        AND body_desc.model_code = :modelCode
        AND body_desc.body = :body
        AND body_desc.engine1 = :engine1
        AND body_desc.train = :train
        AND body_desc.trans = :trans
        AND body_desc.grade = :grade
        AND body_desc.sus = :sus
        AND body_desc.f2 = :f2
        AND body_desc.f3 = :f3
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('body',  $pole['body']);
        $query->bindValue('engine1',  $pole['engine1']);
        $query->bindValue('train',  $pole['train']);
        $query->bindValue('trans',  $pole['trans']);
        $query->bindValue('grade',  $pole['grade']);
        $query->bindValue('trans',  $pole['trans']);
        $query->bindValue('sus',  $pole['sus']);
        $query->bindValue('f2',  $pole['f2']);
        $query->bindValue('f3',  $pole['f3']);


        $query->execute();

        $aData = $query->fetch();

        $complectation = $aData['f1'];


        return $complectation;

    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)

    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sql = "
        SELECT body_desc.f1
        FROM body_desc
        WHERE body_desc.catalog = :regionCode
        AND body_desc.model_code = :modelCode
        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modelCode',  $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();

        foreach($aData as $item){


            $complectations[$item['f1']] = array(
                Constants::NAME => $item['f1'],
                Constants::OPTIONS => array());

        }

        return $complectations;

    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $table = 'pri_groups_jp_translate';
            $lang = 'jp';

        }
        else
        {
            $table = 'pri_groups';
            $lang = 'en';

        }

        $sql = "
        SELECT *
        FROM $table
        WHERE $table.catalog = :regionCode AND $table.model_code = :model_code
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();
        foreach($aData as $item){
            $groups[$item['id']] = array(
                Constants::NAME     => ($item['desc_en'])?$item['desc_en']:$item['desc_'.$lang],
                Constants::OPTIONS  => array()
            );
        }

        return $groups;
    }

    public function getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        if ($regionCode == 'JP'){
            $table = 'pri_groups_full_jp_translate';
            $imagestable = 'part_images_jp_translate';
            $lang = 'jp';

        }
        else
        {
            $table = 'pri_groups_full';
            $imagestable = 'part_images';
            $lang = 'en';

        }


        $sqlNumPrigroup = "
        SELECT $table.num_image, $table.catalog, $table.sub_dir, $table.sub_wheel, $imagestable.num_model
        FROM $table
        INNER JOIN $imagestable ON ($imagestable.catalog = $table.catalog AND $imagestable.model_code = $table.model_code)
        WHERE $table.catalog = :regionCode
            AND $table.model_code =:model_code
            AND $table.pri_group = :groupCode
        GROUP BY $imagestable.num_model
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
    /*    foreach ($aData as $item)*/ {
            $groupSchemas[$aData['num_image']] = array(Constants::NAME => $aData['num_image'], Constants::OPTIONS => array(
              Constants::CD => $aData['catalog'],
                        'sub_dir' => $aData['sub_dir'],
                        'sub_wheel' => $aData['sub_wheel'],
                    	'num_model' => $aData['num_model'],
                        'num_image' => $aData['num_image']
                ));
        }

        return $groupSchemas;

    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));


        if ($regionCode == 'JP'){
            $table = 'sec_groups_full_jp_translate';
            $lang = 'jp';

        }
        else
        {
            $table = 'sec_groups_full';
            $lang = 'en';

        }


        $sqlSubgroups = "
        SELECT *
        FROM $table
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND pri_group = :groupCode
        ORDER BY sec_group
        ";

        $query = $this->conn->prepare($sqlSubgroups);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        foreach ($aData as &$aPnc)
        {

            $sqlSchemaLabels = "
        SELECT x, y
        FROM $table
        WHERE catalog = :regionCode
          AND model_code =:model_code
          AND pri_group = :groupCode
          AND sec_group = :subGroupCode
           ";

            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('regionCode', $regionCode);
            $query->bindValue('model_code', $modelCode);
            $query->bindValue('groupCode', $groupCode);
            $query->bindValue('subGroupCode', $aPnc['sec_group']);

            $query->execute();

            $aPnc['clangjap'] = $query->fetchAll();
            unset($aPnc);

        }

        $subgroups = array();

        foreach ($aData as $index=>$value) {

            if (!$value['clangjap'])
            {
                unset ($aData[$index]);

            }

            foreach ($value['clangjap'] as $item1)
            {
                $subgroups[$value['sec_group']][Constants::OPTIONS][Constants::COORDS][$item1['x']] = array(
                    Constants::X1 => floor($item1['x']/2),
                    Constants::X2 => $item1['x']/2+50,
                    Constants::Y1 => floor($item1['y']/2-5),
                    Constants::Y2 => $item1['y']/2+20
                );
            }
        }

        foreach ($aData as $item)
        {
            $subgroups[$item['sec_group']][Constants::NAME] = ($item['desc_en'])?$item['desc_en']:$item['desc_'.$lang];
        }

        return $subgroups;

    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
        $complectation = $this->getComplForSchemas($regionCode, $modelCode, $complectationCode);

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $table = 'part_images_jp';
            $lang = 'jp';

        }
        else
        {
            $table = 'part_images';
            $lang = 'en';

        }

        $sqlSchemas = "
        SELECT
        $table.image_file,
        $table.catalog,
        $table.sub_wheel,
        $table.num_model,
        $table.sub_dir,
        $table.page,
        desc_$lang,
        image_time.model_restriction as model_restrictions,
        image_time.sdate,
        image_time.edate
        FROM $table
        INNER JOIN image_time ON (image_time.catalog = $table.catalog AND image_time.model_code = $table.model_code AND image_time.sec_group = $table.sec_group
        AND image_time.sub_wheel = $table.sub_wheel
        AND image_time.page = $table.page)
        WHERE $table.catalog = :regionCode
            AND $table.model_code =:model_code
            AND $table.sec_group = :subGroupCode
            AND $table.sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('wheel', $wheel);
        $query->execute();

        $aDatas = $query->fetchAll();

        $aData = $this->restrictionsFilter($aDatas, $complectation);



        $schemas = array();

        foreach($aData as $item){
		
		if (((substr_count($item['desc_'.$lang],'MY') > 0) && (substr_count($item['desc_'.$lang], substr($modificationCode, 1, 5)) != 0)) || (substr_count($item['desc_'.$lang],'MY') == 0))
        {
            $schemas[$item['image_file']] = array(
                Constants::NAME => $item['desc_'.$lang],
                Constants::OPTIONS => array(
                    Constants::CD => $item['catalog'].'/'.$item['sub_dir'].'/'.$item['sub_wheel'].'/model'.$item['num_model'].'/part1',
                    'num_model' => $item['num_model'],
                    'page' => $item['page'],
                    'sub_dir' => $item['sub_dir'],
                    'sdate' => $item['sdate'],
                    'edate' => $item['edate']
                    )
                );
        }
            
        }

        return $schemas;
    }

    public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));
    	 
        $sqlSchema = "
        SELECT *
        FROM part_images
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND sec_group = :subGroupCode
            AND image_file = :schemaCode
            AND sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sqlSchema);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->bindValue('wheel', $wheel);

        $query->execute();

        $aData = $query->fetchAll();

        $schema = array();
        
        foreach($aData as $item){
			
		            $schema[$item['image_file']] = array(
                    Constants::NAME => $item['desc_en'],
                    Constants::OPTIONS => array(
                        Constants::CD => $item['catalog'].'/'.$item['sub_dir'].'/'.$item['sub_wheel'].'/model'.$item['num_model'].'/part1',
                        'num_model' => $item['num_model'],
                        'page' => $item['page']
                    )
                );
            
        }
        

        return $schema;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $table = 'labels_jp_translate';
            $lang = 'jp';

        }
        else
        {   $table = 'labels';
            $lang = 'en';

        }

    	$sqlSchema = "
        SELECT *
        FROM $table
        WHERE catalog = :regionCode
          AND model_code =:model_code
          AND sec_group = :subGroupCode
          AND page = :page
          AND sub_wheel = :wheel
          AND sub_dir = :sub_dir
        ";

        $query = $this->conn->prepare($sqlSchema);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('page', substr($cd['page'], -2));
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('wheel', $wheel);
        $query->bindValue('sub_dir', $cd['sub_dir']);
        $query->execute();

        $aPncs = $query->fetchAll();

        foreach ($aPncs as &$aPnc)
        {

            $sqlSchemaLabels = "
        SELECT x, y
        FROM $table
        WHERE catalog = :regionCode
          AND model_code =:model_code
          AND sec_group = :subGroupCode
          AND page = :page
          AND sub_wheel = :wheel
          and part_code = :pnc
          and sub_dir = :sub_dir
          AND f9 = :f9
           ";

            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('regionCode', $regionCode);
            $query->bindValue('page', substr($cd['page'], -2));
            $query->bindValue('model_code', $modelCode);
            $query->bindValue('subGroupCode', $subGroupCode);
            $query->bindValue('wheel', $wheel);
            $query->bindValue('pnc',  $aPnc['part_code']);
            $query->bindValue('sub_dir',  $aPnc['sub_dir']);
            $query->bindValue('f9',  $aPnc['f9']);


            $query->execute();

            $aPnc['clangjap'] = $query->fetchAll();
            unset($aPnc);

        }


        $pncs = array();


        foreach ($aPncs as $index=>$value) {

            if (!$value['clangjap'])
            {
                unset ($aPncs[$index]);

            }

            foreach ($value['clangjap'] as $item1)
            {
                $pncs[$value['part_code'].$value['f9']][Constants::OPTIONS][Constants::COORDS][$item1['x']] = array(
                    Constants::X1 => floor($item1['x']/2),
                    Constants::Y1 => ($item1['y']/2-5),
                    Constants::X2 => ($item1['x']/2+80),
                    Constants::Y2 => ($item1['y']/2+20));
            }
        }
         foreach ($aPncs as $item)
         {
            $pncs[$item['part_code'].$item['f9']][Constants::NAME] = $item['label_en'] ? $item['label_en'] : $item['label_'.$lang];
         }

        return $pncs;
    }

    public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $table = 'labels2_jp';
            $lang = 'jp';

        }
        else
        {
            $table = 'labels2';
            $lang = 'en';

        }

        $sqlSchemaLabels = "
        SELECT *
        FROM $table
        WHERE catalog = :regionCode
          AND model_code =:model_code
          AND sec_group = :subGroupCode
          AND page = :page
          AND sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('page', substr($cd['page'], -2));
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('wheel', $wheel);
        $query->execute();

        $aDataLabels = $query->fetchAll();

        $pncs = array();
        foreach ($aDataLabels as $item) {
            {
                $pncs[$item['label_text']][Constants::OPTIONS][Constants::COORDS][] = array(
                    Constants::X1 => floor($item['x']/2),
                    Constants::Y1 => ($item['y']/2-5),
                    Constants::X2 => ($item['x']/2+160),
                    Constants::Y2 => ($item['y']/2+20));
            }
        }
        foreach ($aDataLabels as $item) {
            $pncs[$item['label_text']][Constants::NAME] = $item['label_text'];
        }


        return $pncs;
    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        if ($regionCode == 'JP'){
            $table = 'sec_groups_jp_translate';
            $lang = 'jp';

        }
        else
        {
            $table = 'sec_groups';
            $lang = 'en';

        }

        $sqlSchema= "
          SELECT *
          FROM refer_to_fig
          INNER JOIN $table ON ($table.id = refer_to_fig.refer_fig AND $table.catalog = refer_to_fig.catalog AND $table.model_code = refer_to_fig.model_code)
          WHERE refer_to_fig.catalog = :regionCode
          AND refer_to_fig.model_code = :model_code
          AND refer_to_fig.sec_group = :subGroupCode
          AND refer_to_fig.page LIKE :cd
          AND refer_to_fig.sub_dir LIKE :sub_dir
        ";

        $query = $this->conn->prepare($sqlSchema);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', $cd['page']);
        $query->bindValue('sub_dir', $cd['sub_dir']);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aPncs = $query->fetchAll();
        
        $groups = array();


        foreach ($aPncs as &$aPnc)
        {

            $sqlSchemaLabels = "
          SELECT x,y
          FROM refer_to_fig
          INNER JOIN $table ON ($table.id = refer_to_fig.refer_fig AND $table.catalog = refer_to_fig.catalog AND $table.model_code = refer_to_fig.model_code)
          WHERE refer_to_fig.catalog = :regionCode
          AND refer_to_fig.model_code = :model_code
          AND refer_to_fig.sec_group = :subGroupCode
          AND refer_to_fig.page LIKE :cd
          AND refer_to_fig.refer_fig LIKE :refer_fig
          AND refer_to_fig.sub_dir LIKE :sub_dir

           ";

            $query = $this->conn->prepare($sqlSchemaLabels);
            $query->bindValue('regionCode', $regionCode);
            $query->bindValue('cd', $cd['page']);
            $query->bindValue('model_code', $modelCode);
            $query->bindValue('subGroupCode', $subGroupCode);
            $query->bindValue('refer_fig',  $aPnc['refer_fig']);
            $query->bindValue('sub_dir', $cd['sub_dir']);
            $query->execute();

            $query->execute();

            $aPnc['clangjap'] = $query->fetchAll();
            unset($aPnc);

        }

        foreach ($aPncs as $index=>$value) {

            if (!$value['clangjap'])
            {
                unset ($aPncs[$index]);

            }

            foreach ($value['clangjap'] as $item1)
            {
                $groups[$value['refer_fig']][Constants::OPTIONS][Constants::COORDS][$item1['x']] = array(
                    Constants::X1 => floor($item1['x']/2),
                    Constants::Y1 => ($item1['y']/2-5),
                    Constants::X2 => ($item1['x']/2+80),
                    Constants::Y2 => ($item1['y']/2+20));
            }
        }

        foreach ($aPncs as $item)
        {
            $groups[$item['refer_fig']][Constants::NAME] = $item['desc_en'] ? $item['desc_en'] : $item['desc_'.$lang];
        }

        return $groups;
    }

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode)
    {

        $complectation = $this->getComplForSchemas($regionCode, $modelCode, $complectationCode);

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlArticuls = "
        SELECT *
        FROM part_catalog
        WHERE catalog = :regionCode
          AND model_code = :model_code
          AND sec_group = :subGroupCode
          AND CONCAT (part_code, f11) LIKE :pncCode
          AND sub_wheel = :wheel
        ";

        $query = $this->conn->prepare($sqlArticuls);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);

        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pncCode', $pncCode);
        $query->bindValue('wheel', $wheel);
        $query->execute();

        $aArticulses = $query->fetchAll();

        $aArticuls = $this->restrictionsFilter($aArticulses, $complectation);
        
        $articuls = array();
      
        foreach ($aArticuls as $item) {

            
            $articuls[$item['part_number'].'_'.$item['f8']] = array(
                Constants::NAME =>$item['part_number'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => '',
                    Constants::START_DATE => $item['sdate'],
                    Constants::END_DATE => $item['edate'],
                    'model_restrictions' => $item['model_restrictions']
                )
            );
        }

        return $articuls;
    }

    public function getGroupBySubgroup($regionCode, $modelCode, $subGroupCode)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlGroup = "
        SELECT pri_group
        FROM sec_groups
        WHERE catalog = :regionCode
          AND model_code = :model_code
          AND id = :subGroupCode
        ";

        $query = $this->conn->prepare($sqlGroup);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $groupCode = $query->fetchColumn(0);

        return $groupCode;
        
    }

    public function getComplForSchemas($regionCode, $modelCode, $complectationCode)

    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sql = "
        SELECT body, engine1, train, trans, grade, sus, f2, f3
        FROM body_desc
        WHERE catalog = :regionCode
          AND model_code = :modelCode
          AND f1 = :complectationCode

             ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('complectationCode', $complectationCode);

        $query->execute();

        $complectation = $query->fetchAll();

        return (array_diff($complectation[0], array('')));
    }

    public function multiexplode ($delimiters,$string) {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }

    public function restrictionsFilter($aArticuls, $complectation)
    {

        foreach ($aArticuls as $index => $value) {

            if ($value['model_restrictions'] != 'ALL') {


                $ct = 0;
                $schemaOptions = $this->multiexplode(array(' +', ' + '), $value['model_restrictions']);


                foreach ($schemaOptions as $schemaOptionsOpt) {


                    $item = (strpos($schemaOptionsOpt, '<') !== false) ? substr_replace($schemaOptionsOpt, '', strpos($schemaOptionsOpt, '<'), strripos($schemaOptionsOpt, '>') + 1) : $schemaOptionsOpt;


                    /*  $item = trim($item, ('*()'));*/
                    if (strpos($item, ".")) {
                        $plus = explode('.', $item);
                        $countOfPluses = 0;
                        $pluses = array();


                        foreach ($plus as $index1 => $plusOne) {

                            if (strpos($plusOne, '+')) {

                                unset($plus[$index1]);
                                $plusOne = trim($plusOne, ('*()'));
                                $pluses = explode('+', $plusOne);

                                $countOfPluses = count($pluses) - 1;


                                $plus = array_merge($plus, $pluses);

                            }
                        }


                        $countPlus = count($plus) - $countOfPluses;


                        if ($countPlus == count(array_intersect($plus, $complectation)) || count(array_intersect($plus, $complectation)) == count($complectation)) {
                            $ct = $ct + 1;
                        }


                    } else {

                        if (in_array($item, $complectation)) {
                            $ct = $ct + 1;
                        }

                        else
                        {
                            if (strpos($item, "#"))
                            {
                                $substr = substr_replace($item, '', strpos($item, '#'), strripos($item, '#') + 1);
                                if(preg_grep('"'.$substr.'"', $complectation))
                                {
                                    $ct = $ct + 1;
                                }
                            }
                        }



                    }


                }


                if ($ct === 0) {
                    unset ($aArticuls[$index]);
                }

            }
        }

        return $aArticuls;
    }
} 