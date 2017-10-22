<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\LexusBundle\Models;

use Catalog\CommonBundle\Components\Constants;
use Catalog\LexusBundle\Components\LexusConstants;

class LexusVinModel extends LexusCatalogModel
{

    public function getVinFinderResult($vin, $commonVinFind = false)
    {

        $sqlVin = "
        SELECT JAT.compl_code, JAT.catalog, JAT.catalog_code, JAT.model_code, shamei.model_name, shamei.models_codes, frames.vdate, frames.color_trim_code
        from johokt JAT
        INNER JOIN frames ON (frames.frame_code = JAT.frame AND frames.serial_number = RIGHT (:vin,7) AND JAT.model_code LIKE CONCAT ('%', frames.ext, '-',frames.model2, '%')
        AND frames.catalog = 'OV')
        INNER JOIN shamei ON (shamei.catalog = JAT.catalog AND shamei.catalog_code = JAT.catalog_code AND shamei.model_name LIKE CONCAT('%', 'LEXUS', '%'))
        where JAT.vin8 = SUBSTRING(:vin,1,8)
        ";

        $query = $this->conn->prepare($sqlVin);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetchAll();

        if (!$aData) {
            return null;
        }

        $complectations = $this->getVinComplectation($aData[0]['catalog'], $aData[0]['model_name'],
            $aData[0]['catalog_code']);

        $complectation = $complectations[$aData[0]['compl_code']];

        for ($i = 1; $i < 11; $i++) {
            if ($complectation['options']['OPTION' . $i]) {
                $OnlyCompl[] = $complectation['options']['OPTION' . $i];
            }
        }

        $region            = $aData[0]['catalog'];
        $model             = urlencode($aData[0]['model_name']);
        $modificationCode  = $aData[0]['catalog_code'];
        $complectationCode = $aData[0]['compl_code'];
        $result            = [
            'marka'              => 'LEXUS',
            'model'              => urlencode($aData[0]['model_name']),
            'modelf'             => ($aData[0]['model_name']),
            'modif'              => $aData[0]['models_codes'],
            'complectation'      => $OnlyCompl,
            Constants::PROD_DATE => $aData[0]['vdate'],

            'region'                  => $region,
            LexusConstants::INTCOLOR => substr($aData[0]['color_trim_code'], 0, 3),
            'cvet_salona'             => substr($aData[0]['color_trim_code'], 0, 3),
            'cvet_kuzova'             => substr($aData[0]['color_trim_code'], -4),
            'kod_complektacii'        => $complectationCode,
            'kod_modifikacii'         => $modificationCode,
        ];

        if ($commonVinFind) {
            $urlParams        = [
                'path'   => 'vin_lexus_groups',
                'params' => [
                    'regionCode'        => $region,
                    'modelCode'         => $model,
                    'modificationCode'  => $modificationCode,
                    'complectationCode' => $complectationCode,
                ],
            ];
            $removeFromResult = ['kod_complektacii', 'kod_modifikacii', 'modelf'];

            return ['result' => array_diff_key($result, array_flip($removeFromResult)), 'urlParams' => $urlParams];
        }
        return $result;
    }


    public function getVinComplectation($regionCode, $modelCode, $modificationCode)
    {
        $sql   = "
        SELECT johokt.engine1 as f1, johokt.engine2 as f2, johokt.body as f3, johokt.grade as f4, johokt.atm_mtm as f5, johokt.trans as f6,
        johokt.f1 as f7, johokt.f2 as f8, johokt.f3 as f9, johokt.f4 as f10, compl_code, model_code, prod_start, prod_end,
        kig1.desc_en ken1,
        kig2.desc_en ken2,
        kig3.desc_en ken3,
        kig4.desc_en ken4,
        kig5.desc_en ken5,
        kig6.desc_en ken6,
        kig7.desc_en ken7,
        kig8.desc_en ken8,
        kig9.desc_en ken9,
        kig10.desc_en ken10,

        tkm1.desc_en ten1,
        tkm2.desc_en ten2,
        tkm3.desc_en ten3,
        tkm4.desc_en ten4,
        tkm5.desc_en ten5,
        tkm6.desc_en ten6,
        tkm7.desc_en ten7,
        tkm8.desc_en ten8,
        tkm9.desc_en ten9,
        tkm10.desc_en ten10


        FROM johokt
        LEFT JOIN kig kig1 ON (kig1.catalog = johokt.catalog AND kig1.catalog_code = johokt.catalog_code AND kig1.type = '01' AND kig1.sign = johokt.engine1)
        LEFT JOIN kig kig2 ON (kig2.catalog = johokt.catalog AND kig2.catalog_code = johokt.catalog_code AND kig2.type = '02' AND kig2.sign = johokt.engine2)
        LEFT JOIN kig kig3 ON (kig3.catalog = johokt.catalog AND kig3.catalog_code = johokt.catalog_code AND kig3.type = '03' AND kig3.sign = johokt.body)
        LEFT JOIN kig kig4 ON (kig4.catalog = johokt.catalog AND kig4.catalog_code = johokt.catalog_code AND kig4.type = '04' AND kig4.sign = johokt.grade)
        LEFT JOIN kig kig5 ON (kig5.catalog = johokt.catalog AND kig5.catalog_code = johokt.catalog_code AND kig5.type = '05' AND kig5.sign = johokt.atm_mtm)
        LEFT JOIN kig kig6 ON (kig6.catalog = johokt.catalog AND kig6.catalog_code = johokt.catalog_code AND kig6.type = '06' AND kig6.sign = johokt.trans)
        LEFT JOIN kig kig7 ON (kig7.catalog = johokt.catalog AND kig7.catalog_code = johokt.catalog_code AND kig7.type = '07' AND kig7.sign = johokt.f1)
        LEFT JOIN kig kig8 ON (kig8.catalog = johokt.catalog AND kig8.catalog_code = johokt.catalog_code AND kig8.type = '08' AND kig8.sign = johokt.f2)
        LEFT JOIN kig kig9 ON (kig9.catalog = johokt.catalog AND kig9.catalog_code = johokt.catalog_code AND kig9.type = '09' AND kig9.sign = johokt.f3)
        LEFT JOIN kig kig10 ON (kig10.catalog = johokt.catalog AND kig10.catalog_code = johokt.catalog_code AND kig10.type = '10' AND kig10.sign = johokt.f4)

        LEFT JOIN tkm tkm1 ON (tkm1.catalog = kig1.catalog AND tkm1.catalog_code = kig1.catalog_code AND tkm1.type = kig1.type)
        LEFT JOIN tkm tkm2 ON (tkm2.catalog = kig2.catalog AND tkm2.catalog_code = kig2.catalog_code AND tkm2.type = kig2.type)
        LEFT JOIN tkm tkm3 ON (tkm3.catalog = kig3.catalog AND tkm3.catalog_code = kig3.catalog_code AND tkm3.type = kig3.type)
        LEFT JOIN tkm tkm4 ON (tkm4.catalog = kig4.catalog AND tkm4.catalog_code = kig4.catalog_code AND tkm4.type = kig4.type)
        LEFT JOIN tkm tkm5 ON (tkm5.catalog = kig5.catalog AND tkm5.catalog_code = kig5.catalog_code AND tkm5.type = kig5.type)
        LEFT JOIN tkm tkm6 ON (tkm6.catalog = kig6.catalog AND tkm6.catalog_code = kig6.catalog_code AND tkm6.type = kig6.type)
        LEFT JOIN tkm tkm7 ON (tkm7.catalog = kig7.catalog AND tkm7.catalog_code = kig7.catalog_code AND tkm7.type = kig7.type)
        LEFT JOIN tkm tkm8 ON (tkm8.catalog = kig8.catalog AND tkm8.catalog_code = kig8.catalog_code AND tkm8.type = kig8.type)
        LEFT JOIN tkm tkm9 ON (tkm9.catalog = kig9.catalog AND tkm9.catalog_code = kig9.catalog_code AND tkm9.type = kig9.type)
        LEFT JOIN tkm tkm10 ON (tkm10.catalog = kig10.catalog AND tkm10.catalog_code = kig10.catalog_code AND tkm10.type = kig10.type)

        WHERE johokt.catalog = :regionCode
        AND johokt.catalog_code = :modificationCode
        ";
        $query = $this->conn->prepare($sql);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('modificationCode', $modificationCode);
        $query->execute();
        $aData = $query->fetchAll();

        $complectations = [];

        foreach ($aData as $item) {
            $complectations[$item['compl_code']] = [
                Constants::NAME    => $item['model_code'],
                Constants::OPTIONS => [
                    'FROMDATE' => $item['prod_start'],
                    'UPTODATE' => $item['prod_end'],
                    'OPTION1'  => $item['f1'] ? str_replace(' 1', '',
                            $item['ten1']) . ': (' . $item['f1'] . ') ' . $item['ken1'] : '',
                    'OPTION2'  => $item['f2'] ? $item['ten2'] . ': (' . $item['f2'] . ') ' . $item['ken2'] : '',
                    'OPTION3'  => $item['f3'] ? $item['ten3'] . ': (' . $item['f3'] . ') ' . $item['ken3'] : '',
                    'OPTION4'  => $item['f4'] ? $item['ten4'] . ': (' . $item['f4'] . ') ' . $item['ken4'] : '',
                    'OPTION5'  => $item['f5'] ? $item['ten5'] . ': (' . $item['f5'] . ') ' . $item['ken5'] : '',
                    'OPTION6'  => $item['f6'] ? $item['ten6'] . ': (' . $item['f6'] . ') ' . $item['ken6'] : '',
                    'OPTION7'  => $item['f7'] ? $item['ten7'] . ': (' . $item['f7'] . ') ' . $item['ken7'] : '',
                    'OPTION8'  => $item['f8'] ? $item['ten8'] . ': (' . $item['f8'] . ') ' . $item['ken8'] : '',
                    'OPTION9'  => $item['f9'] ? $item['ten9'] . ': (' . $item['f9'] . ') ' . $item['ken9'] : '',
                    'OPTION10' => $item['f10'] ? $item['ten10'] . ': (' . $item['f10'] . ') ' . $item['ken10'] : '',
                    'trans'    => '',
                ],
            ];
        }
        return $complectations;
    }


}
