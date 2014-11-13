<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 13.11.14
 * Time: 16:46
 */

namespace Catalog\MitsubishiBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class PartCatalogRepository extends EntityRepository
{
    public function getPncsByModel($catalog, $model, $mainGroup, $subGroup, $classification)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('pnc', 'pnc');
        $rsm->addScalarResult('partNumber', 'partNumber');

        $nativeQuery = $em->createNativeQuery('
            SELECT
              pg.PNC as pnc,
              pg.PartNumber as partNumber
            FROM
              `part_catalog` pg
            WHERE
              pg.catalog = :catalog
              AND pg.Model = :model
              AND pg.MainGroup = :mainGroup
              AND pg.SubGroup = :subGroup
              AND (pg.Classification = :classification OR pg.Classification = "")
        ', $rsm)
        ->setParameter('catalog', $catalog)
        ->setParameter('model', $model)
        ->setParameter('mainGroup', $mainGroup)
        ->setParameter('subGroup', $subGroup)
        ->setParameter('classification', $classification);

        $nativeResult = $nativeQuery->getResult();

        $pncs = array();
        foreach ($nativeResult as $item){
            $pncs[$item['pnc']][] = $item['partNumber'];
        }

        return $pncs;
    }
} 