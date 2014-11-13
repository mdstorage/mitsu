<?php

namespace Catalog\MitsubishiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class ModelsRepository extends EntityRepository
{
    protected $descriptionRepository;

    public function setDescriptionRepository(DescriptionRepository $descriptionRepository)
    {
        $this->descriptionRepository = $descriptionRepository;
    }

    public function getCatalogsList()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT m.catalog FROM CatalogMitsubishiBundle:Models m GROUP BY m.catalog
        ');

        $catalogList = $query->getResult();

        return $catalogList;
    }



    public function getCatalogNumsListByCatalog($catalog)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('catalogNum', 'catalogNum');
        $rsm->addScalarResult('repModel', 'repModel');
        $rsm->addScalarResult('startDate', 'startDate');
        $rsm->addScalarResult('endDate', 'endDate');
        $rsm->addScalarResult('descEn', 'descEn');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          m.Catalog_Num as catalogNum,
          m.Rep_Model as repModel,
          md.StartDate as startDate,
          md.EndDate as endDate,
          d.desc_en as descEn
        FROM `models` m
        LEFT JOIN `model_desc` md ON m.Catalog_Num = TRIM(md.catalog_num)
        LEFT JOIN `descriptions` d ON TRIM(md.name) = d.TS
        WHERE m.catalog = :catalog
        AND TRIM(md.catalog) = :catalog
        AND d.catalog = :catalog
        GROUP BY d.desc_en;
        ', $rsm)
            ->setParameter('catalog', $catalog);

        $catalogList = $nativeQuery->getResult();

//        echo "<pre>";
//        print_r($catalogList);
//        echo "</pre>";die;

        return $catalogList;
    }

    public function getModelsListByCatalogNum($catalog, $catalogNum)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('model', 'model');
        $rsm->addScalarResult('descEn', 'descEn');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          m.Model as model,
          d.desc_en as descEn
        FROM `models` m
        LEFT JOIN `descriptions` d ON m.Name1 = d.TS
        WHERE m.catalog = :catalog
        AND m.Catalog_Num = :catalogNum
        AND d.catalog = :catalog
        GROUP BY m.Model;
        ', $rsm)
            ->setParameter('catalog', $catalog)
            ->setParameter('catalogNum', $catalogNum);

        $modelList = $nativeQuery->getResult();

        return $modelList;
    }

    public function getClassificationsListByModel($catalog, $catalogNum, $model)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('classification', 'classification');
        $rsm->addScalarResult('descEn', 'descEn');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          m.Classification as classification,
          d.desc_en as descEn
        FROM `models` m
        LEFT JOIN `descriptions` d ON m.name = d.TS
        WHERE m.catalog = :catalog
        AND m.Model = :model
        AND d.catalog = :catalog
        GROUP BY m.Classification
        ', $rsm)
            ->setParameter('catalog', $catalog)
            ->setParameter('model', $model);

        $classificationsList = $nativeQuery->getResult();

        return $classificationsList;
    }
} 