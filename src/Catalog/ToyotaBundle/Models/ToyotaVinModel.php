<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\ToyotaBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\ToyotaBundle\Components\ToyotaConstants;

class ToyotaVinModel extends ToyotaCatalogModel {

    public function getVinFinderResult($vin)
    {


        $sqlMinSerial = "
        SELECT MIN(ABS(vindat.SERIAL - SUBSTRING(:vin,12,6))) as minimum
        from vindat
        where vindat.vin = SUBSTRING(:vin,1,11)
        AND vindat.SERIAL = SUBSTRING(:vin,12,6)
        ";

        $query = $this->conn->prepare($sqlMinSerial);
        $query->bindValue('vin', $vin);
        $query->execute();

        $sMinimumSerial = $query->fetchColumn(0);


        $sqlMin = "
        SELECT MIN(ABS(vindat.MDLPOS - mdlcode.POSDATA)) as minimum
        from vindat, mdlcode
        where vindat.vin = SUBSTRING(:vin,1,11)
        AND vindat.SERIAL = SUBSTRING(:vin,12,6)
        AND ABS(vindat.SERIAL - SUBSTRING(:vin,12,6)) = :MinimumSerial
        AND vindat.CDNAME = mdlcode.CDNAME
        AND vindat.CATALOG = mdlcode.CATALOG

        ";

        $query = $this->conn->prepare($sqlMin);
        $query->bindValue('vin', $vin);
        $query->bindValue('MinimumSerial', $sMinimumSerial);
        $query->execute();

        $sMinimum = $query->fetchColumn(0);

        $sql = "
        SELECT vindat.CATALOG, vindat.PRODYM, vindat.COLOR1, vindat.COLOR2, mdlcode.MODSERIES, mdlcode.POSNUM, destcnt.ShashuCD as MDLDIR, posname.DATA1, cdindex.SHASHUKO, COLOR1.DESCRSTR AS COL1,
        COLOR2.DESCRSTR AS COL2
        from vindat
        LEFT JOIN mdlcode ON (vindat.CDNAME = mdlcode.CDNAME AND ABS(vindat.MDLPOS - mdlcode.POSDATA) = :minimum AND vindat.CATALOG = mdlcode.CATALOG)
        LEFT JOIN destcnt ON (destcnt.CATALOG = vindat.CATALOG AND destcnt.SHASHU = mdlcode.MODSERIES)
        LEFT JOIN posname ON (posname.CATALOG = destcnt.CATALOG  AND posname.MDLDIR = destcnt.ShashuCD AND posname.NNO = mdlcode.POSNUM)
        LEFT JOIN cdindex ON (cdindex.CATALOG = vindat.CATALOG and cdindex.SHASHU = mdlcode.MODSERIES)
        LEFT join abbrev COLOR1 ON (COLOR1.CATALOG = vindat.CATALOG and COLOR1.MDLDIR = destcnt.ShashuCD and COLOR1.ABBRSTR = CONCAT('C', vindat.COLOR1))
        LEFT join abbrev COLOR2 ON (COLOR2.CATALOG = vindat.CATALOG and COLOR2.MDLDIR = destcnt.ShashuCD and COLOR2.ABBRSTR = CONCAT('C#', vindat.COLOR2))


        where vindat.vin = SUBSTRING(:vin,1,11)
        AND vindat.SERIAL = SUBSTRING(:vin,12,6)
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->bindValue('minimum', $sMinimum);

        $query->execute();

        $aData = $query->fetchAll();
        $OnlyCompl = array();



        $complectations = $this->getComplectations($aData[0]['CATALOG'], $aData[0]['SHASHUKO'], $aData[0]['MODSERIES']);
        $complectation = $complectations[str_pad($aData[0]['MDLDIR'], 3, "0", STR_PAD_LEFT).'_'.$aData[0]['POSNUM'].'_'.$aData[0]['DATA1']];


       for ($i = 1; $i < 9; $i++)
        {
            $OnlyCompl[] = $complectation['options']['OPTION'.$i];
        }




        $result = array();

        if ($aData) {
            $result = array(
                'model' => urlencode($aData[0]['SHASHUKO']),
                'modif' => $aData[0]['MODSERIES'],
                'complectation' => $OnlyCompl,
                Constants::PROD_DATE => $aData[0]['PRODYM'],

                'region' => $aData[0]['CATALOG'],
                ToyotaConstants::INTCOLOR => $aData[0]['COLOR1'],
                'cvet_salona' => $aData[0]['COL1'],
                'cvet_kuzova' => $aData[0]['COL2'],
                'kod_complektacii' => str_pad($aData[0]['MDLDIR'], 3, "0", STR_PAD_LEFT).'_'.$aData[0]['POSNUM'].'_'.$aData[0]['DATA1'],
                );
        }



        return $result;
    }


} 