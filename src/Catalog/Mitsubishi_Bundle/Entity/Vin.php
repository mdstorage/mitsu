<?php
/**
 * Created by PhpStorm.
 * User: misha
 * Date: 17.11.14
 * Time: 23:11
 */

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\VinRepository")
 * @ORM\Table(name="vin")
 */
class Vin {
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