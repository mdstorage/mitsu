<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 17.11.14
 * Time: 23:12
 */

namespace Catalog\MitsubishiBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class VinRepository extends EntityRepository {

    public function findByVin($vin = "", $xref = "", $frame1 = "", $frame2 = ""){

        if ($frame1 == "" && $frame2 == ""){
            $chassis = 'SUBSTRING(:vin, 1, 10)';
            if ($xref == ""){
                $serialNo = 'SUBSTRING(:vin, 11)';
            } else {
                $serialNo = ':xref';
            }
        } else {
            $chassis = ':frame1';
            if ($xref == ""){
                $serialNo = ':frame2';
            } else {
                $serialNo = ':xref';
            }
        }
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('catalog', 'catalog');
        $rsm->addScalarResult('model', 'model');
        $rsm->addScalarResult('classification', 'classification');
        $rsm->addScalarResult('prodDate', 'prodDate');
        $rsm->addScalarResult('xref', 'xref');
        $rsm->addScalarResult('opc', 'opc');
        $rsm->addScalarResult('exterior', 'exterior');
        $rsm->addScalarResult('interior', 'interior');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          v.Catalog as catalog,
          v.Model as model,
          v.Classification as classification,
          v.ProdDate as prodDate,
          v.XREF as xref,
          v.OPC as opc,
          v.Exterior as exterior,
          v.Interior as interior
        FROM
          `vin` v
        WHERE
          v.Chassis = ' . $chassis . '
          AND v.SerialNo = ' . $serialNo . '
          LIMIT 1
        ', $rsm);

        if ($vin !== ""){
            $nativeQuery->setParameter('vin', $vin);
        }

        if ($xref !== ""){
            $nativeQuery->setParameter('xref', $xref);
        }

        if ($frame1 !== ""){
            $nativeQuery->setParameter('frame1', $frame1);
        }
        if ($frame2 !== ""){
            $nativeQuery->setParameter('frame2', $frame2);
        }


        try {
            $byVin = $nativeQuery->getSingleResult();
        } catch(\Doctrine\Orm\NoResultException $e) {
            $byVin = null;
        }

        return $byVin;
    }
} 