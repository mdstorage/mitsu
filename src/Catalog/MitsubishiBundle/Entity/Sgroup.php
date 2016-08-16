<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\SgroupRepository")
 * @ORM\Table(name="sgroup")
 */
class Sgroup
{
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
} 