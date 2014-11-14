<?php

namespace Catalog\MitsubishiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class ModelDescRepository extends EntityRepository
{
    private $descriptionRepository;

    public function setDescriptionRepository(DescriptionRepository $descriptionRepository)
    {
        $this->descriptionRepository = $descriptionRepository;
    }

    public function getCatalogNumDesc($catalog, $catalogNum)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('descEn', 'descEn');

        $query = $em->createNativeQuery('
            SELECT d.desc_en as descEn
            FROM `model_desc` md
            LEFT JOIN `descriptions` d ON d.TS = TRIM(md.name)
            WHERE TRIM(md.catalog) = :catalog
            AND TRIM(md.Catalog_Num) = :catalogNum
            AND d.catalog = :catalog
            LIMIT 1
        ', $rsm)
            ->setParameter('catalog', $catalog)
            ->setParameter('catalogNum', $catalogNum);

        try {
            $result = $query->getSingleScalarResult();
        } catch(\Doctrine\Orm\NoResultException $e) {
            $result = null;
            echo $e->getMessage();
        }

        return $result;
    }
} 