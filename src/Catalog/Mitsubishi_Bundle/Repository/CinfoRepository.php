<?php

namespace Catalog\MitsubishiBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CinfoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CinfoRepository extends EntityRepository
{
    public function getCatalog(){
        $query = $this->getEntityManager()->createQuery(
            'SELECT c.Catalog FROM CatalogMitsubishiBundle:Cinfo c GROUP BY c.Catalog'
        );

        return $query->getResult();
    }
}
