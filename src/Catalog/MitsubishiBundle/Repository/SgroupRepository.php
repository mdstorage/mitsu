<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 12.11.14
 * Time: 17:33
 */

namespace Catalog\MitsubishiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class SgroupRepository extends EntityRepository
{
    public function getSgroupsByMgroup($catalog, $catalogNum, $model, $mainGroup, $classification)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('illustration', 'illustration');
        $rsm->addScalarResult('subGroup', 'subGroup');
        $rsm->addScalarResult('descEn', 'descEn');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          s.SubGroup as subGroup,
          s.Illustration as illustration,
          d.desc_en as descEn
        FROM
          `sgroup` s
        LEFT JOIN `descriptions` d ON d.TS = s.TS
        WHERE
          s.Catalog = :catalog
          AND s.`Catalog Num` = :catalogNum
          AND s.Model = :model
          AND s.MainGroup = :mainGroup
          AND (s.Classicfication = "" OR s.Classicfication = :classification)
          AND d.catalog = :catalog
          GROUP BY s.SubGroup
        ', $rsm)
            ->setParameter('catalog', $catalog)
            ->setParameter('catalogNum', $catalogNum)
            ->setParameter('model', $model)
            ->setParameter('classification', $classification)
            ->setParameter('mainGroup', $mainGroup);

        $sgroups = $nativeQuery->getResult();

        return $sgroups;
    }
} 