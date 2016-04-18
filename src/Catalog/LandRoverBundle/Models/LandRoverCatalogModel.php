<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\LandRoverBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\LandRoverBundle\Components\LandRoverConstants;

class LandRoverCatalogModel extends CatalogModel{

    public function getRegions(){

        $aData = array('EU' => 'Европа');



        $regions = array();
        foreach($aData as $index => $value)
        {
            $regions[$index] = array(Constants::NAME=>$value, Constants::OPTIONS=>array());
        }

        return $regions;

    }

    public function getModels($regionCode)
    {

            $sql = "
        SELECT auto_name, model_id, engine_type
        FROM lrec
        ORDER by ABS(model_id)
        ";



        $query = $this->conn->prepare($sql);
        $query->execute();

        $aData = $query->fetchAll();
        $aDatas = array();

        foreach($aData as $item) {

            if ($item['model_id'] !== NULL)
            $aDatas[] = $item['model_id'];
        }

            $models = array();


        foreach($aData as $item){

            if ($item['model_id'] !== null)

            $models[$item['model_id'].'_'.(ctype_alpha($item['engine_type'])?'GC'.$item['engine_type']:$item['engine_type'])] = array(Constants::NAME=>strtoupper($item['auto_name']),
            Constants::OPTIONS=>array(
                'key' => (array_search($item['model_id'],$aDatas)!=0)?array_search($item['model_id'],$aDatas):"0",
                'type' => ctype_alpha($item['engine_type'])?'GC'.$item['engine_type']:$item['engine_type']

            ));
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {


        $modifications = array();

            $modifications['1'] = array(
                Constants::NAME     => '1',
                Constants::OPTIONS  => array());


        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {


        $complectations = array();


                $complectations['1'] = array(
                    Constants::NAME => '1',
                    Constants::OPTIONS => array());


         return $complectations;
     
    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {
        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));

        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));



            $sql = "
        SELECT (SUBSTRING(RIGHT(name_group,3),1,1)) as ngroup, lex_name, coordinates_names.index1 as num_index
        FROM coord_header_info

        INNER JOIN coordinates_names ON (coordinates_names.model_id = coord_header_info.model_id AND coordinates_names.group_detail_sign = 1
        AND coordinates_names.num_model_group = ASCII(SUBSTRING(RIGHT(name_group,3),1,1))-64)

        INNER JOIN lex ON (lex.index_lex = coord_header_info.id_main AND lex.lang = 'EN')
        WHERE coord_header_info.model_id = :modelCode


        UNION

        SELECT (SUBSTRING(RIGHT(name_group,3),1,1)) as ngroup, lex_name, coordinates_names.num_index as num_index
        FROM coord_header_info

        INNER JOIN coordinates_names ON (coordinates_names.model_id = coord_header_info.model_id AND coordinates_names.group_detail_sign = 1
        AND coordinates_names.num_model_group = (SUBSTRING(RIGHT(name_group,3),1,1)))

        INNER JOIN lex ON (lex.index_lex = coord_header_info.id_main AND lex.lang = 'EN')
        WHERE coord_header_info.model_id = :modelCode

        order by (1)

        ";

  /*     $sql = "
        SELECT SUBSTRING(name_group,1,1) as ngroup
        FROM coord_header_info
        WHERE coord_header_info.model_id = :modelCode
        group by ngroup
        ";
*/
            $query = $this->conn->prepare($sql);
            $query->bindValue('modelCode',  $modelCode);

            $query->execute();
            $aData = $query->fetchAll();






        $groups = array();


        foreach($aData as $item){

            $groups[$item['ngroup']] = array(
                Constants::NAME     => strtoupper($item ['lex_name']),
                Constants::OPTIONS => array(
                    'picture' => $item['num_index'],
                    'pictureFolder' => $pictureFolder,
                )
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
        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));
        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));






if (strlen($pictureFolder) == 2) {


    $sql = "
        SELECT coord_header_info.name_group as nsubgroup, lex_name, coordinates.num_index, coordinates.x1, coordinates.x2, coordinates.y1, coordinates.y2
        FROM coord_header_info
        INNER JOIN lex ON (lex.index_lex = coord_header_info.id_sector AND lex.lang = 'EN')
        INNER JOIN coordinates ON (coordinates.model_id = coord_header_info.model_id AND coordinates.label_name = CONCAT(:groupCode, ABS(SUBSTRING(coord_header_info.name_group,2,2))))
        WHERE coord_header_info.model_id = :modelCode
        AND coord_header_info.pnc_code = ''
        AND SUBSTRING(coord_header_info.name_group,1,1) = :groupCode

        group by (1)
        ";

    $query = $this->conn->prepare($sql);
    $query->bindValue('modelCode', $modelCode);
    $query->bindValue('groupCode', $groupCode);
    $query->execute();
    $aData = $query->fetchAll();

}

        else {

            $sql = "

        SELECT (RIGHT(coord_header_info.name_group,3)) as nsubgroup, lex_name, coordinates.num_index, coordinates.x1, coordinates.x2, coordinates.y1, coordinates.y2
        FROM coord_header_info
        left JOIN lex ON (lex.index_lex = coord_header_info.id_sector AND lex.lang = 'EN')
        left JOIN coordinates ON (coordinates.model_id = coord_header_info.model_id AND coordinates.label_name LIKE CONCAT('%',coord_header_info.pnc_code,'%'))
        WHERE coord_header_info.model_id = :modelCode
    AND SUBSTRING(RIGHT(coord_header_info.name_group,3),1,1) = :groupCode

    group by (1)
        ";
        }


        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->bindValue('groupCode', $groupCode);

        $query->execute();
        $aData = $query->fetchAll();



           $subgroups = array();


           foreach($aData as $item)
           {

               $subgroups[$item['nsubgroup']] = array(

               Constants::NAME => strtoupper($item['lex_name']),
                   Constants::OPTIONS => array(
                       'picture' => $item['num_index'],
                       'pictureFolder' => $pictureFolder,
                       Constants::X1 => floor($item['x1']),
                       Constants::X2 => floor($item['x1']+30),
                       Constants::Y1 => floor($item['y1']),
                       Constants::Y2 => floor($item['y1']+20),
                   )

               );

           }

           return $subgroups;
       }

       public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
       {

           $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));
           $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));


         /*  $sql = "
         SELECT coord_detail_info.code_detail, coordinates_names.num_index, lex1.lex_name as lex11, lex2.lex_name as lex22
           FROM coord_detail_info

           INNER JOIN coordinates_names ON (coordinates_names.num_model_group = coord_detail_info.num_model_group
           AND coordinates_names.model_id = coord_detail_info.model_id AND coordinates_names.group_detail_sign = 2)

           INNER JOIN lex lex1 ON (lex1.index_lex = SUBSTRING(coord_detail_info.id_detail, 1, INSTR(coord_detail_info.id_detail, ' ')-1) AND lex1.lang = 'EN')
           INNER JOIN lex lex2 ON (lex2.lex_code = RIGHT(TRIM(coord_detail_info.code_filter),5) AND lex2.lang = 'EN')


           WHERE coord_detail_info.model_id = :modelCode
           AND coord_detail_info.code_detail LIKE :subGroupCode

           ";*/


           $sql = "
           SELECT coord_detail_info.code_detail, coordinates_names.num_index, lex1.lex_name as lex11, lex2.lex_name as lex22, lex3.lex_name as lex33
           FROM coord_detail_info

           INNER JOIN coordinates_names ON (coordinates_names.num_model_group = coord_detail_info.num_model_group
           AND coordinates_names.model_id = coord_detail_info.model_id AND coordinates_names.group_detail_sign = 2)

           INNER JOIN lex lex1 ON ((lex1.index_lex = SUBSTRING(coord_detail_info.id_detail, 1, INSTR(coord_detail_info.id_detail, ' ')-1)
           OR lex1.index_lex = TRIM(coord_detail_info.id_detail)) AND lex1.lang = 'EN')
           LEFT JOIN lex lex2 ON ((lex2.lex_code = RIGHT(TRIM(coord_detail_info.code_filter),5) OR lex2.lex_code = LEFT(TRIM(coord_detail_info.code_filter),5)) AND lex2.lang = 'EN')
           LEFT JOIN lex lex3 ON (lex3.index_lex = TRIM(coord_detail_info.lex_filter) AND lex3.lang = 'EN')


           WHERE coord_detail_info.model_id = :modelCode
           AND coord_detail_info.code_detail LIKE :subGroupCode

           UNION

           SELECT coord_detail_info.code_detail, coordinates_names.num_index, lex1.lex_name as lex11, lex2.lex_name as lex22, lex3.lex_name as lex33
           FROM coord_detail_info

           INNER JOIN coordinates_names ON (coordinates_names.num_model_group = coord_detail_info.num_model_group
           AND coordinates_names.model_id = coord_detail_info.model_id AND coordinates_names.group_detail_sign = 2)

           INNER JOIN lex lex1 ON (lex1.index_lex = coord_detail_info.id_detail AND lex1.lang = 'EN')
           LEFT JOIN lex lex2 ON (lex2.lex_code = RIGHT(TRIM(coord_detail_info.code_filter),6) AND lex2.lang = 'EN')
           LEFT JOIN lex lex3 ON (lex3.index_lex = TRIM(coord_detail_info.lex_filter) AND lex3.lang = 'EN')


           WHERE coord_detail_info.model_id = :modelCode
           AND coord_detail_info.code_detail LIKE :subGroupCode


           ";

           $query = $this->conn->prepare($sql);
           $query->bindValue('modelCode',  $modelCode);
           $query->bindValue('subGroupCode',  '%'.$subGroupCode.'%');
           $query->execute();

           $aData = $query->fetchAll();




           $schemas = array();
           foreach($aData as $item)
           {

                       $schemas[$item['num_index']] = array(
                       Constants::NAME => $item['lex11'],
                       Constants::OPTIONS => array(
                           'picture' => $item['num_index'],
                           'pictureFolder' => $pictureFolder,

                           )
                   );
           }


           return $schemas;
       }

       public function getSchema($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
       {

           $schema = array();


                       $schema[$schemaCode] = array(
                       Constants::NAME => $schemaCode,
                           Constants::OPTIONS => array(
                               Constants::CD => '1'
                           )
                   );



           return $schema;
       }

       public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
       {
           $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));
           $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));


           $num_index = $options['picture'];

           $aPncs = array();


           if (strlen($pictureFolder) == 2) {

               $sqlPnc = "

           SELECT ABS(coordinates.label_name) as label_name, mcpart_un.detail_lex_index_hex
           FROM coordinates
           INNER JOIN mcpart1 ON (mcpart1.pict_index = coordinates.num_index
           AND coordinates.label_name = REPLACE(SUBSTRING_INDEX(mcpart1.param1, '.', 1), '1:', '20')-1 AND coordinates.label_name >= 10)

           INNER JOIN mcpart_un ON (mcpart_un.param1_offset = mcpart1.param1_offset)
           WHERE coordinates.num_index = :num_index

           UNION

           SELECT ABS(coordinates.label_name) as label_name, mcpart_un.detail_lex_index_hex
           FROM coordinates
           INNER JOIN mcpart1 ON (mcpart1.pict_index = coordinates.num_index
           AND coordinates.label_name = SUBSTRING_INDEX(mcpart1.param1, '-', 1))

           INNER JOIN mcpart_un ON (mcpart_un.param1_offset = mcpart1.param1_offset)
           WHERE coordinates.num_index = :num_index
           ORDER BY (1)
           ";
           }

           else {

               $sqlPnc = "

           SELECT coordinates.label_name, mcpart_un.detail_lex_index_hex
           FROM coordinates
           INNER JOIN mcpart3 ON (mcpart3.pict_index = coordinates.num_index
           AND coordinates.label_name = mcpart3.detail_code)
           INNER JOIN mcpart_un ON (mcpart_un.param1_offset = mcpart3.param1_offset)

           WHERE coordinates.num_index = :num_index
           ";
           }



           $query = $this->conn->prepare($sqlPnc);

           $query->bindValue('num_index',  $num_index);

           $query->execute();

           $aPncs = $query->fetchAll();



           foreach ($aPncs as &$aPnc)
           {

               $sqlSchemaLabels = "
           SELECT x1, x2, y1, y2
           FROM coordinates
            WHERE coordinates.num_index = :num_index
            AND coordinates.model_id = :modelCode
            AND coordinates.label_name = :pnc

           ";

               $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('modelCode',  $modelCode);
               $query->bindValue('num_index',  $num_index);
               $query->bindValue('pnc',  $aPnc['label_name']);


               $query->execute();

               $aPnc['clangjap'] = $query->fetchAll();


               unset ($aPnc);

           }



           $pncs = array();
           $str = array();
           foreach ($aPncs as $index=>$value) {
               {
                   if (!$value['clangjap'])
                   {
                       unset ($aPncs[$index]);
                   }

                   foreach ($value['clangjap'] as $item1)
                   {
                       $pncs[($value['label_name'])][Constants::OPTIONS][Constants::COORDS][($item1['x1'])] = array(
                           Constants::X2 => floor($item1['x2']),
                           Constants::Y2 => ($item1['y2']),
                           Constants::X1 => floor($item1['x1']),
                           Constants::Y1 => ($item1['y1']));

                   }



               }
           }


           foreach ($aPncs as $item) {



               $pncs[$item['label_name']][Constants::NAME] = strtoupper(trim($this->gethexdecLex($item['detail_lex_index_hex'])));



           }


            return $pncs;
       }

    public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
    {

           $pncs = array();

           return $pncs;

    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
    {

        $pncs = array();


        return $pncs;
    }



    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $pncCode, $options)
    {

        $pictureFolder = substr($modelCode, strpos($modelCode, '_')+1, strlen($modelCode));
        $modelCode = substr($modelCode, 0, strpos($modelCode, '_'));

        $num_index = $options['picture'];

        if (strlen($pictureFolder) == 2) {

            $sqlPnc = "

           SELECT mcpart1.detail_code, mcpart_un.detail_lex_index_hex
           FROM mcpart1
           INNER JOIN mcpart_un ON (mcpart_un.param1_offset = mcpart1.param1_offset)
           WHERE mcpart1.pict_index = :num_index AND SUBSTRING_INDEX(mcpart1.param1, '-', 1) = :pncCode

           UNION

           SELECT mcpart1.detail_code, mcpart_un.detail_lex_index_hex
           FROM mcpart1
           LEFT JOIN mcpart_un ON (mcpart_un.param1_offset = mcpart1.param1_offset)
           WHERE mcpart1.pict_index = :num_index AND REPLACE(SUBSTRING_INDEX(mcpart1.param1, '.', 1), '1:', '20')-1 = :pncCode AND REPLACE(SUBSTRING_INDEX(mcpart1.param1, '.', 1), '1:', '20') >= 10


          GROUP BY (1)
         ";
        }

        else
        {
            $sqlPnc = "
            SELECT mcpart1.detail_code, mcpart_un.detail_lex_index_hex
           FROM mcpart1
           INNER JOIN mcpart3 ON (mcpart3.param1_offset = mcpart1.param1_offset and mcpart3.detail_code = :pncCode)
           INNER JOIN mcpart_un ON (mcpart_un.param1_offset = mcpart3.param1_offset)

           WHERE mcpart1.pict_index = :num_index
            ";
        }


        $query = $this->conn->prepare($sqlPnc);
         $query->bindValue('num_index',  $num_index);
         $query->bindValue('pncCode',  $pncCode);


        $query->execute();

         $aArticuls = $query->fetchAll();



$articuls = array();

        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['detail_code']] = array(
                Constants::NAME => $this->gethexdecLex($item['detail_lex_index_hex']),
                Constants::OPTIONS => array(


                )
            );
            
        }


        return $articuls;
    }

    public function getGroupBySubgroup($regionCode, $modelCode, $modificationCode, $complectationCode, $subGroupCode)
    {

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");



        if ($regionCode != 'JP')
        {
            $sql = "
        SELECT PICGROUP
        FROM gsecloc_all
        WHERE FIGURE = :subgroupCode
        AND MDLDIR = :MDLDIR
        AND CATALOG = :regionCode
        ";
        }

        else
        {
            $sql = "
        SELECT PICGROUP
        FROM esecloc_jp
        WHERE FIGURE = :subgroupCode
        AND MDLDIR = :MDLDIR
        AND CATALOG = :regionCode
        ";
        }




        $query = $this->conn->prepare($sql);
        $query->bindValue('subgroupCode',  $subGroupCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('MDLDIR',  $MDLDIR);
        $query->execute();

        $groupCode = $query->fetchColumn(0);

        return $groupCode;

    }


    public function gethexdecLex ($string) {

        $decString = hexdec($string);

        $sql = "

        SELECT lex.lex_name
           FROM lex
           WHERE lex.index_lex = :decString AND lex.lang = 'EN'

            ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('decString', $decString);
        $query->execute();

        $launch = $query->fetchColumn(0);


        return  $launch;
    }




    
} 