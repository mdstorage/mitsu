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
        SELECT catalog
        FROM models
        GROUP BY catalog";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $item){
            $regions[$item['catalog']] = array(Constants::NAME=>$item['catalog'], Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {
        $sql = "
        SELECT *
        FROM models
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item){
            $models[$item['model_code']] = array(Constants::NAME=>'('.$item['model_code'].') '.$item['desc_en'], Constants::OPTIONS=>array());
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sql = "
        SELECT desc_en, change_abb, sdate, edate
        FROM model_changes
        WHERE model_code = :modelCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();
        

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['change_abb'].$item['desc_en']] = array(
                Constants::NAME     => $item['change_abb'].' '.$item['desc_en'],
                Constants::OPTIONS  => array(
                    Constants::START_DATE   => $item['sdate'],
                    Constants::END_DATE   => $item['edate'],
                   
            ));
            
        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {        
        $sql = "
        SELECT *
        FROM body
        WHERE catalog = :regionCode
        AND model_code =:model_code 
        AND SUBSTR(body, 4, 1) = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('modificationCode', substr($modificationCode, 0, 1));
        $query->execute();

        $aData = $query->fetchAll();
        
       
         
       foreach($aData as $item) 
       {
       	 
        $sqlDesc = "
        SELECT *
        FROM body_desc
        WHERE model_code = :model_code
        AND
        id = :body_desc_id
        ";

        $query = $this->conn->prepare($sqlDesc);
        $query->bindValue('model_code', $item['model_code']);
        $query->bindValue('body_desc_id', trim($item['body_desc_id']));
        $query->execute();
        $aData1[$item['body']] = $query->fetchAll();
     
        }
        
        $complectations = array();
        
       $ch = array();
        
        foreach($aData1 as $item){
        	
         foreach($item[0] as $index =>$value )	
        {	
        	$sqlAbb = "
        SELECT param_name
        FROM model_desc
        WHERE catalog = :regionCode
        AND model_code = :model_code 
        AND param_abb = :item1
        ";

        $query = $this->conn->prepare($sqlAbb);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('item1', $value);
        $query->execute();

        $sDesc[$index] = $query->fetch();
       
         $ch[$index] ='('.$value.') '.$sDesc[$index]['param_name'];
         		
		}
       	   
        
        	
        	
        	
            $complectations[$item[0]['f1']] = array(
                Constants::NAME     => $item[0]['f1'],
                Constants::OPTIONS  => array('option1'=> $ch['body'],
                							 'option2'=> $ch['engine1'],
                							 'option3'=> $ch['train'],
                							 'option4'=> $ch['trans'],
                							 'option5'=> $ch['grade'],
                							 'option6'=> $ch['sus'],)
            );  
      }
      
     return $complectations;
      
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $sql = "
        SELECT `id`, `desc_en`
        FROM pri_groups
        WHERE catalog = :regionCode AND model_code =:model_code
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
        /*$sql = "
        SELECT pp.cd, pp.pic_name
        FROM pgroup_pics pp
        WHERE pp.catalog = :regionCode
            AND pp.catalog_number = :modificationCode
            AND pp.id = :groupCode
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groupSchemas = array();
        foreach ($aData as $item) {
            $groupSchemas[$item['pic_name']] = array(Constants::NAME => $item['pic_name'], Constants::OPTIONS => array(Constants::CD => $item['cd']));
        }*/
		$groupSchemas = array();
        return $groupSchemas;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
     /*   $sqlGroup = "
        SELECT pp.cd, pp.pic_name
        FROM pgroups pg
        LEFT JOIN pgroup_pics pp ON (pg.id = pp.id AND pg.catalog = pp.catalog AND pg.catalog_number = pp.catalog_number)
        WHERE pg.catalog = :regionCode
            AND pg.catalog_number = :modificationCode
            AND pg.id = :groupCode
            AND pg.lang = 1
        LIMIT 1
        ";

        $query = $this->conn->prepare($sqlGroup);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aGroup = $query->fetch();

        $sqlPicture = "
            SELECT p.part_code, p.xs, p.ys, p.xe, p.ye
            FROM pictures p
            WHERE p.cd = :cd
              AND p.pic_name = :picName
        ";

        $query = $this->conn->prepare($sqlPicture);
        $query->bindValue('cd', $aGroup['cd']);
        $query->bindValue('picName', $aGroup['pic_name']);
        $query->execute();

        $aPicture = $query->fetchAll();

        $labels = array();
        foreach ($aPicture as $label){
            $labels[$label['part_code']] = $label;
        }*/

        $sqlSubgroups = "
        SELECT id, desc_en
        FROM sec_groups
        WHERE catalog = :regionCode
            AND model_code =:model_code
            AND pri_group = :groupCode
        ORDER BY id
        ";

        $query = $this->conn->prepare($sqlSubgroups);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subgroups = array();
        foreach($aData as $item){
            $subgroups[$item['id']] = array(
                Constants::NAME => $item['desc_en'],
                Constants::OPTIONS => /*isset($labels[substr($item['sgroup'], 0, 4)]) ? array(
                    Constants::X1 => $labels[substr($item['sgroup'], 0, 4)]['xs'],
                    Constants::X2 => $labels[substr($item['sgroup'], 0, 4)]['xe'],
                    Constants::Y1 => $labels[substr($item['sgroup'], 0, 4)]['ys'],
                    Constants::Y2 => $labels[substr($item['sgroup'], 0, 4)]['ye']
                ) : */array()
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {
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
     /*   $sqlSchemaLabels = "
        SELECT p.part_code, p.xs, p.ys, p.xe, p.ye
        FROM pictures p
        WHERE p.catalog = :regionCode
          AND p.cd = :cd
          AND p.pic_name = :schemaCode
          AND p.XC26ECHK = 1
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', $cd);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aDataLabels = $query->fetchAll();

        $sqlSchemaLabelsDescr = "
        SELECT pn.id, pn.descr
        FROM print_names pn
        WHERE pn.catalog = ?
          AND pn.cd = ?
          AND pn.lang = 1
          AND pn.id IN (?)
        ";

        $query = $this->conn->executeQuery($sqlSchemaLabelsDescr, array(
            $regionCode,
            $cd,
            array_column($aDataLabels, 'part_code')
        ), array(
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aDataDescr = $query->fetchAll();

        $sqlGroupPncs = "
        SELECT pc.dcod
        FROM part_catalog pc
        WHERE pc.catalog = :regionCode
          AND pc.cd = :cd
          AND pc.catalog_number = :modificationCode
          AND pc.sgroup = :subGroupCode
        GROUP BY pc.dcod
        ";

        $query = $this->conn->prepare($sqlGroupPncs);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', $cd);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aDataGroupPncs = $query->fetchAll();

        $pncs = array();
        foreach ($aDataLabels as $item) {
            if (in_array($item['part_code'], array_column($aDataGroupPncs, 'dcod'))){
                $pncs[$item['part_code']][Constants::OPTIONS][Constants::COORDS][] = array(
                    Constants::X1 => $item['xs'],
                    Constants::Y1 => $item['ys'],
                    Constants::X2 => $item['xe'],
                    Constants::Y2 => $item['ye'],);
            }
        }

        foreach ($aDataDescr as $item) {
            $pncs[$item['id']][Constants::NAME] = $item['descr'];
        }
        */
        
       
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
        $sqlArticuls = "
        SELECT *
        FROM part_catalog
        WHERE catalog = :regionCode
          AND model_code = :model_code
          AND f8 = :modificationCode
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
       

    /*    $sqlArticulsDescr = "
        SELECT pd.id, GROUP_CONCAT(pd.descr SEPARATOR '; ') as descr
        FROM part_descs pd
        WHERE pd.catalog = ?
          AND pd.cd = ?
          AND pd.catalog_number = ?
          AND pd.lang = 1
          AND pd.id IN (?)
        GROUP BY pd.id
        ";

        $query = $this->conn->executeQuery($sqlArticulsDescr, array(
            $regionCode,
            $cd,
            $modificationCode,
            array_column($aData, 'desc_id')
        ), array(
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aDataDescr = $query->fetchAll();
        $aDataDescr = array_combine(array_column($aDataDescr, 'id'), array_column($aDataDescr, 'descr'));
*/
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

    public function getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $subGroupCode)
    {
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