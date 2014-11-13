<?php

namespace Catalog\MitsubishiBundle\Repository;


use Doctrine\ORM\EntityRepository;

class DescriptionRepository extends EntityRepository
{
    public function getDesc($catalog, $ts)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT d.descEn FROM CatalogMitsubishiBundle:descriptions d WHERE d.catalog = :catalog AND d.ts = :ts
        ')
        ->setParameter('catalog', $catalog)
        ->setParameter('ts', $ts)
        ->setMaxResults(1);

        $result = $query->getSingleScalarResult();

        return $result;
    }
} 