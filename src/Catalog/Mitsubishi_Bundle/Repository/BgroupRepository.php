<?php

namespace Catalog\MitsubishiBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class BgroupRepository extends EntityRepository{

    public function getClassificationsListByModel($catalog, $model)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT b.classicfication, b.mainGroup, b.subGroup, b.illustration, b.catalogNum FROM MitsubishiCatalogBundle:Bgroup b WHERE b.catalog = :catalog AND b.model = :model
        ')
            ->setParameter('catalog', $catalog)
            ->setParameter('model', $model)
            ;

        $result = $query->getResult();
        $array = array('catalognum'=>0);
        foreach($result as &$item){
            $array['catalognum']=$item['catalogNum'];
            $array[$item['catalogNum']]['classification'] = $item['classicfication'];
            $array[$item['catalogNum']][$item['classicfication']][$item['mainGroup']][] = array('subgroup'=>$item['subGroup'], 'illustration'=>$item['illustration']);
        }
//        echo "<pre>";
//        print_r($array);
//        echo "</pre>";die;
        return $result;
    }

    public function getMainGroupsListByClassicfication($catalog, $model, $classicfication)
    {
        if ($classicfication !== '-') {
            $clString = 'b.classicfication = :classicfication';
        } else {
            $clString = "b.classicfication = ''";
        }
        $em = $this->getEntityManager();

        $query = $em->createQuery('
            SELECT b.mainGroup FROM MitsubishiCatalogBundle:Bgroup b WHERE b.catalog = :catalog AND b.model = :model AND ' . $clString .' GROUP BY b.mainGroup
        ')
            ->setParameter('catalog', $catalog)
            ->setParameter('model', $model);

        if ($classicfication  !== '-') {
            $query->setParameter('classicfication', $classicfication);
        }

        $result = $query->getResult();

        return $result;
    }

    public function getTest($catalog, $model)
    {
        $em = $this->getEntityManager();



        $query = $em->createQuery('
            SELECT b.classicfication FROM MitsubishiCatalogBundle:Bgroup b WHERE b.catalog = :catalog AND b.model = :model GROUP BY b.classicfication
        ')
            ->setParameter('catalog', $catalog)
            ->setParameter('model', $model);

        $result = $query->getResult();

        return $result;
    }

    public function getSubGroupsListByMainGroup($catalog, $model, $classification, $maingroup)
    {

    }

    public function getBgroupsBySgroup($catalog, $catalogNum, $model, $mainGroup, $subGroup, $complectation)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();


        $rsm->addScalarResult('illustration', 'illustration');

        $nativeQuery = $em->createNativeQuery('
        SELECT
          bg.Illustration as illustration
        FROM
          `bgroup` bg
        WHERE bg.catalog = :catalog
        AND bg.Catalog_Num = :catalogNum
        AND bg.Model = :model
        AND bg.MainGroup = :mainGroup
        AND bg.SubGroup = :subGroup
        AND (bg.Classicfication = :complectation OR bg.Classicfication = "")
        GROUP BY bg.Illustration
        ', $rsm)
            ->setParameter('catalog', $catalog)
            ->setParameter('catalogNum', $catalogNum)
            ->setParameter('model', $model)
            ->setParameter('mainGroup', $mainGroup)
            ->setParameter('subGroup', $subGroup)
            ->setParameter('complectation', $complectation);

        $mgroups = $nativeQuery->getResult();

        return $mgroups;
    }
} 