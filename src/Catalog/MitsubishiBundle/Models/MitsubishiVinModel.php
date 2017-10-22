<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\MitsubishiBundle\Models;

use Catalog\CommonBundle\Components\Constants;

class MitsubishiVinModel extends MitsubishiCatalogModel
{
    public function getVinFinderResult($vin, $commonVinFind = false)
    {
        $chassis  = substr($vin, 0, 10);
        $serialNo = substr($vin, 10);
        $sql      = "
        SELECT
          v.Catalog as catalog, vv.Catalog as catalog,
          v.Model as model, vv.Model as model,
          v.Classification as classification, vv.Classification as classification,
          v.ProdDate as prodDate, vv.ProdDate as prodDate,
          v.XREF as xref, vv.XREF as xref,
          v.OPC as opc, vv.OPC as opc,
          v.Exterior as exterior, vv.Exterior as exterior,
          v.Interior as interior, vv.Interior as interior,
          dmodel.desc_en as descEnModel,
          dmodif.desc_en as descEnModif,
          dcompl.desc_en as descEnCompl,
          m.Catalog_Num
        FROM
          `vin` v
        INNER JOIN vin vv ON (vv.Chassis = :chassis AND vv.SerialNo = v.XREF AND vv.XREF = '')

        LEFT JOIN models m ON ((m.catalog = v.catalog OR m.catalog = vv.catalog) AND (m.Model = v.model OR m.Model = vv.model) AND (m.Classification = v.classification OR m.Classification = vv.classification))
        LEFT JOIN `model_desc` md ON m.Catalog_Num = TRIM(md.catalog_num)
        LEFT JOIN `descriptions` dmodel ON (TRIM(md.name) = dmodel.TS AND dmodel.catalog = v.catalog)

        LEFT JOIN `descriptions` dmodif ON (TRIM(m.Name1) = dmodif.TS AND dmodif.catalog = v.catalog)

        LEFT JOIN `descriptions` dcompl ON (TRIM(m.Name) = dcompl.TS AND dcompl.catalog = v.catalog)

        WHERE
          v.Chassis = :chassis
          AND v.SerialNo = :serialNo
          LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('chassis', $chassis);
        $query->bindValue('serialNo', $serialNo);
        $query->execute();

        $aData = $query->fetch();

        if (!$aData) {
            return null;
        }

        $region          = $aData['catalog'];
        $model_for_group = $aData['Catalog_Num'];
        $modif_for_group = $aData['model'];
        $compl_for_group = $aData['classification'];

        $result = [
            'marka'           => 'MITSUBISHI',
            'model'           => $aData['descEnModel'],
            'model_for_group' => $model_for_group,
            'modif'           => '(' . $aData['model'] . ') ' . $aData['descEnModif'],
            'modif_for_group' => $modif_for_group,
            'compl'           => '(' . $aData['classification'] . ') ' . $aData['descEnCompl'],
            'compl_for_group' => $compl_for_group,
            Constants::PROD_DATE => $aData['prodDate'],
            'region'             => $region,
            'exterior'           => $aData['exterior'],
            'interior'           => $aData['interior'],
        ];

        if ($commonVinFind) {
            $urlParams        = [
                'path'   => 'vin_mitsubishi_groups',
                'params' => [
                    'regionCode'        => $region,
                    'modelCode'         => $model_for_group,
                    'modificationCode'  => $modif_for_group,
                    'complectationCode' => $compl_for_group,
                ],
            ];
            $removeFromResult = ['compl_for_group', 'modif_for_group', 'model_for_group'];

            return ['result' => array_diff_key($result, array_flip($removeFromResult)), 'urlParams' => $urlParams];
        }

        return $result;
    }
} 
