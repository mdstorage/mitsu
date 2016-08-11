<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\BgroupRepository")
 * @ORM\Table(name="bgroup")
 */
class Bgroup {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @ORM\Column(name="Catalog", type="string")
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
     * @ORM\Column(name="MainGroup", type="string")
     */
    private $mainGroup;
    /**
     * @ORM\Column(name="SubGroup", type="string")
     */
    private $subGroup;
    private $startDate;
    private $endDate;
    private $ts;
    private $tsDesc;
    /**
     * @ORM\Column(name="Illustration", type="string")
     */
    private $illustration;
    /**
     * @ORM\Column(name="Classicfication", type="string")
     */
    private $classicfication;
    private $opc;

    public function getClassicfication()
    {
        return $this->classicfication;
    }
}
