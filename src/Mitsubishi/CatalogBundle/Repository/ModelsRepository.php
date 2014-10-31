<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 31.10.14
 * Time: 14:45
 */

namespace Mitsubishi\CatalogBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ModelsRepository extends EntityRepository
{
    public function getCatalogsList()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT m.catalog FROM MitsubishiCatalogBundle:Models m GROUP BY m.catalog
        ');

        $result = $query->getResult();

        return $result;
    }

    public function getVncsListByCatalog($catalog)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT m.vnc FROM MitsubishiCatalogBundle:Models m WHERE m.catalog = :catalog GROUP BY m.vnc
        ')->setParameter('catalog', $catalog);

        $result = $query->getResult();

        return $result;
    }
} 