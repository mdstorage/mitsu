<?php

namespace Mitsubishi\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Models
 * @package Mitsubishi\CatalogBundle\Entity
 * @ORM\Entity(repositoryClass="Mitsubishi\CatalogBundle\Repository\ModelsRepository")
 * @ORM\Table(name="models")
 */
class Models {
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
     * @ORM\Column(name="VNC", type="string")
     */
    private $vnc;
    private $model;
    private $repModel;
    private $name1;
    private $catalogNum;
    private $classification;
    private $name;

    public function getId()
    {
        return $this->id;
    }

    public function getCatalog()
    {
        return $this->catalog;
    }

    public function getVnc()
    {
        return $this->vnc;
    }
} 