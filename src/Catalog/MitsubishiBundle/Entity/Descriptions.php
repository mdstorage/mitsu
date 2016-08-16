<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Descriptions
 * @package Catalog\MitsubishiBundle\Entity
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\DescriptionRepository")
 * @ORM\Table(name="descriptions")
 */
class Descriptions {
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
     * @ORM\Column(name="TS", type="string")
     */
    private $ts;
    /**
     * @ORM\Column(name="desc_en", type="string")
     */
    private $descEn;
} 