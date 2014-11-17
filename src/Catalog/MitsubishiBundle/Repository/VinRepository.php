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

    public function findByVin($vin, $xref = ""){
        if ($xref == ""){
            $serialNo = 'SUBSTRING(:vin, 11)';
        } else {
            $serialNo = ':xref';
        }
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('catalog', 'catalog');
        $rsm->addScalarResult('model', 'model');
        $rsm->addScalarResult('classification', 'classification');
        $rsm->addScalarResult('xref', 'xref');
        $rsm->addScalarResult('opc', 'opc');
        $rsm->addScalarResult('exterior', 'exterior');
        $rsm->addScalarResult('interior', 'interior');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          v.Catalog as catalog,
          v.Model as model,
          v.Classification as classification,
          v.XREF as xref,
          v.OPC as opc,
          v.Exterior as exterior,
          v.Interior as interior
        FROM
          `vin` v
        WHERE
          v.Chassis = SUBSTRING(:vin, 1, 10)
          AND v.SerialNo = '. $serialNo .'
          LIMIT 1
        ', $rsm)
            ->setParameter('vin', $vin);

        if ($xref !== ""){
            $nativeQuery->setParameter('xref', $xref);
        }

        $byVin = $nativeQuery->getSingleResult();

        return $byVin;
    }
} 