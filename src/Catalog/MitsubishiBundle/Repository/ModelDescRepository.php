<?php

namespace Catalog\MitsubishiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ModelDescRepository extends EntityRepository
{
    private $descriptionRepository;

    public function setDescriptionRepository(DescriptionRepository $descriptionRepository)
    {
        $this->descriptionRepository = $descriptionRepository;
    }

    public function getCatalogNumData($catalogNum)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT md.name FROM CatalogMitsubishiBundle:ModelDesc WHERE md.$catalogNum LIKE %:catalogNum%
        ')
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