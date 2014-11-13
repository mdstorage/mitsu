<?php

namespace Catalog\MitsubishiBundle\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class PicturesRepository extends EntityRepository
{
    public function getGroupsByPicture($catalog, $illustration)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('startX', 'startX');
        $rsm->addScalarResult('startY', 'startY');
        $rsm->addScalarResult('endX', 'endX');
        $rsm->addScalarResult('endY', 'endY');
        $rsm->addScalarResult('mgCode', 'mgCode');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          p.startX,
          p.startY,
          p.endX,
          p.endY,
          p.desc_code1 as mgCode
        FROM `pictures` p
        WHERE p.catalog = :catalog
        AND p.picture_file = :pictureFile
        ORDER BY p.desc_code1
        ', $rsm)
        ->setParameter('catalog', $catalog)
        ->setParameter('pictureFile', $illustration);

        $groupsList = $nativeQuery->getResult();

        return $groupsList;
    }
} 