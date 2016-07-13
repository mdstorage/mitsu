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

            $regions[$item['catalog'].'_'.$item['sub_wheel']] = array(Constants::NAME=>$item['catalog'].'_'.$item['sub_wheel'], Constants::OPTIONS=>array());
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

        foreach ($aAgregateNames as $aAgregateName)
        {
            $aAgregateData = array_combine(str_split($aAgregateName['dif_code']) , array_diff(explode(' ', $aAgregateName['dif_fields']), array('')));
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
                    $psevd['p'.$i] = str_replace('ENGINE 1', 'ENGINE', $aAgregateData[$i]);

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

        $sql = "
        SELECT `id`, `desc_en`
        FROM pri_groups
        WHERE pri_groups.catalog = :regionCode AND pri_groups.model_code =:model_code
        UNION
        SELECT `id`, `desc_en`
        FROM pri_groups_jp_translate
        WHERE pri_groups_jp_translate.catalog = :regionCode AND pri_groups_jp_translate.model_code = :model_code
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();
        foreach($aData as $item){
            $groups[$item['id']] = array(
                Constants::NAME     => $item['desc_en'],
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

        $subgroups = array();
        foreach($aData as $item){
            $subgroups[$item['sec_group']] = array(
                Constants::NAME => ($item['desc_en'])?$item['desc_en']:$item['desc_'.$lang],
                Constants::OPTIONS => array(
                    Constants::X1 => floor($item['x']/2),
                    Constants::X2 => $item['x']/2+50,
                    Constants::Y1 => $item['y']/2-5,
                    Constants::Y2 => $item['y']/2+20
                )
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlSchemas = "
        SELECT *
        FROM part_images
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND sec_group = :subGroupCode
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        
        foreach($aData as $item){
		
		if ((substr_count($item['desc_en'],'MY')>0)&&(substr_count($item['desc_en'], substr($modificationCode, 1, 5))!=0)||(substr_count($item['desc_en'],'MY')==0))
		           { $schemas[$item['image_file']] = array(
                    Constants::NAME => $item['desc_en'],
                    Constants::OPTIONS => array(
                        Constants::CD => $item['catalog'].$item['sub_dir'].$item['sub_wheel'].$item['num_model'].$item['page'],
                        'num_model' => $item['num_model']
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
        ";

        $query = $this->conn->prepare($sqlSchema);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schema = array();
        
        foreach($aData as $item){
			
		            $schema[$item['image_file']] = array(
                    Constants::NAME => $item['desc_en'],
                    Constants::OPTIONS => array(
                        Constants::CD => $item['catalog'].$item['sub_dir'].$item['sub_wheel'].$item['num_model'].$item['page'], 
                        'num_model' => $item['num_model']
                    )
                );
            
        }
        

        return $schema;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

    	$sqlSchemaLabels = "
        SELECT *
        FROM labels
        WHERE catalog = :regionCode
          AND model_code =:model_code
          AND sec_group = :subGroupCode
          AND page = :cd
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', substr($cd['cd'], -2));
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aDataLabels = $query->fetchAll();
       
        $pncs = array();
        foreach ($aDataLabels as $item) {
            {
                $pncs[$item['part_code']][Constants::OPTIONS][Constants::COORDS][] = array(
                    Constants::X1 => floor($item['x']/2),
                    Constants::Y1 => ($item['y']/2-5),
                    Constants::X2 => ($item['x']/2+80),
                    Constants::Y2 => ($item['y']/2+20));
            }
        }
         foreach ($aDataLabels as $item) {
            $pncs[$item['part_code']][Constants::NAME] = $item['label_en'];
        }
      

        return $pncs;
    }

    public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {

        $articuls = array();
        return $articuls;
    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlSchemaLabels = "
        SELECT *
        FROM refer_to_fig
         WHERE catalog = :regionCode
          AND model_code =:model_code
          AND sec_group = :subGroupCode
          AND page = :cd
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', substr($cd['cd'], -2));
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aDataLabels = $query->fetchAll();
        
        $groups = array();
        foreach ($aDataLabels as $item) {
        	
        $sqlSubgroups = "
        SELECT desc_en
        FROM sec_groups
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND id = :refer_fig
        ";

        $query = $this->conn->prepare($sqlSubgroups);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('refer_fig', $item['refer_fig']);
        $query->execute();

        $aData = $query->fetch();
        
            $groups[$item['refer_fig']][Constants::NAME] = $aData['desc_en'];
            $groups[$item['refer_fig']][Constants::OPTIONS][Constants::COORDS][] = array(
                Constants::X1 => $item['x']/2,
                Constants::Y1 => $item['y']/2-5,
                Constants::X2 => $item['x']/2+80,
                Constants::Y2 => $item['y']/2+20,
            );
        }
        return $groups;
    }

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode)
    {

        $wheel = substr($regionCode, strpos($regionCode, '_')+1, strlen($regionCode));
        $regionCode = substr($regionCode, 0, strpos($regionCode, '_'));

        $sqlArticuls = "
        SELECT *
        FROM part_catalog
        WHERE catalog = :regionCode
          AND model_code = :model_code
          AND (f8 = :modificationCode OR f8 = '')
          AND sec_group = :subGroupCode
          AND part_code = :pncCode
        ";

        $query = $this->conn->prepare($sqlArticuls);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('modificationCode', substr($modificationCode, 0, 1));
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pncCode', $pncCode);
        $query->execute();

        $aData = $query->fetchAll();
       

        $sql = "
        SELECT *
        FROM body_desc
        WHERE catalog = :regionCode 
        AND model_code = :model_code
        AND f1 = :f1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('f1', $complectationCode);
        $query->execute();

        $aCompl = $query->fetch();
       
        $articuls = array();
      
        foreach ($aData as $item) {

            
            $articuls[$item['part_number']] = array(
                Constants::NAME =>$item['model_restrictions'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => '',
                    Constants::START_DATE => $item['sdate'],
                    Constants::END_DATE => $item['edate']
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
} 