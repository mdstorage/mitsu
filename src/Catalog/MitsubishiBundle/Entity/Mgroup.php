<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\MgroupRepository")
 * @ORM\Table(name="mgroup")
 */
class Mgroup {
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
     * @ORM\Column(name="Catalog_Num", type="string")
     */
    private $catalogNum;
    /**
     * @ORM\Column(name="Model", type="string")
     */
    private $model;
    /**
     * @ORM\Column(name="Illustration", type="string")
     */
    private $illustration;
    /**
     * @ORM\Column(name="MainGroup", type="string")
     */
    private $mainGroup;
    /**
     * @ORM\Column(name="MajorGroup", type="string")
     */
    private $majorGroup;
    /**
     * @ORM\Column(name="TS", type="string")
     */
    private $ts;
    /**
     * @ORM\Column(name="Complectation", type="string")
     */
    private $complectation;
    /**
     * @ORM\Column(name="StartDate", type="string")
     */
    private $startDate;
    /**
     * @ORM\Column(name="EndDate", type="string")
     */
    private $endDate;
} 