<?php

namespace Catalog\MitsubishiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Models
 * @package Catalog\MitsubishiBundle\Entity
 * @ORM\Entity(repositoryClass="Catalog\MitsubishiBundle\Repository\ModelsRepository")
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
    /**
     * @ORM\Column(name="Model", type="string")
     */
    private $model;
    /**
     * @ORM\Column(name="Rep_Model", type="string")
     */
    private $repModel;
    /**
     * @ORM\Column(name="name1", type="string")
     */
    private $name1;
    /**
     * @ORM\Column(name="Catalog_Num", type="string")
     */
    private $catalogNum;
    /**
     * @ORM\Column(name="Classification", type="string")
     */
    private $classification;
    /**
     * @ORM\Column(name="name", type="string")
     */
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

    public function getModel()
    {
        return $this->model;
    }

    public function getClassification()
    {
        return $this->classification;
    }

    public function getRepModel()
    {
        return $this->repModel;
    }
} 