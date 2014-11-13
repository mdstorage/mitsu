<?php

namespace Catalog\MitsubishiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class MgroupRepository extends EntityRepository
{
    public function getMgroupsIllustration($catalog, $catalogNum, $model, $classification)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('illustration', 'illustration');

        $nativeQuery = $em->createNativeQuery('
            SELECT
                mg.Illustration as illustration
            FROM `mgroup` mg
            LEFT JOIN `descriptions` d ON d.TS = mg.TS
            WHERE mg.catalog = :catalog
            AND   mg.Catalog_Num = :catalogNum
            AND   mg.Model = :model
            LIMIT 1
        ', $rsm)
        ->setParameter('catalog', $catalog)
        ->setParameter('catalogNum', $catalogNum)
        ->setParameter('model', $model)
        ->setParameter('classification', $classification);

        try {
            $illustration = $nativeQuery->getSingleScalarResult();
        } catch(\Doctrine\Orm\NoResultException $e) {
            $illustration = null;
            echo $e->getMessage();
        }

        return $illustration;
    }

    public function getMgroupsByModel($catalog, $catalogNum, $model, $complectation)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();


        $rsm->addScalarResult('MainGroup', 'MainGroup');
        $rsm->addScalarResult('descEn', 'descEn');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          CONCAT(mg.MainGroup, SUBSTRING(mg.MajorGroup, 1, 1)) as MainGroup,
          d.desc_en as descEn
        FROM
          `mgroup` mg
        LEFT JOIN `descriptions` d ON mg.TS = d.TS
        WHERE mg.catalog = :catalog
        AND mg.Catalog_Num = :catalogNum
        AND mg.Model = :model
        AND (mg.Complectation = :complectation OR mg.Complectation = "")
        AND d.catalog = :catalog
        GROUP BY CONCAT(mg.MainGroup, SUBSTRING(mg.MajorGroup, 1, 1))
        ', $rsm)
            ->setParameter('catalog', $catalog)
            ->setParameter('catalogNum', $catalogNum)
            ->setParameter('model', $model)
            ->setParameter('complectation', $complectation);

        $nativeResult = $nativeQuery->getResult();

        $mgroups = array();

        foreach($nativeResult as $item){
            $mgroups[$item['MainGroup']] = $item['descEn'];
        }

        return $mgroups;
    }
} 