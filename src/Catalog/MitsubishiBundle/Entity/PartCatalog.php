<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\PartCatalogRepository")
 * @ORM\Table(name="part_catalog")
 */
class PartCatalog {
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