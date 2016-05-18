<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\VolvoBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\VolvoBundle\Components\VolvoConstants;

class VolvoCatalogModel extends CatalogModel{

    public function getRegions(){


        $sql = "
        SELECT *
        FROM partnergroup
        ";

        $query = $this->conn->prepare($sql);

        $query->execute();

        $aData = $query->fetchAll();



        $regions = array();
        foreach($aData as $index => $value)
        {
            $regions[$value['Id']] = array(Constants::NAME=>$value['Cid'], Constants::OPTIONS=>array());
        }


        return $regions;

    }

    public function getModels($regionCode)
    {

        $sql = "
        SELECT VM.Id, VM.Description
        FROM vehiclemodel VM
        LEFT JOIN vehicleprofile VP ON VP.fkVehicleModel = VM.Id
        WHERE VP.fkPartnerGroup = :regionCode
        ORDER BY VM.Description
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode',  $regionCode);

        $query->execute();

        $aData = $query->fetchAll();


        $models = array();
        foreach($aData as $item){

                    $models[$item['Id']] =
                        array(Constants::NAME=>strtoupper($item['Description']),
                            Constants::OPTIONS=>array());

        }


        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {

        $sql = "
        SELECT DISTINCT MY.Id, MY.Cid, MY.Description
        FROM modelyear MY
        INNER JOIN vehicleprofile VP ON MY.Id = VP.fkModelYear
        WHERE VP.fkPartnerGroup = :regionCode AND VP.fkVehicleModel = :modelCode
        ORDER BY MY.Description
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);

        $query->execute();

        $aData = $query->fetchAll();


        $modifications = array();




            foreach($aData as $item)
            {
                    $modifications[$item['Id']] = array(
                        Constants::NAME     => $item['Description'],
                        Constants::OPTIONS  => array());

            }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
   
    {

        $sql = "
        SELECT DISTINCT ENG.Id eid, ENG.Cid ecid, ENG.Description ed, TRANS.Id tid, TRANS.Cid tcid, TRANS.Description td
        FROM vehicleprofile VP
        INNER JOIN engine ENG ON ENG.Id = VP.fkEngine
        INNER JOIN transmission TRANS ON (TRANS.Id = VP.fkTransmission)
        WHERE VP.fkPartnerGroup = :regionCode
        AND VP.fkVehicleModel = :modelCode
        AND VP.fkModelYear IN (:modificationCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $aDataTrans = array();
        $aDataEng = array();
        $result = array();

        foreach ($aData as $item)
        {
            if ($item['tid'] != null)
                $aDataTrans[$item['tid']] = $item['td'];

            if ($item['eid'] != null)
                $aDataEng[$item['eid']] = $item['ed'];
        }



        $sqldtr = "
        SELECT DISTINCT STR.Id sid, STR.Cid scid, STR.Description sd, BS.Id bid, BS.Cid bcid, BS.Description bd,
        SPV.Id spvid, SPV.Cid spvcid, SPV.Description spvd
        from vehicleprofile VP
        LEFT JOIN steering STR ON STR.Id = VP.fkSteering
        LEFT JOIN bodystyle BS  ON BS.Id = VP.fkBodyStyle
        LEFT JOIN specialvehicle SPV ON SPV.Id = VP.fkSpecialVehicle
        WHERE VP.fkPartnerGroup = :regionCode
        AND VP.fkVehicleModel = :modelCode
        AND VP.fkModelYear IN (:modificationCode)
        ";
        $query = $this->conn->prepare($sqldtr);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aDataAll = $query->fetchAll();
        $aDataRole = array();
        $aDataKuzov = array();
        $aDataSpec = array();

        foreach ($aDataAll as $item)
        {
            if ($item['sid'] != null)
                $aDataRole[$item['sid']] = $item['sd'];

            if ($item['bid'] != null)
                $aDataKuzov[$item['bid']] = $item['bd'];

            if ($item['spvid'] != null)
                $aDataSpec[$item['spvid']] = $item['spvd'];

        }

        if ($aDataEng)
            $result['EN'] = $aDataEng;

        if ($aDataTrans)
            $result['KP'] = $aDataTrans;

        if ($aDataRole)
            $result['RU'] = $aDataRole;

        if (($aDataKuzov))
            $result['TK'] = $aDataKuzov;

        if ($aDataSpec)
            $result['ST'] = $aDataSpec;






        foreach ($result as $index => $value) {

            $complectations[($index)] = array(
                Constants::NAME => $value,
                Constants::OPTIONS => array('option1'=>$value)
            );
        }

        return $complectations;


     
    }

    public function getEngine($regionCode, $modelCode, $modificationCode)

    {
        $sql = "
        SELECT DISTINCT ENG.Id eid, ENG.Cid ecid, ENG.Description ed
        FROM engine ENG
        INNER JOIN vehicleprofile VP ON ENG.Id = VP.fkEngine
        WHERE VP.fkPartnerGroup = :regionCode
        AND VP.fkVehicleModel = :modelCode
        AND VP.fkModelYear IN (:modificationCode)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $engine = array();
        $aData = $query->fetchAll();




        foreach ($aData as &$item) {


            $engine[$item['eid']] = array(
                Constants::NAME => $item['ed'],
                Constants::OPTIONS => array()
            );
        }


        return $engine;

    }

    public function getComplectationsKorobka($regionCode, $modelCode, $modificationCode, $priznak, $engine)
    {

        $sqleng = "
        SELECT DISTINCT ENG.Id eid, ENG.Cid ecid, ENG.Description ed, TRANS.Id tid, TRANS.Cid tcid, TRANS.Description td
        FROM vehicleprofile VP
        INNER JOIN engine ENG ON ENG.Id = VP.fkEngine
        INNER JOIN transmission TRANS ON (TRANS.Id = VP.fkTransmission)
        WHERE VP.fkPartnerGroup = :regionCode
        AND VP.fkVehicleModel = :modelCode
        AND VP.fkModelYear IN (:modificationCode)
        ";

        $query = $this->conn->prepare($sqleng);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        foreach ($aData as $item)
        {
            if ($item['eid'] != null)
                $aDataEng[$item['eid']] = $item['ed'];
        }


        $sql = "
        SELECT DISTINCT TRANS.Id tid, TRANS.Cid tcid, TRANS.Description td
        FROM  transmission TRANS
        INNER JOIN vehicleprofile VP ON (TRANS.Id = VP.fkTransmission)
        WHERE VP.fkPartnerGroup = :regionCode
        AND VP.fkVehicleModel = :modelCode
        AND VP.fkModelYear IN (:modificationCode)
        AND VP.fkEngine = :engine
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->bindValue('engine', $engine);
        $query->execute();

        $complectations = array();
        $result = array();
        $aData = $query->fetchAll();
        $aDataTrans = array();

        foreach ($aData as $item)
        {
            if ($item['tid'] != null)
                $aDataTrans[$item['tid']] = $item['td'];
        }



        $sqldtr = "
        SELECT DISTINCT STR.Id sid, STR.Cid scid, STR.Description sd, BS.Id bid, BS.Cid bcid, BS.Description bd,
        SPV.Id spvid, SPV.Cid spvcid, SPV.Description spvd
        from vehicleprofile VP
        LEFT JOIN steering STR ON STR.Id = VP.fkSteering
        LEFT JOIN bodystyle BS  ON BS.Id = VP.fkBodyStyle
        LEFT JOIN specialvehicle SPV ON SPV.Id = VP.fkSpecialVehicle
        WHERE VP.fkPartnerGroup = :regionCode
        AND VP.fkVehicleModel = :modelCode
        AND VP.fkModelYear IN (:modificationCode)
        ";
        $query = $this->conn->prepare($sqldtr);
        $query->bindValue('modelCode',  $modelCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('modificationCode',  $modificationCode);
        $query->execute();

        $aDataAll = $query->fetchAll();
        $aDataRole = array();
        $aDataKuzov = array();
        $aDataSpec = array();

        foreach ($aDataAll as $item)
        {
            if ($item['sid'] != null)
            $aDataRole[$item['sid']] = $item['sd'];

            if ($item['bid'] != null)
                $aDataKuzov[$item['bid']] = $item['bd'];

            if ($item['spvid'] != null)
                $aDataSpec[$item['spvid']] = $item['spvd'];

        }



        if ($aDataEng)
            $result['EN'] = $aDataEng;

        if ($aDataTrans)
            $result['KP'] = $aDataTrans;

        if ($aDataRole)
            $result['RU'] = $aDataRole;

        if (($aDataKuzov))
            $result['TK'] = $aDataKuzov;

        if ($aDataSpec)
            $result['ST'] = $aDataSpec;






        foreach ($result as $index => $value) {

            $complectations[($index)] = array(
                Constants::NAME => $value,
                Constants::OPTIONS => array('option1'=>$value)
            );
        }

        return $complectations;

    }

    public function getGroups($regionCode, $modelCode, $modificationCode, $complectationCode)
    {


        $complectationCode = json_decode(base64_decode($complectationCode), true);


        $sql = "
        SELECT major_group.MAJOR_GROUP, major_group.MAJOR_DESC, group_master.GROUP_ID
        FROM major_group, group_usage, group_master
        WHERE group_usage.CATALOG_CODE = :catalogCode
        and group_usage.GROUP_ID = group_master.GROUP_ID and group_master.MAJOR_GROUP = major_group.MAJOR_GROUP and group_usage.GROUP_TYPE = 'B'
        ORDER BY (1)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catalogCode',  $catalogCode);
        $query->execute();

        $aData = $query->fetchAll();


        $groups = array();


        foreach($aData as $item){

            $groups[$item['MAJOR_GROUP']] = array(
                Constants::NAME     => $item ['MAJOR_DESC'],
                Constants::OPTIONS => array()
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

           $subgroups = array();

               $subgroups['1'] = array(

                   Constants::NAME => '1',
                   Constants::OPTIONS => array()

               );

           return $subgroups;
       }

       public function getSchemas($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
       {


           $catalogCode = substr($complectationCode, 0, strpos($complectationCode, '_'));
           $year = $modificationCode;




           $sql = "
        SELECT callout_legend.ART_NBR, CAPTION_DESC, callout_legend.IMAGE_NAME
        FROM callout_legend, category, art, caption
        WHERE callout_legend.CATALOG_CODE = :catalogCode and CAPTION_GROUP = :groupCode
        and :year BETWEEN CAPTION_FIRST_YEAR AND CAPTION_LAST_YEAR
        and category.CATEGORY_ID = art.CATEGORY_ID and art.ART_ID = callout_legend.ART_ID
        AND caption.ART_NBR = callout_legend.ART_NBR
        and :year BETWEEN caption.FIRST_YEAR AND caption.LAST_YEAR
        AND caption.COUNTRY_LANG = 'EN'
        AND caption.CATALOG_CODE = callout_legend.CATALOG_CODE
        GROUP BY callout_legend.ART_NBR
        ";


           $query = $this->conn->prepare($sql);
           $query->bindValue('catalogCode',  $catalogCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('year',  $year);

           $query->execute();

           $aData = $query->fetchAll();



           $schemas = array();

           foreach($aData as $item)
           {

               $schemas[$item['ART_NBR']] = array(

                   Constants::NAME => $item['CAPTION_DESC'],
                   Constants::OPTIONS => array('IMAGE_NAME' => urlencode($item['IMAGE_NAME']))

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
                               Constants::CD => $schemaCode
                           )
                   );



           return $schema;
       }

       public function getPncs($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $options)
       {
           $catalogCode = substr($complectationCode, 0, strpos($complectationCode, '_'));
           $year = $modificationCode;

           $sql = "
        SELECT (callout_legend.CALLOUT_NBR) CALLOUT_NBR, part_usage_lang.PART_NAME, callout_legend.IMAGE_NAME
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND part_usage.PART_TYPE NOT LIKE 'Z' AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*'))
        LEFT JOIN part_usage_lang ON (part_usage_lang.PART_USAGE_LANG_ID = part_usage.PART_USAGE_LANG_ID AND part_usage_lang.COUNTRY_LANG = 'EN')
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode



        UNION
        SELECT (callout_legend.CALLOUT_NBR) CALLOUT_NBR, SUBSTRING_INDEX(part_v.part_desc, ',', 1) as PART_NAME, callout_legend.IMAGE_NAME
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND part_usage.PART_TYPE LIKE 'Z' AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*'))
        INNER JOIN part_v ON (part_v.PART_NBR = part_usage.PART_NBR AND part_v.COUNTRY_LANG = 'EN' and part_v.CATALOG_CODE = callout_legend.CATALOG_CODE and part_v.COUNTRY_CODE = part_usage.COUNTRY_CODE
        and (callout_legend.ORIG_MINOR_GROUP IS NULL OR part_v.MINOR_GROUP = callout_legend.ORIG_MINOR_GROUP))
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode

        UNION
        SELECT (callout_legend.CALLOUT_NBR) CALLOUT_NBR, callout_model_lang.CALLOUT_NOUN as PART_NAME, callout_legend.IMAGE_NAME
        FROM callout_legend
        INNER JOIN callout_model ON (callout_model.CALLOUT_ID = callout_legend.CALLOUT_ID)
        INNER JOIN callout_model_lang ON (callout_model_lang.CALLOUT_MODEL_LANG_ID = callout_model.CALLOUT_MODEL_LANG_ID
         AND callout_model_lang.COUNTRY_LANG = 'EN')
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode


        ORDER BY (1)
        ";




           $query = $this->conn->prepare($sql);
           $query->bindValue('catalogCode',  $catalogCode);
           $query->bindValue('groupCode',  $groupCode);
           $query->bindValue('regionCode',  $regionCode);
           $query->bindValue('schemaCode',  $schemaCode);
           $query->bindValue('year', $year);

           $query->execute();


           $aPncs = $query->fetchAll();





           foreach ($aPncs as &$aPnc)
           {

               $sqlSchemaLabels = "
           SELECT x, y
           FROM coord
            WHERE coord.IMAGE_NAME_KEY = :IMAGE_NAME
            AND coord.LABEL_NAME = :pnc
           ";

               $query = $this->conn->prepare($sqlSchemaLabels);
               $query->bindValue('IMAGE_NAME',  $aPnc['IMAGE_NAME']);
               $query->bindValue('pnc',  str_pad($aPnc['CALLOUT_NBR'], 5, "0", STR_PAD_LEFT));

               $query->execute();

               $aPnc['clangjap'] = $query->fetchAll();


               unset ($aPnc);

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
                     /*  if ($value['PART_NAME'] != NULL)*/
                       $pncs[($value['CALLOUT_NBR'])][Constants::OPTIONS][Constants::COORDS][($item1['x'])] = array(
                           Constants::X2 => floor($item1['x'])+30,
                           Constants::Y2 => $item1['y']+30,
                           Constants::X1 => floor($item1['x'])-30,
                           Constants::Y1 => ($item1['y'])-30);

                   }



               }
           }


           foreach ($aPncs as $item) {

             /*  if ($item['PART_NAME'] != NULL)*/

               $pncs[$item['CALLOUT_NBR']][Constants::NAME] = strtoupper($item['PART_NAME']);

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
     /*   $catCode = substr($modificationCode, strpos($modificationCode, '_')+1, strlen($modificationCode));


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

    public function getArticuls($regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode, $pncCode, $options)
    {
        $catalogCode = substr($complectationCode, 0, strpos($complectationCode, '_'));
        $year = $modificationCode;


        $sql = "
        SELECT part_usage.PART_NBR, part_usage_lang.PART_DESC, part_usage.FIRST_YEAR, part_usage.LAST_YEAR, part_usage.QUANTITY
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND part_usage.PART_TYPE NOT LIKE 'Z' AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*'))
        LEFT JOIN part_usage_lang ON (part_usage_lang.PART_USAGE_LANG_ID = part_usage.PART_USAGE_LANG_ID AND part_usage_lang.COUNTRY_LANG = 'EN')
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode AND callout_legend.CALLOUT_NBR = :pnc

        UNION
        SELECT part_v.PART_NBR, part_v.PART_DESC, part_v.FIRST_YEAR, part_v.LAST_YEAR, part_v.QUANTITY
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND part_usage.PART_TYPE LIKE 'Z' AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*'))
        INNER JOIN part_v ON (part_v.PART_NBR = part_usage.PART_NBR AND part_v.COUNTRY_LANG = 'EN' and part_v.CATALOG_CODE = callout_legend.CATALOG_CODE and part_v.COUNTRY_CODE = part_usage.COUNTRY_CODE
        and (callout_legend.ORIG_MINOR_GROUP IS NULL OR part_v.MINOR_GROUP = callout_legend.ORIG_MINOR_GROUP))
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode AND callout_legend.CALLOUT_NBR = :pnc

        UNION
        SELECT part_usage.PART_NBR, callout_model_lang.CALLOUT_DESC PART_DESC, callout_legend.FIRST_YEAR, callout_legend.LAST_YEAR, callout_legend.QUANTITY
        FROM callout_legend
        INNER JOIN part_usage ON (callout_legend.PART_USAGE_ID = part_usage.PART_USAGE_ID AND (part_usage.COUNTRY_CODE = :regionCode OR part_usage.COUNTRY_CODE = '*'))
        INNER JOIN callout_model ON (callout_model.CALLOUT_ID = callout_legend.CALLOUT_ID)
        INNER JOIN callout_model_lang ON (callout_model_lang.CALLOUT_MODEL_LANG_ID = callout_model.CALLOUT_MODEL_LANG_ID
         AND callout_model_lang.COUNTRY_LANG = 'EN')
        WHERE callout_legend.CATALOG_CODE = :catalogCode and callout_legend.CAPTION_GROUP = :groupCode
        and :year BETWEEN callout_legend.FIRST_YEAR AND callout_legend.LAST_YEAR
        AND callout_legend.ART_NBR = :schemaCode AND callout_legend.CALLOUT_NBR = :pnc

        ORDER BY (1)
        ";




        $query = $this->conn->prepare($sql);
        $query->bindValue('catalogCode',  $catalogCode);
        $query->bindValue('groupCode',  $groupCode);
        $query->bindValue('regionCode',  $regionCode);
        $query->bindValue('schemaCode',  $schemaCode);
        $query->bindValue('year',  $year);
        $query->bindValue('pnc', $pncCode);

        $query->execute();


         $aArticuls = $query->fetchAll();



$articuls = array();

        foreach ($aArticuls as $item) {
        	 
            
            
				$articuls[$item['PART_NBR']] = array(
                Constants::NAME => $item['PART_DESC'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['QUANTITY'],
                    Constants::START_DATE => $item['FIRST_YEAR'],
                    Constants::END_DATE => $item['LAST_YEAR'],

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