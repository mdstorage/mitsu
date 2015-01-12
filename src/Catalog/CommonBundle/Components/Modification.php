<?php
namespace Catalog\CommonBundle\Components;


use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;
use Catalog\CommonBundle\Components\Interfaces\ComplectationInterface;
use Catalog\CommonBundle\Components\Interfaces\ModificationInterface;

class Modification extends CommonElement implements ModificationInterface{

    private $complectations;

    public function setComplectations(CollectionInterface $collection)
    {
        $this->complectations = $collection->getCollection();

        return $this;
    }

    public function addComplectation(ComplectationInterface $object)
    {
        $this->complectations[] = $object;

        return $this;
    }

    public function getComplectations()
    {
        return $this->complectations;
    }
} 