<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 11.01.15
 * Time: 16:58
 */

namespace Catalog\InfinitiBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\InfinitiBundle\Components\InfinitiConstants;

class InfinitiArticulModel extends InfinitiCatalogModel{

    public function getArticulRegions($articulCode){



        $sqlPnc = "
         SELECT CATALOG
          FROM catalog
          WHERE catalog.OEMCODE = :articulCode
          AND CATALOG IN ('ELINF', 'ERINF', 'CAINF', 'USINF')

         ";

        $query = $this->conn->prepare($sqlPnc);
        $query->bindValue('articulCode',  $articulCode);
        $query->execute();

        $aData = $query->fetchAll();

        $regions = array();
        foreach($aData as $index => $value)
        {
            $regions[] = $value['CATALOG'];
        }

        return $regions;

    }

    public function getArticulModels ($articul, $regionCode)
    {


        if ($regionCode !== 'JP')
        {
            $sql = "
        SELECT catalog.CATALOG, cdindex.SHASHU, cdindex.SHASHUKO
        FROM catalog
        INNER JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.ShashuCD = catalog.MDLDIR)
        INNER JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = destcnt.SHASHU)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode
        ORDER by SHASHUKO
        ";
        }

        else
        {
            $sql = "
        SELECT cdindex.SHASHU, cdindex_jp_en.SHASHUKO
        FROM catalog
        LEFT JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.ShashuCD = catalog.MDLDIR)
        LEFT JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = destcnt.SHASHU)
        LEFT JOIN cdindex_jp_en ON (cdindex.SHASHU = cdindex_jp_en.SHASHU)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode
        ORDER by SHASHUKO

        ";
        }


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);

        $query->execute();

        $aData = $query->fetchAll();

		
		$models = array();

        foreach($aData as $item)
        {
            $models[] = urlencode($item['SHASHUKO']);

        }

        return array_unique($models);
    }
    
    public function getArticulModifications($articul, $regionCode, $modelCode)
    {
        $modelCode = urldecode($modelCode);

        if ($regionCode !== 'JP')
        {
            $sql = "
        SELECT cdindex.SHASHU
        FROM catalog
        LEFT JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.ShashuCD = catalog.MDLDIR)
        LEFT JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = destcnt.SHASHU and cdindex.SHASHUKO = :modelCode)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode
        ORDER by SHASHUKO
        ";

            $query = $this->conn->prepare($sql);
            $query->bindValue('articulCode', $articul);
            $query->bindValue('regionCode', $regionCode);
            $query->bindValue('modelCode', $modelCode);
        }

        else
        {
            $sql = "
        SELECT catalog.CATALOG, cdindex.SHASHU
        FROM catalog
        left JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.ShashuCD = catalog.MDLDIR)
        left JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = destcnt.SHASHU)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode
        ORDER by SHASHU

        ";
            $query = $this->conn->prepare($sql);
            $query->bindValue('articulCode', $articul);
            $query->bindValue('regionCode', $regionCode);

        }



        $query->execute();

        $aData = $query->fetchAll();
        $modifications = array();

        foreach($aData as $item)
        {
            $modifications[] = $item['SHASHU'];

        }

        return array_unique($modifications);


    }
    
    public function getArticulComplectations($articul, $regionCode, $modelCode, $modificationCode)
    {

        $sql = "
        SELECT posname.NNO, posname.MDLDIR, posname.DATA1
        FROM catalog
        LEFT JOIN posname ON (posname.CATALOG = catalog.CATALOG AND posname.MDLDIR = catalog.MDLDIR)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode


        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->execute();

        $aData = $query->fetchAll();




            $complectations = array();

            foreach ($aData as $item) {

                    $complectations[] = str_pad($item['MDLDIR'], 3, "0", STR_PAD_LEFT) . '_' . $item['NNO']. '_' .$item['DATA1'];

            }


        return array_unique($complectations);
    }
    
    public function getArticulGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode)
    {

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");


        if ($regionCode != 'JP')
        {
            $sql = "
        SELECT gsecloc_all.PICGROUP
        FROM catalog
        left JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        left JOIN gsecloc_all ON (gsecloc_all.CATALOG = catalog.CATALOG AND gsecloc_all.MDLDIR = catalog.MDLDIR and gsecloc_all.FIGURE = SUBSTRING(pcodenes.FIGURE,1,3))
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR
        ";

        }

        else{
            $sql = "
        SELECT esecloc_jp.PICGROUP
        FROM catalog
        left JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        left JOIN esecloc_jp ON (esecloc_jp.CATALOG = catalog.CATALOG AND esecloc_jp.MDLDIR = catalog.MDLDIR and esecloc_jp.FIGURE = SUBSTRING(pcodenes.FIGURE,1,3))
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR
        ";

        }

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('MDLDIR', $MDLDIR);
        $query->execute();

        $aData = $query->fetchAll();



        $groups = array();

        foreach($aData as $item)
		{

			$groups[]= $item['PICGROUP'];

		}


        return array_unique($groups);
    }


    public function getArticulSubGroups($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode)
    {
        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");

        if ($regionCode != 'JP')
        {
            $sql = "
        SELECT gsecloc_all.FIGURE
        FROM catalog
        LEFT JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        LEFT JOIN gsecloc_all ON (gsecloc_all.CATALOG = catalog.CATALOG AND gsecloc_all.MDLDIR = catalog.MDLDIR and gsecloc_all.FIGURE = SUBSTRING(pcodenes.FIGURE,1,3)
        AND gsecloc_all.PICGROUP = :groupCode)
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR

        ";
        }

        else{
            $sql = "
        SELECT esecloc_jp.FIGURE
        FROM catalog
        LEFT JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        LEFT JOIN esecloc_jp ON (esecloc_jp.CATALOG = catalog.CATALOG AND esecloc_jp.MDLDIR = catalog.MDLDIR and esecloc_jp.FIGURE = SUBSTRING(pcodenes.FIGURE,1,3)
        AND esecloc_jp.PICGROUP = :groupCode)
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR

        ";
        }



        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('groupCode', $groupCode);
        $query->bindValue('MDLDIR', $MDLDIR);

        $query->execute();

        $aData = $query->fetchAll();
    	$subgroups = array();

        foreach($aData as $item)
        {
            $subgroups[]= $item['FIGURE'];

        }



        return array_unique($subgroups);

    }
    
    public function getArticulSchemas($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode)
    {

        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");


        $sql = "
        SELECT illnote.PIMGSTR
        FROM catalog
        LEFT JOIN pcodenes ON (pcodenes.CATALOG = catalog.CATALOG and pcodenes.MDLDIR = catalog.MDLDIR and pcodenes.PARTCODE = catalog.PARTCODE)
        LEFT JOIN illnote ON (illnote.CATALOG = catalog.CATALOG AND illnote.MDLDIR = catalog.MDLDIR and illnote.FIGURE = pcodenes.FIGURE AND illnote.SECNO = pcodenes.SECNO)
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('MDLDIR', $MDLDIR);

        $query->execute();

        $aData = $query->fetchAll();

	   
	   $schemas = array();
        foreach($aData as $item) {

                $schemas[] = strtoupper($item['PIMGSTR']);


        }

		   
        return array_unique($schemas);
    }
         
     public function getArticulPncs($articul, $regionCode, $modelCode, $modificationCode, $complectationCode, $groupCode, $subGroupCode, $schemaCode)
    {


        $MDLDIR = ltrim(substr($complectationCode, 0, strpos($complectationCode, '_')), "0");

        $sql = "
        SELECT PARTCODE
        FROM catalog
        WHERE catalog.CATALOG = :regionCode
        AND catalog.OEMCODE = :articulCode
        and catalog.MDLDIR = :MDLDIR
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('MDLDIR', $MDLDIR);

        $query->execute();

        $aData = $query->fetchAll();

        $pncs = array();
        foreach($aData as $item) {

                $pncs[] = $item['PARTCODE'];


        }

        return array_unique($pncs);
    }

    public function getArticulDesc($articul, $regionCode, $modelCode, $modificationCode)
    {




            $sql = "
        SELECT catalog.REC3
        FROM catalog
        INNER JOIN cdindex ON (cdindex.CATALOG = catalog.CATALOG AND cdindex.SHASHU = :modificationCode)
        INNER JOIN destcnt ON (destcnt.CATALOG = catalog.CATALOG AND destcnt.SHASHU = cdindex.SHASHU and destcnt.ShashuCD = catalog.MDLDIR)
        WHERE catalog.OEMCODE = :articulCode
        and catalog.CATALOG = :regionCode

        ";


        $query = $this->conn->prepare($sql);
        $query->bindValue('articulCode', $articul);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);

        $query->execute();

        $aData = $query->fetchAll();



        return $aData;
    }
} 