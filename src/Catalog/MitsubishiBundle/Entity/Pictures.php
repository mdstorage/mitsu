<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Pictures
 * @package Catalog\MitsubishiBundle\Entity
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\PicturesRepository")
 * @ORM\Table(name="pictures")
 */
class Pictures {
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
     * @ORM\Column(name="picture_file", type="string")
     */
    private $pictureFile;
    /**
     * @ORM\Column(name="startX", type="string")
     */
    private $startX;
    /**
     * @ORM\Column(name="startY", type="string")
     */
    private $startY;
    /**
     * @ORM\Column(name="endX", type="string")
     */
    private $endX;
    /**
     * @ORM\Column(name="endY", type="string")
     */
    private $endY;
    /**
     * @ORM\Column(name="desc_code1", type="string")
     */
    private $descCode1;
} 