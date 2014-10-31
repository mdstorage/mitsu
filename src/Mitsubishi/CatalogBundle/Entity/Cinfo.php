<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 29.10.14
 * Time: 17:20
 */

namespace Mitsubishi\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Mitsubishi\CatalogBundle\Repository\CinfoRepository")
 * @ORM\Table(name="cinfo")
 */
class Cinfo {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="Catalog", type="string")
     */
    private $Catalog;
    private $CatalogNum;
    private $Version;
    private $Name;
    private $Type;
    private $StartDate;
    private $EndDate;
    private $DataPackage;
    private $DataPackageName;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCatalog()
    {
        return $this->Catalog;
    }
}
