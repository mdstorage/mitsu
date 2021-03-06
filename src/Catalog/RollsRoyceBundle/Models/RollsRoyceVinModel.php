<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\RollsRoyceBundle\Models;

use Catalog\CommonBundle\Components\Constants;

class RollsRoyceVinModel extends RollsRoyceCatalogModel
{

    public function getVinFinderResult($vin, $commonVinFind = false)
    {
        $vin = substr($vin, strlen($vin) - 7, 7);

        $sql = "
       select distinct
fgstnr_mospid Modellspalte,
fgstnr_typschl Typ,
fgstnr_werk Werk,
baureihe_marke_tps Marke,
baureihe_produktart Produktart,
fztyp_vbereich Katalogumfang,
fztyp_baureihe Baureihe,
b.ben_text ExtBaureihe,
baureihe_bauart Bauart,
bb.ben_text ExtBauart,
fztyp_karosserie Karosserie,
bk.ben_text ExtKarosserie,
fztyp_motor Motor,
fztyp_erwvbez Modell,
fztyp_ktlgausf Region,
fztyp_lenkung Lenkung,
fztyp_getriebe Getriebe,
fgstnr_prod Produktionsdatum,
fztyp_sichtschutz Sichtschutz
from w_fgstnr
inner join w_fztyp on (fgstnr_typschl = fztyp_typschl and fgstnr_mospid = fztyp_mospid)
inner join w_baureihe on (fztyp_baureihe = baureihe_baureihe AND w_baureihe.baureihe_marke_tps = 'Rolls-Royce')
inner join w_publben pk on (fztyp_karosserie = pk.publben_bezeichnung and pk.publben_art = 'K')
inner join w_publben pb on (baureihe_bauart = pb.publben_bezeichnung and pb.publben_art = 'B')
inner join w_ben_gk b on (baureihe_textcode = b.ben_textcode and b.ben_iso = 'ru' and b.ben_regiso = '  ')
inner join w_ben_gk bk on (pk.publben_textcode = bk.ben_textcode and bk.ben_iso = 'ru' and bk.ben_regiso = '  ')
inner join w_ben_gk bb on (pb.publben_textcode = bb.ben_textcode and bb.ben_iso = 'ru' and bb.ben_regiso = '  ')
where fgstnr_von <= :vin and fgstnr_bis >= :vin and fgstnr_anf  = :subVin
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->bindValue('subVin', substr($vin, 0, 2));
        $query->execute();

        $aData = $query->fetch();

        if (!$aData) {
            return null;
        }

        $region            = $aData['Region'];
        $modelforGroups    = $aData['Baureihe'] . '_' . $aData['Karosserie'];
        $modifforGroups    = $aData['Modellspalte'];
        $complectationCode = $aData['Lenkung'] . $aData['Getriebe'] . $aData['Produktionsdatum'];

        $result = [
            'marka'              => 'RollsRoyce',
            'region'             => $region,
            'model'              => $aData['ExtBaureihe'],
            'modif'              => $aData['Modell'],
            Constants::PROD_DATE => $aData['Produktionsdatum'],
            'wheel'              => $aData['Lenkung'],
            'modelforGroups'     => $modelforGroups,
            'modifforGroups'     => $modifforGroups,
            'complectationCode'  => $complectationCode,
            'engine'             => $aData['Motor'],
            'korobka'            => $aData['Getriebe'],
        ];

        if ($commonVinFind) {
            $urlParams        = [
                'path'   => 'vin_rollsroyce_groups',
                'params' => [
                    'regionCode'        => $region,
                    'modelCode'         => $modelforGroups,
                    'modificationCode'  => $modifforGroups,
                    'complectationCode' => $complectationCode,
                ],
            ];
            $removeFromResult = ['modelforGroups', 'modifforGroups'];

            return ['result' => array_diff_key($result, array_flip($removeFromResult)), 'urlParams' => $urlParams];
        }
        return $result;
    }


} 
