<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 13.11.14
 * Time: 16:46
 */

namespace Catalog\MitsubishiBundle\Repository;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class PartCatalogRepository extends EntityRepository
{
    protected $conn;
    public function setConnection(Connection $connection)
    {
        $this->conn = $connection;
    }

    public function getPncsByModel($catalog, $model, $mainGroup, $subGroup, $classification, $prodDate = "")
    {
        if ($prodDate !== ""){
            $prodDateString = 'AND (pg.EndDate >= :prodDate OR pg.EndDate = "")';
        } else {
            $prodDateString = "";
        }

        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('pnc', 'pnc');
        $rsm->addScalarResult('partNumber', 'partNumber');
        $rsm->addScalarResult('descEn', 'descEn');
        $rsm->addScalarResult('startDate', 'startDate');
        $rsm->addScalarResult('endDate', 'endDate');
        $rsm->addScalarResult('quantity', 'quantity');

        $nativeQuery = $em->createNativeQuery('
            SELECT
              pg.PNC as pnc,
              pg.PartNumber as partNumber,
              pg.StartDate as startDate,
              pg.EndDate as endDate,
              pg.Qty as quantity
            FROM
              `part_catalog` pg
            WHERE
              pg.catalog = :catalog
              AND (pg.Model = :model)
              AND pg.MainGroup = :mainGroup
              AND pg.SubGroup = :subGroup
              AND (pg.Classification = :classification OR pg.Classification = "")' . $prodDateString . '
              GROUP BY pg.PartNumber, pg.StartDate, pg.EndDate
              ORDER BY pg.PNC, pg.StartDate
        ', $rsm)
        ->setParameter('catalog', $catalog)
        ->setParameter('model', $model)
        ->setParameter('mainGroup', $mainGroup)
        ->setParameter('subGroup', $subGroup)
        ->setParameter('classification', $classification)
        ->setParameter('prodDate', $prodDate);

        $nativeResult = $nativeQuery->getResult();

        $pncs = array();
        foreach ($nativeResult as $item){
            $sqlPncDescription = "
                SELECT d.desc_en
                FROM `pnc`
                LEFT JOIN
                  `descriptions` d ON pnc.desc_code = d.TS
                WHERE pnc.pnc = :pnc
                LIMIT 1
                ";
            $query = $this->conn->prepare($sqlPncDescription);
            $query->bindValue('pnc', $item['pnc']);
            $query->execute();

            $sData = $query->fetchColumn();

            $pncs[$item['pnc']]['descEn'] = $sData;
            $pncs[$item['pnc']]['partNumbers'][] = array(
                'partNumber'=>$item['partNumber'],
                'startDate'=>$item['startDate'],
                'endDate'=>$item['endDate'],
                'quantity'=>$item['quantity'],
            );
        }

        return $pncs;
    }

    public function findModelByArticul($articul)
    {
        $em = $this->getEntityManager();

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('catalog', 'catalog');
        $rsm->addScalarResult('model', 'model');
        $rsm->addScalarResult('classification', 'classification');
        $rsm->addScalarResult('mainGroup', 'mainGroup');
        $rsm->addScalarResult('subGroup', 'subGroup');
        $rsm->addScalarResult('pnc', 'pnc');

        $nativeQuery = $em->createNativeQuery('
            SELECT
              pc.Catalog as catalog,
              pc.Model as model,
              pc.Classification as classification,
              pc.MainGroup as mainGroup,
              pc.SubGroup as subGroup,
              pc.PNC as pnc
            FROM
              `part_catalog` pc
            WHERE
              pc.PartNumber = :articul
        ', $rsm);

        if ($articul){
            $nativeQuery->setParameter('articul', $articul);
        }

        $nativeResult = $nativeQuery->getResult();

        return $nativeResult;
    }
} 