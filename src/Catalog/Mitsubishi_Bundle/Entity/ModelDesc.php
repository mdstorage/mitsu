<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * Class ModelDesc
 * @package Catalog\MitsubishiBundle\Entity
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\ModelDescRepository")
 * @ORM\Table(name="model_desc")
 */
class ModelDesc {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="catalog", type="string")
     */
    private $catalog;

    /**
     * @ORM\Column(name="catalog_num", type="string")
     */
    private $catalogNum;
    /**
     * @ORM\Column(name="name", type="string")
     */
    private $name;
    /**
     * @ORM\Column(name="StartDate", type="string")
     */
    private $startDate;
    /**
     * @ORM\Column(name="EndDate", type="string")
     */
    private $endDate;
    private $dataPackage;
    private $dataPackageNameJ;
} 