<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 12:14
 */

namespace Catalog\MazdaBundle\Models;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Models\CatalogModel;
use Catalog\MazdaBundle\Components\MazdaConstants;

class MazdaCatalogModel extends CatalogModel{

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
        SELECT model_name
        FROM catalog
        WHERE lang = 1
        GROUP BY model_name
        ";

        $query = $this->conn->query($sql);

        $aData = $query->fetchAll();

        $models = array();
        foreach($aData as $item){
            $models[md5($item['model_name'])] = array(Constants::NAME=>$item['model_name'], Constants::OPTIONS=>array());
        }

        return $models;
    }

    public function getModifications($regionCode, $modelCode)
    {
        $sql = "
        SELECT catalog_number, prod_year, prod_date, carline
        FROM catalog
        WHERE model_name = :modelCode AND lang = 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modelCode', $modelCode);
        $query->execute();

        $aData = $query->fetchAll();

        $modifications = array();
        foreach($aData as $item){
            $modifications[$item['catalog_number']] = array(
                Constants::NAME     => $item['catalog_number'],
                Constants::OPTIONS  => array(
                    MazdaConstants::PROD_YEAR   => $item['prod_year'],
                    MazdaConstants::PROD_DATE   => $item['prod_date'],
                    MazdaConstants::CARLINE     => $item['carline']
            ));
        }

        return $modifications;
    }

    public function getComplectations($regionCode, $modelCode, $modificationCode)
    {
        $sql = "
        SELECT CONCAT(`MDLCD`, `MSCSPCCD`) as complectation
        FROM model3
        WHERE catalog = :regionCode AND catalog_number = :modificationCode
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $complectations = array();
        foreach($aData as $item){
            $complectations[$item['complectation']] = array(
                Constants::NAME     => $item['complectation'],
                Constants::OPTIONS  => array()
            );
        }

        return $complectations;
    }

    public function getGroups($regionCode, $modelCode, $modificationCode)
    {
        $sql = "
        SELECT `id`, `descr`
        FROM pgroups
        WHERE catalog = :regionCode AND catalog_number = :modificationCode AND lang = 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();

        $aData = $query->fetchAll();

        $groups = array();
        foreach($aData as $item){
            $groups[$item['id']] = array(
                Constants::NAME     => $item['descr'],
                Constants::OPTIONS  => array()
            );
        }

        return $groups;
    }

    public function getGroupSchemas($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $sql = "
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
        }

        return $groupSchemas;
    }

    public function getSubgroups($regionCode, $modelCode, $modificationCode, $groupCode)
    {
        $sqlGroup = "
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
        }

        $sqlSubgroups = "
        SELECT sg.sgroup, sg.descr
        FROM sgroup sg
        WHERE sg.catalog = :regionCode
            AND sg.catalog_number = :modificationCode
            AND sg.pgroup = :groupCode
            AND sg.lang = 1
        ";

        $query = $this->conn->prepare($sqlSubgroups);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('groupCode', $groupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $subgroups = array();
        foreach($aData as $item){
            $subgroups[$item['sgroup']] = array(
                Constants::NAME => $item['descr'],
                Constants::OPTIONS => $labels[substr($item['sgroup'], 0, 4)] ? array(
                    Constants::X1 => $labels[substr($item['sgroup'], 0, 4)]['xs'],
                    Constants::X2 => $labels[substr($item['sgroup'], 0, 4)]['xe'],
                    Constants::Y1 => $labels[substr($item['sgroup'], 0, 4)]['ys'],
                    Constants::Y2 => $labels[substr($item['sgroup'], 0, 4)]['ye']
                ) : array()
            );
        }

        return $subgroups;
    }

    public function getSchemas($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode)
    {
        $sqlSchemas = "
        SELECT sp.cd, sp.pic_name, sp.XC26TKT1, sp.descr
        FROM sgroup_pics sp
        WHERE sp.catalog = :regionCode
            AND sp.catalog_number = :modificationCode
            AND sp.sgroup = :subGroupCode
            AND sp.lang = 1
        UNION
        SELECT s3.cd, s3.XC26ILFL, s3.XC26TKT1, null
        FROM sgroup3 s3
        WHERE s3.catalog = :regionCode
            AND s3.catalog_number = :modificationCode
            AND s3.XC26PSNO = :subGroupCode
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schemas = array();
        $add_descr = "";
        foreach($aData as $item){
            if($item['pic_name']){
                $aDescr = array();
                foreach (explode(" ", $item['XC26TKT1']) as $code) {
                    $sql = "
                    SELECT ad.descr
                    FROM additional_descs ad
                    WHERE ad.catalog = :regionCode
                        AND ad.catalog_number = :modificationCode
                        AND ad.cd = :cd
                        AND ad.id = :id
                        AND ad.lang = 1
                    LIMIT 1
                    ";

                    $query = $this->conn->prepare($sql);
                    $query->bindValue('regionCode', $regionCode);
                    $query->bindValue('modificationCode', $modificationCode);
                    $query->bindValue('cd', $item['cd']);
                    $query->bindValue('id', $code);
                    $query->execute();

                    $aDescr[] = $query->fetchColumn();

                    $add_descr = implode(" ", $aDescr);
                }

                $schemas[$item['pic_name']] = array(
                    Constants::NAME => $item['descr'] . " " . $add_descr,
                    Constants::OPTIONS => array(
                        Constants::CD => $item['cd']
                    )
                );
            }
        }

        return $schemas;
    }

    public function getSchema($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode)
    {
        $sqlSchemas = "
        SELECT sp.cd, sp.XC26TKT1, sp.descr
        FROM sgroup_pics sp
        WHERE sp.catalog = :regionCode
            AND sp.catalog_number = :modificationCode
            AND sp.sgroup = :subGroupCode
            AND sp.pic_name = :schemaCode
            AND sp.lang = 1
        UNION
        SELECT s3.cd, s3.XC26TKT1, null
        FROM sgroup3 s3
        WHERE s3.catalog = :regionCode
            AND s3.catalog_number = :modificationCode
            AND s3.XC26ILFL = :schemaCode
            AND s3.XC26PSNO = :subGroupCode
        ";

        $query = $this->conn->prepare($sqlSchemas);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aData = $query->fetchAll();

        $schema = array();
        foreach ($aData as $item) {
            $schema[$schemaCode] = array(
                Constants::NAME => $schemaCode,
                Constants::OPTIONS => array(
                    Constants::CD => $item['cd']
                )
            );
        }

        return $schema;
    }

    public function getPncs($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
        $sqlSchemaLabels = "
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

        return $pncs;
    }

    public function getCommonArticuls($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
        $sqlSchemaLabels = "
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
        }

        return $articuls;
    }

    public function getReferGroups($regionCode, $modelCode, $modificationCode, $groupCode, $subGroupCode, $schemaCode, $cd)
    {
        $sqlSchemaLabels = "
        SELECT p.part_code, p.xs, p.ys, p.xe, p.ye
        FROM pictures p
        WHERE p.catalog = :regionCode
          AND p.cd = :cd
          AND p.pic_name = :schemaCode
          AND p.XC26ECHK = 3
        ";

        $query = $this->conn->prepare($sqlSchemaLabels);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', $cd);
        $query->bindValue('schemaCode', $schemaCode);
        $query->execute();

        $aDataLabels = $query->fetchAll();

        $groups = array();
        foreach ($aDataLabels as $item) {
            $groups[$item['part_code']][Constants::NAME] = $item['part_code'];
            $groups[$item['part_code']][Constants::OPTIONS][Constants::COORDS][] = array(
                Constants::X1 => $item['xs'],
                Constants::Y1 => $item['ys'],
                Constants::X2 => $item['xe'],
                Constants::Y2 => $item['ye'],
            );
        }

        return $groups;
    }

    public function getArticuls($regionCode, $cd, $modificationCode, $subGroupCode, $pncCode)
    {
        $sqlArticuls = "
        SELECT pc.part_name, pc.quantity, pc.sdate, pc.edate, pc.desc_id
        FROM part_catalog pc
        WHERE pc.catalog = :regionCode
          AND pc.cd = :cd
          AND pc.catalog_number = :modificationCode
          AND pc.sgroup = :subGroupCode
          AND pc.dcod = :pncCode
        ";

        $query = $this->conn->prepare($sqlArticuls);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('cd', $cd);
        $query->bindValue('modificationCode', $modificationCode);
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pncCode', $pncCode);
        $query->execute();

        $aData = $query->fetchAll();

        $sqlArticulsDescr = "
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

        $articuls = array();
        foreach ($aData as $item) {
            $articuls[$item['part_name']] = array(
                Constants::NAME => isset($aDataDescr[$item['desc_id']]) ? $aDataDescr[$item['desc_id']] : "",
                Constants::OPTIONS => array(
                    Constants::QUANTITY => $item['quantity'],
                    Constants::START_DATE => $item['sdate'],
                    Constants::END_DATE => $item['edate']
                )
            );
        }

        return $articuls;
    }
} 